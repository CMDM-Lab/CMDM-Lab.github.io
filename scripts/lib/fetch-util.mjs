// Shared HTTP helpers for the data-sync scripts.
//
// Every external API used here is a public, no-key scholarly service. Two of
// them ask for politeness in return, and we honour both:
//   - NCBI E-utilities: at most 3 requests/second without an API key.
//   - Crossref: send a `mailto` so we land in the polite pool.
// Violating either gets the GitHub Action's shared IP throttled or blocked,
// which would silently stall the nightly publication sync.

export const CONTACT_EMAIL = 'cot@cmdm.csie.ntu.edu.tw';
const USER_AGENT = `cmdm-lab-website/2.0 (+https://www.cmdm.tw; mailto:${CONTACT_EMAIL})`;

/** Minimum spacing between requests to a given host, in milliseconds. */
const HOST_THROTTLE_MS = {
  'eutils.ncbi.nlm.nih.gov': 400, // stay under the 3 req/s cap
  'api.crossref.org': 200,
  'pub.orcid.org': 200,
};

const lastRequestAt = new Map();

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function throttle(host) {
  const spacing = HOST_THROTTLE_MS[host] ?? 100;
  const previous = lastRequestAt.get(host) ?? 0;
  const wait = previous + spacing - Date.now();
  if (wait > 0) await sleep(wait);
  lastRequestAt.set(host, Date.now());
}

/**
 * GET a URL and parse it as JSON, with host-aware throttling and retries.
 *
 * Retries cover the failure mode that actually bites in CI: a transient 429/5xx
 * from a service that is up but busy. A 404 is a real answer, not a blip, so it
 * fails fast rather than burning three attempts.
 */
export async function fetchJson(url, { attempts = 3, timeoutMs = 45_000 } = {}) {
  const host = new URL(url).host;
  let lastError;

  for (let attempt = 1; attempt <= attempts; attempt += 1) {
    await throttle(host);
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeoutMs);
    try {
      const response = await fetch(url, {
        signal: controller.signal,
        headers: { Accept: 'application/json', 'User-Agent': USER_AGENT },
      });
      if (response.status === 404) {
        throw Object.assign(new Error(`404 Not Found: ${url}`), { fatal: true });
      }
      if (!response.ok) {
        throw new Error(`HTTP ${response.status} ${response.statusText}: ${url}`);
      }
      return await response.json();
    } catch (error) {
      if (error.fatal) throw error;
      lastError = error;
      if (attempt < attempts) {
        // Exponential backoff: 1s, 2s. Enough to ride out a rate-limit window
        // without making the whole sync job look hung.
        await sleep(1000 * 2 ** (attempt - 1));
      }
    } finally {
      clearTimeout(timer);
    }
  }
  throw new Error(`giving up after ${attempts} attempts: ${lastError?.message}`);
}

/** Split an array into fixed-size chunks, for batched API calls. */
export function chunk(items, size) {
  const out = [];
  for (let i = 0; i < items.length; i += size) out.push(items.slice(i, i + size));
  return out;
}

/**
 * Normalise a DOI into a stable dedupe key.
 *
 * The same DOI reaches us in several shapes across sources -- bare
 * (`10.1093/bib/bbaf392`), prefixed (`https://doi.org/10.1093/...`), and in
 * mixed case. DOIs are case-insensitive by spec, so we lowercase and strip the
 * resolver prefix; without this, ORCID and PubMed records for one paper end up
 * as two entries.
 */
export function normalizeDoi(raw) {
  if (!raw) return '';
  return String(raw)
    .trim()
    .toLowerCase()
    .replace(/^https?:\/\/(dx\.)?doi\.org\//, '')
    .replace(/^doi:/, '');
}

/**
 * Reduce a title to a comparison key, used only when a record has no DOI.
 *
 * Strips punctuation, collapses whitespace, and drops a trailing period so that
 * "Taiwan Controlled Substances Database." and "Taiwan Controlled Substances
 * Database" collapse to one key.
 */
export function titleKey(raw) {
  if (!raw) return '';
  return String(raw)
    .toLowerCase()
    .replace(/<[^>]+>/g, ' ')
    .replace(/[^\p{L}\p{N}]+/gu, ' ')
    .trim();
}
