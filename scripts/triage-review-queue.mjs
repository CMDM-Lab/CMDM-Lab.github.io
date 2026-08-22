#!/usr/bin/env node
/**
 * Rank the publication review queue by how likely each candidate is the lab's.
 *
 * `data/publications-review.yml` holds the PubMed affiliation query's leftovers.
 * Reading 40-odd titles by hand every sync is the kind of chore that quietly
 * stops happening, so this script does the sorting pass and leaves only the
 * judgement call. It decides nothing: DOIs still have to be moved into
 * `include:` or `exclude:` in publications-overrides.yml by hand.
 *
 * WHY THE QUEUE IS SO NOISY. It is not one name collision, it is many.
 * `Tseng YJ[Author]` matches every researcher whose given name abbreviates to
 * "YJ", and in Taiwan that is a crowd. Observed in this queue alone:
 *
 *     Yea-Jing · Yun-Ju · Yu-Ju · Yu-Jui · Yen-Ju · Yu-Jou · Yi-Ju · Y.-J.
 *
 * all distinct people, none of them the lab's PI.
 *
 * SCORING. The discriminator is the PI's own given name, not co-authorship.
 * Publishers record her as "Yufeng Jane", "Yufeng J." or "Y. Jane" -- every
 * variant contains "yufeng" or "jane", and none of the names above contain
 * either. That single test separates the queue cleanly.
 *
 * Co-author overlap is reported as supporting context only. It is deliberately
 * NOT the primary signal: an earlier version of this script keyed collaborators
 * on surname plus first initial, which for Chinese names collides constantly
 * ("Chi-Tang Ho" and "Chi-Chang Huang" style pairs), and it confidently ranked
 * another Tseng's entire corpus as "likely yours". Full given names are
 * compared now, but the lesson stands: an initial is not an identity.
 *
 * Usage:
 *   node scripts/triage-review-queue.mjs [--top N]
 *   node scripts/triage-review-queue.mjs --emit-exclude   # YAML for the overrides file
 *
 * `--emit-exclude` prints an annotated `exclude:` block covering only the
 * candidates whose Tseng has a spelled-out given name that is not the PI's.
 * Ambiguous and metadata-less entries are left out on purpose: they stay in the
 * queue until a person rules on them.
 */

import { readFile } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';
import { CONTACT_EMAIL, chunk, fetchJson, normalizeDoi } from './lib/fetch-util.mjs';

const ROOT = path.resolve(import.meta.dirname, '..');
const DATA_DIR = path.join(ROOT, 'data');

const topFlag = process.argv.indexOf('--top');
const TOP = topFlag !== -1 ? Number(process.argv[topFlag + 1]) || 25 : 25;
const EMIT_EXCLUDE = process.argv.includes('--emit-exclude');

/**
 * Collapse an author into a comparison key.
 *
 * Full given name, not an initial. Chinese given names share initials far too
 * often for an initial to identify anyone -- keying on one made unrelated
 * researchers look like the same collaborator.
 */
function authorKey(author) {
  const family = (author.family ?? '').toLowerCase().replace(/[^a-z]/g, '');
  const given = (author.given ?? '').toLowerCase().replace(/[^a-z]/g, '');
  return `${family}|${given}`;
}

const isTseng = (author) => (author.family ?? '').toLowerCase().includes('tseng');

/**
 * Does this Tseng look like the lab's PI?
 *
 * Every form publishers use for her contains "yufeng" or "jane". The other
 * Tseng YJs in this queue -- Yea-Jing, Yun-Ju, Yu-Ju, Yu-Jui, Yen-Ju, Yu-Jou,
 * Yi-Ju -- contain neither.
 */
const PI_GIVEN_NAME = /yufeng|jane/i;

function piMatch(authors) {
  const tsengs = authors.filter(isTseng);
  const matched = tsengs.filter((a) => PI_GIVEN_NAME.test(a.given ?? ''));
  if (matched.length > 0) return 'yes';
  // "Y. J. Tseng" with no spelled-out given name cannot be resolved either way.
  const ambiguous = tsengs.filter((a) => !/[a-z]{3}/i.test((a.given ?? '').replace(/[^a-z]/gi, '')));
  if (tsengs.length === 0) return 'no-tseng';
  return ambiguous.length > 0 ? 'ambiguous' : 'other-tseng';
}

