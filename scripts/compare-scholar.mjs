#!/usr/bin/env node
/**
 * Compare a Google Scholar profile against the site's publication data.
 *
 * Scholar has the best coverage of any source -- it picks up conference
 * proceedings, book chapters, preprints and papers whose author metadata is too
 * mangled for ORCID or PubMed to match. It also has no API, forbids scraping,
 * and offers no export. So the workflow is deliberately manual: select the
 * profile's publication list in a browser, paste it into
 * `data/google-scholar-copy.txt`, and run this to see what the automated
 * sources are missing.
 *
 * This is a gap report. It writes nothing to data/ -- resolving a gap means
 * either linking the paper on ORCID (preferred; it then syncs forever) or
 * adding it to `publications-overrides.yml` / `publications-legacy.yml`.
 *
 * MATCHING. Scholar truncates long titles in list view ("...Empowered by
 * Semi-Supervis"), so a truncated Scholar title is compared as a prefix of the
 * known title rather than for equality. That direction matters: the Scholar
 * string is the shorter one.
 *
 * Usage:
 *   node scripts/compare-scholar.mjs [--file path] [--show N]
 */

import { readFile } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';

const ROOT = path.resolve(import.meta.dirname, '..');
const DATA_DIR = path.join(ROOT, 'data');

const fileFlag = process.argv.indexOf('--file');
const INPUT = fileFlag !== -1
  ? path.resolve(process.argv[fileFlag + 1])
  : path.join(DATA_DIR, 'google-scholar-copy.txt');

const showFlag = process.argv.indexOf('--show');
const SHOW = showFlag !== -1 ? Number(process.argv[showFlag + 1]) || 40 : 40;

/** A Scholar row's trailing line is either `year` or `citations<TAB>year`. */
const YEAR_LINE = /^(?:(\d+)\t)?(\d{4})$/;

/**
 * Reduce a title to a comparison key: letters and digits only, lowercased.
 *
 * Runs of single letters are glued back together so that a dotted acronym and
 * its plain form agree -- Crossref registers one paper as "G.A.M.E.: GPU-
 * accelerated mixture elucidator" while Scholar shows "GAME: ...", and without
 * this they key differently and the paper looks missing when it is not.
 */
function titleKey(raw) {
  return String(raw ?? '')
    .toLowerCase()
    .replace(/[‘’“”]/g, '')
    .replace(/[^\p{L}\p{N}]+/gu, ' ')
    .trim()
    .replace(/\b(?:\p{L} )+\p{L}\b/gu, (run) => run.replace(/ /g, ''));
}

/**
 * Parse the pasted list.
 *
 * Records are delimited by their trailing year line rather than assumed to be a
 * fixed number of lines, because the venue line is absent for some entries.
 */
function parseScholar(text) {
  const lines = text.split('\n').map((line) => line.replace(/\r$/, ''));
  const records = [];
  let buffer = [];

  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed) continue;

    const year = YEAR_LINE.exec(trimmed);
    if (year && buffer.length > 0) {
      records.push({
        title: buffer[0],
        authors: buffer[1] ?? '',
        venue: buffer.slice(2).join(' '),
        citations: year[1] ? Number(year[1]) : 0,
        year: Number(year[2]),
      });
      buffer = [];
      continue;
    }
    // Skip the three-line column header Scholar puts at the top.
    if (/^(title|cited by|year)$/i.test(trimmed)) continue;
    buffer.push(trimmed);
  }
  return records;
}

/**
 * Does this Scholar title correspond to a known publication?
 *
 * Exact key match first, then prefix in either direction to absorb Scholar's
 * truncation. A prefix has to be substantial to count -- a short title could
 * otherwise prefix-match an unrelated longer one.
 */
function findMatch(scholarKey, knownKeys) {
  if (knownKeys.has(scholarKey)) return knownKeys.get(scholarKey);
  if (scholarKey.length < 25) return null;
  for (const [key, label] of knownKeys) {
    if (key.startsWith(scholarKey) || scholarKey.startsWith(key)) return label;
  }
  return null;
}

async function loadYaml(file, fallback) {
  try {
    return YAML.parse(await readFile(path.join(DATA_DIR, file), 'utf8')) ?? fallback;
  } catch (error) {
    if (error.code !== 'ENOENT') throw error;
    return fallback;
  }
}

async function main() {
  let text;
  try {
    text = await readFile(INPUT, 'utf8');
  } catch (error) {
    if (error.code === 'ENOENT') {
      console.error(`no Scholar paste at ${path.relative(ROOT, INPUT)}`);
      console.error('Select the publication list on the Scholar profile, paste it into that');
      console.error('file, and run this again.');
      process.exit(1);
    }
    throw error;
  }

  const scholar = parseScholar(text);

  const published = (await loadYaml('publications.yml', {})).publications ?? [];
  const legacy = await loadYaml('publications-legacy.yml', {});

  // Everything the site already carries, keyed by title.
  const known = new Map();
  for (const entry of published) known.set(titleKey(entry.title), 'publications.yml');
  for (const [section, list] of Object.entries(legacy)) {
    if (!Array.isArray(list)) continue;
    for (const citation of list) known.set(titleKey(citation), `legacy/${section}`);
  }

  const matched = [];
  const missing = [];
  for (const record of scholar) {
    const key = titleKey(record.title);
    if (!key) continue;
    const where = findMatch(key, known);
    if (where) matched.push({ ...record, where });
    else missing.push(record);
  }

  console.log(`scholar entries parsed : ${scholar.length}`);
  console.log(`site already has       : ${matched.length}`);
  console.log(`MISSING from the site  : ${missing.length}`);
  console.log();
  console.log(`(site corpus: ${published.length} journal articles + `
    + `${Object.values(legacy).filter(Array.isArray).reduce((n, l) => n + l.length, 0)} legacy citations)`);
  console.log();

  if (missing.length === 0) {
    console.log('Nothing missing. The automated sources cover the whole Scholar profile.');
    return;
  }

  // Citation count is the most useful sort here: an uncited 2013 abstract
  // matters far less than a well-cited paper the site never mentions.
  missing.sort((a, b) => b.citations - a.citations || b.year - a.year);

  console.log('=== missing, most-cited first ===');
  for (const record of missing.slice(0, SHOW)) {
    console.log(`  ${String(record.citations).padStart(5)} cites  ${record.year}`);
    console.log(`         ${record.title}`);
    console.log(`         ${record.authors}`);
    console.log(`         ${record.venue}`);
  }
  if (missing.length > SHOW) {
    console.log(`  … ${missing.length - SHOW} more (raise --show)`);
  }

  const byYear = {};
  for (const record of missing) byYear[record.year] = (byYear[record.year] ?? 0) + 1;
  console.log();
  console.log('missing by year:', Object.entries(byYear)
    .sort((a, b) => Number(b[0]) - Number(a[0]))
    .map(([year, n]) => `${year}:${n}`)
    .join(' '));
  console.log();
  console.log('To resolve: link the paper on https://orcid.org/0000-0002-8461-6181 so it');
  console.log('syncs automatically, or add it to data/publications-overrides.yml (journal');
  console.log('articles) or data/publications-legacy.yml (conference papers, patents, books).');
}

await main();
