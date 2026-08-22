#!/usr/bin/env node
/**
 * Rebuild `data/publications.yml` from public scholarly APIs.
 *
 * Source strategy -- measured against the real record on 2026-08-22, not
 * assumed. Counts are logged on every run so drift shows up in the sync PR:
 *
 *   ORCID profile (0000-0002-8461-6181) .......... 101 works, 100 with a DOI
 *   PubMed "Tseng Yufeng Jane[Author]" ...........  48 works
 *   PubMed "Tseng YJ[Author] AND NTU[Affiliation]" 127 works
 *
 * ORCID is the primary source because it is the only one that is both complete
 * back to 2003 and curated by the author. PubMed's full-name query looks like
 * the obvious source but retrieves barely half: PubMed only stores a full
 * author name when the publisher supplied one, so pre-2015 papers are indexed
 * as "Tseng YJ" alone and the full-name query silently drops 65 of them.
 *
 * The full-name query still earns its place -- it finds 12 papers absent from
 * ORCID -- so tier 1 is the union of the two, and both are trusted enough to
 * publish unattended.
 *
 * The affiliation query is deliberately NOT trusted, and the reason is worse
 * than a single name clash. "Tseng YJ" is not one other researcher, it is at
 * least ten -- Yu-Ju, Yun-Ju, Yi-Ju, Yu-Jui, Yen-Ju, Yea-Jing, Yen-Jhen,
 * Yu-Jou, Yu-Jung, Yong-Jhe, Yu-Jen -- all of whom abbreviate identically and
 * several of whom also publish from NTU. Its extra hits arrive as tigecycline
 * dosing, retinal-ganglion-cell degeneration and soft-coral natural products
 * mixed in with real lab output.
 *
 * Nor does co-authorship separate them: some share genuine collaborators with
 * this lab. The only reliable discriminator is the PI's spelled-out given name,
 * which always contains "yufeng" or "jane" and never matches the others. That
 * is what scripts/triage-review-queue.mjs keys on.
 *
 * So tier 2 goes to `data/publications-review.yml` for a human to rule on, and
 * the ruling is recorded in `data/publications-overrides.yml`.
 */

import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';
import {
  CONTACT_EMAIL, chunk, fetchJson, normalizeDoi, plainText, titleKey,
} from './lib/fetch-util.mjs';
import { listed, normalizePatch, splitIdentifiers } from './lib/overrides.mjs';

const ORCID_ID = '0000-0002-8461-6181'; // Yufeng Jane Tseng, National Taiwan University

/** Trusted sources: merged and published without human review. */
const TIER1_PUBMED_QUERY = 'Tseng Yufeng Jane[Author]';
/** Untrusted source: "Tseng YJ" collides with ~10 researchers, so review-only. */
const TIER2_PUBMED_QUERY = 'Tseng YJ[Author] AND National Taiwan University[Affiliation]';

const ROOT = path.resolve(import.meta.dirname, '..');
const DATA_DIR = path.join(ROOT, 'data');
const OUT_PUBLICATIONS = path.join(DATA_DIR, 'publications.yml');
const OUT_REVIEW = path.join(DATA_DIR, 'publications-review.yml');
const OVERRIDES = path.join(DATA_DIR, 'publications-overrides.yml');

// ---------------------------------------------------------------------------
// Sources
// ---------------------------------------------------------------------------

async function fetchOrcidWorks() {
  const payload = await fetchJson(`https://pub.orcid.org/v3.0/${ORCID_ID}/works`);
  const records = [];
  for (const group of payload.group ?? []) {
    const summary = group['work-summary']?.[0];
    if (!summary) continue;
    const ids = {};
    for (const entry of group['external-ids']?.['external-id'] ?? []) {
      if (entry['external-id-type']) ids[entry['external-id-type']] = entry['external-id-value'];
    }
    records.push({
      title: plainText(summary.title?.title?.value),
      doi: normalizeDoi(ids.doi),
      pmid: ids.pmid ? String(ids.pmid) : '',
      year: Number(summary['publication-date']?.year?.value) || null,
      journal: plainText(summary['journal-title']?.value),
      type: summary.type ?? '',
      source: 'orcid',
    });
  }
  return records;
}

async function fetchPubmedQuery(term) {
  const search = await fetchJson(
    'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi'
      + `?db=pubmed&retmode=json&retmax=500&email=${encodeURIComponent(CONTACT_EMAIL)}`
      + `&term=${encodeURIComponent(term)}`,
  );
  const pmids = search.esearchresult?.idlist ?? [];
  const records = [];

  for (const batch of chunk(pmids, 200)) {
    const summary = await fetchJson(
      'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi'
        + `?db=pubmed&retmode=json&id=${batch.join(',')}`
        + `&email=${encodeURIComponent(CONTACT_EMAIL)}`,
    );
    for (const pmid of summary.result?.uids ?? []) {
      const item = summary.result[pmid];
      const doi = item.articleids?.find((a) => a.idtype === 'doi')?.value ?? '';
      records.push({
        title: plainText(item.title),
        doi: normalizeDoi(doi),
        pmid: String(pmid),
        year: Number((item.pubdate ?? '').slice(0, 4)) || null,
        journal: plainText(item.fulljournalname || item.source),
        type: 'journal-article',
        source: 'pubmed',
      });
    }
  }
  return records;
}