async function main() {
  const confirmed = YAML.parse(
    await readFile(path.join(DATA_DIR, 'publications.yml'), 'utf8'),
  ).publications ?? [];

  // The PI is on nearly every paper in both researchers' corpora, so she
  // carries no discriminating information and is left out of the key set.
  const collaborators = new Map();
  for (const publication of confirmed) {
    for (const author of publication.authors ?? []) {
      if (isTseng(author)) continue;
      const key = authorKey(author);
      collaborators.set(key, (collaborators.get(key) ?? 0) + 1);
    }
  }

  const review = YAML.parse(
    await readFile(path.join(DATA_DIR, 'publications-review.yml'), 'utf8'),
  ).candidates ?? [];

  console.log(`confirmed corpus : ${confirmed.length} papers, ${collaborators.size} distinct collaborators`);
  console.log(`review queue     : ${review.length} candidates`);

  const dois = review.map((entry) => normalizeDoi(entry.doi)).filter(Boolean);
  const metadata = new Map();
  for (const batch of chunk(dois, 20)) {
    const filter = batch.map((doi) => `doi:${doi}`).join(',');
    const payload = await fetchJson(
      `https://api.crossref.org/works?rows=${batch.length}`
      + `&mailto=${encodeURIComponent(CONTACT_EMAIL)}`
      + `&filter=${encodeURIComponent(filter)}`,
    );
    for (const item of payload.message?.items ?? []) metadata.set(normalizeDoi(item.DOI), item);
  }
  console.log(`crossref hits    : ${metadata.size} / ${dois.length}\n`);

  const scored = review.map((entry) => {
    const doi = normalizeDoi(entry.doi);
    const item = metadata.get(doi);
    if (!item) {
      return {
        ...entry, doi, verdict: 'no-metadata', shared: [], tseng: [], journal: entry.journal ?? '',
      };
    }
    const authors = item.author ?? [];
    return {
      year: entry.year ?? item.issued?.['date-parts']?.[0]?.[0] ?? null,
      title: entry.title ?? item.title?.[0] ?? '',
      doi,
      journal: item['container-title']?.[0] ?? entry.journal ?? '',
      verdict: piMatch(authors),
      shared: authors
        .filter((a) => !isTseng(a) && collaborators.has(authorKey(a)))
        .map((a) => `${a.given ?? ''} ${a.family ?? ''}`.trim()),
      tseng: authors.filter(isTseng).map((a) => `${a.given ?? ''} ${a.family ?? ''}`.trim()),
    };
  });

  const buckets = [
    ['INCLUDE — the PI is an author', 'yes'],
    ['DECIDE BY HAND — Tseng given name is only initials', 'ambiguous'],
    ['EXCLUDE — a different Tseng', 'other-tseng'],
    ['EXCLUDE — no Tseng in the author list at all', 'no-tseng'],
    ['NO CROSSREF METADATA — check manually', 'no-metadata'],
  ];

  if (EMIT_EXCLUDE) {
    // Only the confidently-wrong ones. Each line records which Tseng it
    // actually is, so the exclusion can be audited without re-querying.
    const rejects = scored
      .filter((s) => s.verdict === 'other-tseng' || s.verdict === 'no-tseng')
      .sort((a, b) => (b.year ?? 0) - (a.year ?? 0));
    console.log(`  # ${rejects.length} candidates whose author is a different Tseng.`);
    console.log('  # Emitted by scripts/triage-review-queue.mjs --emit-exclude');
    for (const entry of rejects) {
      const who = entry.tseng.join(', ') || 'no Tseng in author list';
      console.log(`  - ${entry.doi}  # ${entry.year ?? '????'} ${who}`);
    }
    return;
  }

  for (const [label, verdict] of buckets) {
    console.log(`${label}: ${scored.filter((s) => s.verdict === verdict).length}`);
  }
  console.log();

  for (const [label, verdict] of buckets) {
    const list = scored
      .filter((s) => s.verdict === verdict)
      .sort((a, b) => (b.year ?? 0) - (a.year ?? 0));
    if (list.length === 0) continue;
    console.log(`=== ${label} ===`);
    for (const entry of list.slice(0, TOP)) {
      console.log(`  ${entry.year ?? '????'}  ${entry.doi}`);
      console.log(`        ${(entry.title ?? '').slice(0, 88)}`);
      console.log(`        ${entry.journal}`);
      console.log(`        Tseng recorded as: ${entry.tseng.join(', ') || 'NONE'}`);
      if (entry.shared.length) {
        console.log(`        also-published-with: ${entry.shared.slice(0, 6).join(', ')}`);
      }
    }
    if (list.length > TOP) console.log(`  … ${list.length - TOP} more (raise --top to see them)`);
    console.log();
  }

  const ready = scored.filter((s) => s.verdict === 'yes');
  if (ready.length > 0) {
    console.log('Paste into `include:` in data/publications-overrides.yml:');
    for (const entry of ready) console.log(`  - ${entry.doi}`);
    console.log();
  }
  console.log('Everything else belongs in `exclude:`. Then re-run');
  console.log('scripts/fetch-publications.mjs.');
}

await main();
