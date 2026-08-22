// Reading and applying the human decisions in data/publications-overrides.yml.
//
// This is small but correctness-critical in both directions: a mistaken
// exclusion silently drops a real paper off the site, and a broken exclusion
// silently publishes someone else's. Hence its own module with its own tests
// rather than living inline in the fetch script.

import { normalizeDoi } from './fetch-util.mjs';

/**
 * Every DOI starts with a `10.` registrant prefix. Anything else is a typo.
 *
 * This check exists because `normalizeDoi` only cleans a string up, it does not
 * judge it -- so without validation here, a mistyped line would be accepted as
 * a perfectly good DOI that simply matches nothing. The paper stays wrongly
 * excluded, or wrongly missing, and there is no error to notice.
 */
const DOI_SHAPE = /^10\.\d{4,9}\/\S+$/;

/**
 * Split an override list into DOIs and PMIDs.
 *
 * Most entries are DOIs, but not every record has one -- older conference
 * proceedings in particular are PubMed-only. Without a way to name those, a
 * PMID-only false positive could never be ruled out and would sit in the review
 * queue forever. An entry written `pmid:23542986` addresses one by PubMed ID.
 *
 * Malformed entries are collected into `invalid` rather than throwing: the file
 * is hand-edited and one bad line should not take down the weekly sync. Callers
 * are expected to print `invalid`, so a typo produces a visible complaint
 * instead of a silently ineffective rule.
 */
export function splitIdentifiers(entries) {
  const dois = new Set();
  const pmids = new Set();
  const invalid = [];

  for (const raw of entries ?? []) {
    if (raw === null || raw === undefined) continue;
    const value = String(raw).trim();
    if (!value) continue;

    const asPmid = value.match(/^pmid:\s*(\d+)$/i);
    if (asPmid) {
      pmids.add(asPmid[1]);
      continue;
    }

    const doi = normalizeDoi(value);
    if (DOI_SHAPE.test(doi)) dois.add(doi);
    else invalid.push(value);
  }
  return { dois, pmids, invalid };
}

/** Is this record named in an override list, by either identifier? */
export function listed(list, record) {
  if (!list || !record) return false;
  if (record.doi && list.dois.has(normalizeDoi(record.doi))) return true;
  return Boolean(record.pmid) && list.pmids.has(String(record.pmid));
}

/** Normalise patch keys so a DOI's case does not decide whether a patch applies. */
export function normalizePatch(patch) {
  return Object.fromEntries(
    Object.entries(patch ?? {}).map(([doi, fields]) => [normalizeDoi(doi), fields]),
  );
}