/**
 * Fill in authoritative bibliographic metadata for the DOIs we have.
 *
 * ORCID summaries carry a title and year but often no author list, and PubMed
 * abbreviates journal names inconsistently. Crossref is the registration
 * authority for these DOIs, so its record is what we render.
 */
async function enrichFromCrossref(dois) {
  const enriched = new Map();
  for (const batch of chunk(dois, 20)) {
    const filter = batch.map((doi) => `doi:${doi}`).join(',');
    const payload = await fetchJson(
      `https://api.crossref.org/works?rows=${batch.length}`
        + `&mailto=${encodeURIComponent(CONTACT_EMAIL)}`
        + `&filter=${encodeURIComponent(filter)}`,
    );
    for (const item of payload.message?.items ?? []) {
      const doi = normalizeDoi(item.DOI);
      if (!doi) continue;
      const issued = item.issued?.['date-parts']?.[0] ?? [];
      enriched.set(doi, {
        // Publisher markup is stripped here, at the boundary, so nothing
        // downstream has to remember that these strings can contain HTML.
        title: plainText(item.title?.[0]),
        journal: plainText(item['container-title']?.[0]),
        year: issued[0] ?? null,
        month: issued[1] ?? null,
        volume: item.volume ?? '',
        issue: item.issue ?? '',
        pages: item.page ?? '',
        type: item.type ?? '',
        publisher: item.publisher ?? '',
        authors: (item.author ?? []).map((a) => ({
          family: plainText(a.family),
          given: plainText(a.given),
          orcid: a.ORCID ? normalizeDoi(a.ORCID).replace(/^https?:\/\/orcid\.org\//, '') : '',
        })).filter((a) => a.family || a.given),
      });
    }
  }
  return enriched;
}

// ---------------------------------------------------------------------------
// Merge
// ---------------------------------------------------------------------------

/**
 * Key a record for deduplication.
 *
 * DOI is the identity when present. Records without one -- one ORCID entry, and
 * a handful of PubMed records for older papers -- fall back to a normalised
 * title so they still collapse across sources instead of appearing twice.
 */
function dedupeKey(record) {
  if (record.doi) return `doi:${record.doi}`;
  if (record.pmid) return `pmid:${record.pmid}`;
  return `title:${titleKey(record.title)}`;
}

/** Merge records from several sources, remembering which sources saw each one. */
function mergeRecords(groups) {
  const merged = new Map();
  const titleIndex = new Map();

  for (const records of groups) {
    for (const record of records) {
      // A DOI-bearing record and a DOI-less record for the same paper key
      // differently, so also reconcile on title before inserting.
      const tk = titleKey(record.title);
      const existingKey = merged.has(dedupeKey(record))
        ? dedupeKey(record)
        : (tk && titleIndex.get(tk)) || dedupeKey(record);

      const existing = merged.get(existingKey);
      if (!existing) {
        const entry = { ...record, sources: [record.source] };
        delete entry.source;
        merged.set(existingKey, entry);
        if (tk) titleIndex.set(tk, existingKey);
        continue;
      }

      if (!existing.sources.includes(record.source)) existing.sources.push(record.source);
      // Prefer any non-empty value; sources are complementary, not ranked.
      for (const field of ['doi', 'pmid', 'journal', 'title']) {
        if (!existing[field] && record[field]) existing[field] = record[field];
      }
      existing.year ??= record.year;
    }
  }
  return merged;
}

async function readOverrides() {
  const empty = {
    exclude: { dois: new Set(), pmids: new Set() },
    include: { dois: new Set(), pmids: new Set() },
    manual: [],
    patch: {},
  };
  try {
    const parsed = YAML.parse(await readFile(OVERRIDES, 'utf8')) ?? {};
    return {
      exclude: splitIdentifiers(parsed.exclude),
      include: splitIdentifiers(parsed.include),
      manual: parsed.manual ?? [],
      patch: normalizePatch(parsed.patch),
    };
  } catch (error) {
    if (error.code !== 'ENOENT') throw error;
    return empty;
  }
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

async function main() {
  const overrides = await readOverrides();

  // A malformed identifier is a rule that silently matches nothing, so say so
  // loudly rather than letting it look like it worked.
  for (const [section, list] of [['include', overrides.include], ['exclude', overrides.exclude]]) {
    for (const bad of list.invalid ?? []) {
      console.warn(`WARNING: ${section}: "${bad}" is not a valid DOI or pmid: entry -- ignored`);
    }
  }

  console.log('fetching sources…');
  const [orcid, pubmedTier1, pubmedTier2] = await Promise.all([
    fetchOrcidWorks(),
    fetchPubmedQuery(TIER1_PUBMED_QUERY),
    fetchPubmedQuery(TIER2_PUBMED_QUERY),
  ]);
  console.log(`  orcid            ${orcid.length}`);
  console.log(`  pubmed tier1     ${pubmedTier1.length}  (${TIER1_PUBMED_QUERY})`);
  console.log(`  pubmed tier2     ${pubmedTier2.length}  (review only)`);

  const trusted = mergeRecords([orcid, pubmedTier1]);

  // Tier 2 records are candidates unless a human already ruled on them.
  const review = [];
  for (const record of pubmedTier2) {
    const key = dedupeKey(record);
    if (trusted.has(key)) continue;
    if (listed(overrides.exclude, record)) continue;
    if (listed(overrides.include, record)) {
      trusted.set(key, { ...record, sources: ['pubmed-affiliation'], confirmed: true });
      continue;
    }
    review.push(record);
  }

  // Drop anything a human ruled out, including from the trusted sources: an
  // ORCID profile can carry a mistaken claim too.
  for (const [key, record] of trusted) {
    if (listed(overrides.exclude, record)) trusted.delete(key);
  }

  // `include:` is authoritative, not merely a promotion from tier 2. Some of
  // the lab's papers are in none of the three sources at all -- the author
  // metadata is too mangled for PubMed, and they were never linked on ORCID --
  // so a DOI listed here is fetched from Crossref regardless of whether any
  // query found it. Comparing against the Google Scholar profile
  // (scripts/compare-scholar.mjs) is how those gaps get noticed.
  const presentDois = new Set([...trusted.values()].map((r) => r.doi).filter(Boolean));
  let adopted = 0;
  for (const doi of overrides.include.dois) {
    if (presentDois.has(doi)) continue;
    trusted.set(`doi:${doi}`, { title: '', doi, pmid: '', year: null, journal: '', type: '', sources: ['manual-include'] });
    adopted += 1;
  }
  if (adopted > 0) console.log(`  adopted from include: ${adopted} (absent from every source)`);

  console.log('enriching from crossref…');
  const dois = [...trusted.values()].map((r) => r.doi).filter(Boolean);
  const crossref = await enrichFromCrossref(dois);
  console.log(`  crossref hits    ${crossref.size} / ${dois.length}`);

  const publications = [...trusted.values()].map((record) => {
    const extra = record.doi ? crossref.get(record.doi) ?? {} : {};
    const entry = {
      title: extra.title || record.title,
      authors: extra.authors ?? [],
      journal: extra.journal || record.journal,
      year: extra.year ?? record.year,
      month: extra.month ?? null,
      volume: extra.volume ?? '',
      issue: extra.issue ?? '',
      pages: extra.pages ?? '',
      type: extra.type || record.type || 'journal-article',
      doi: record.doi,
      pmid: record.pmid,
      url: record.doi ? `https://doi.org/${record.doi}` : '',
      sources: [...record.sources].sort(),
    };
    return { ...entry, ...(overrides.patch[record.doi] ?? {}) };
  });

  for (const entry of overrides.manual) {
    publications.push({ type: 'journal-article', sources: ['manual'], ...entry });
  }

  // Deterministic ordering keeps the sync PR's diff readable: newest first, and
  // a stable tiebreak so equal-year entries never shuffle between runs.
  publications.sort((a, b) => (b.year ?? 0) - (a.year ?? 0)
    || (a.title ?? '').localeCompare(b.title ?? ''));

  const byYear = {};
  for (const p of publications) byYear[p.year ?? 'unknown'] = (byYear[p.year ?? 'unknown'] ?? 0) + 1;

  await writeFile(OUT_PUBLICATIONS, YAML.stringify({
    // eslint-disable-next-line max-len
    _generated: 'Generated by scripts/fetch-publications.mjs -- do not hand-edit. Corrections belong in publications-overrides.yml.',
    orcid: ORCID_ID,
    count: publications.length,
    publications,
  }, { lineWidth: 100 }), 'utf8');

  review.sort((a, b) => (b.year ?? 0) - (a.year ?? 0)
    || (a.title ?? '').localeCompare(b.title ?? ''));
  await writeFile(OUT_REVIEW, YAML.stringify({
    _generated: `Candidates from "${TIER2_PUBMED_QUERY}" that are NOT in a trusted source.`,
    _instructions: 'This query name-collides with another NTU researcher. Move real lab papers'
      + " into publications-overrides.yml `include:`, and the rest into `exclude:`.",
    count: review.length,
    candidates: review,
  }, { lineWidth: 100 }), 'utf8');

  console.log(`\nwrote ${path.relative(ROOT, OUT_PUBLICATIONS)}: ${publications.length} publications`);
  console.log(`wrote ${path.relative(ROOT, OUT_REVIEW)}: ${review.length} awaiting review`);
  console.log('by year:', Object.entries(byYear).sort((a, b) => b[0].localeCompare(a[0]))
    .slice(0, 6).map(([y, n]) => `${y}:${n}`).join(' '));
}

await main();
