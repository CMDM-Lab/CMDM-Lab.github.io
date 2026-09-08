#!/usr/bin/env node
/**
 * Find thesis records the site does not link yet.
 *
 * check-thesis-records.mjs answers "is the year we publish the year NTU holds?"
 * for rows that already carry a `thesis_url`. This script answers the question
 * that comes first: does a record exist at all, and what does it say?
 *
 * It exists because a list of former members arrived from the lab with a
 * degree column -- D or M against each name -- and that column turned out to be
 * unreliable wherever it could be checked. Of twelve rows checked against NTU,
 * nine disagreed with the record: years off by a year in both directions, a
 * master's marked D, a doctorate marked M, and one person who took both degrees
 * here listed once. So no name from that list is published on the strength of
 * the list alone. This is how the record gets read instead.
 *
 * WHAT "NOT FOUND" MEANS, and it is the most useful thing this script says.
 * Mode A below reads every record the repository has under 曾宇鳳's supervision
 * and finds that it already contains every record this site links. The search
 * therefore sees the whole set, and a former member with no record in it did
 * not write a thesis under her supervision. For the 2008-2016 cohort that is
 * the expected answer, not a gap: those are 大學部專題生, undergraduates doing a
 * project in the lab, and several of them went on to a master's in another
 * professor's lab -- 曾子家 under 傅楸善, 盧泓志 under 王傑智, 黃雍文 under
 * 歐陽彥正. Their records are real and are not evidence about this lab.
 *
 * Usage:
 *   node scripts/find-thesis-records.mjs
 *       Mode A. Read every 曾宇鳳-advised record and report the ones no row in
 *       data/ links to, so an unlinked record can be attached to its person.
 *
 *   node scripts/find-thesis-records.mjs 李謙 楊淯元
 *       Mode B. Search by author name and print every candidate record with its
 *       advisor, date, degree and unit. Names are substring-matched, so results
 *       are candidates: a search for 陳琤 also returns 陳琬琤.
 *
 * Writes nothing. Like its sibling it is a report, not a test -- it reaches a
 * university library's DSpace, and a red light nobody can fix is a red light
 * people learn to ignore.
 */

import { readFile } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';
import {
  ADVISOR, RECORD_HOST, codeForUnit, describeRecord, fetchRecord, handleOf, recordUrl,
  searchHandles,
} from './lib/thesis-records.mjs';

const ROOT = path.resolve(import.meta.dirname, '..');

/**
 * Every handle the site already links, from both files that carry rows.
 *
 * Deliberately not the merged on-page set that allAlumni() builds: whether a
 * row currently renders makes no difference to whether its record is already
 * known, and the merge rule has enough copies in this repository already.
 */
async function linkedHandles() {
  const linked = new Map();
  for (const [file, key] of [['alumni-historical.yml', 'alumni'], ['members.yml', 'members']]) {
    const parsed = YAML.parse(await readFile(path.join(ROOT, 'data', file), 'utf8')) ?? {};
    for (const row of parsed[key] ?? []) {
      if (row.thesis_url) linked.set(handleOf(row.thesis_url), row.name);
    }
  }
  return linked;
}

function report(handle, record) {
  const unit = record.unit ? `${record.unit}${codeForUnit(record.unit) ? ` (${codeForUnit(record.unit)})` : ''}` : '';
  console.log(`  ${recordUrl(handle)}`);
  console.log(`    author   ${record.author}`);
  console.log(`    advisor  ${record.advisor}${record.byAdvisor ? '' : `   <-- not ${ADVISOR}`}`);
  console.log(`    口試     ${record.accepted || '(none in the record)'}   ${record.degreeZh} ${record.degree ? `(${record.degree})` : ''}`);
  if (unit) console.log(`    unit     ${unit}`);
  console.log(`    title    ${record.title}`);
  console.log('');
}

/** Mode A: the advisor's whole shelf, minus what we already link. */
async function findUnlinked() {
  const linked = await linkedHandles();
  console.log(`reading every ${ADVISOR}-advised record on ${RECORD_HOST}\n`);

  const handles = await searchHandles('advisor', ADVISOR, { pages: 8 });
  const unlinked = handles.filter((handle) => !linked.has(handle));
  const missing = [...linked.keys()].filter((handle) => !handles.includes(handle));

  console.log(`${handles.length} records under ${ADVISOR}; `
    + `the site links ${linked.size} of them, ${unlinked.length} are unlinked\n`);

  // The search missing a record we already link would mean it does not see the
  // whole set, and every "no record exists" conclusion drawn from it would be
  // worth nothing. Reported first because it invalidates the rest.
  if (missing.length) {
    console.log(`WARNING the search did not return ${missing.length} record(s) this site links:`);
    for (const handle of missing) console.log(`  ${handle}  ${linked.get(handle)}`);
    console.log('\nso "not found" below cannot be trusted -- the search is not seeing everything\n');
  }

  for (const handle of unlinked) {
    const record = describeRecord(await fetchRecord(handle));
    report(handle, record);
  }

  if (!unlinked.length) console.log('nothing unlinked: every record has a row.');
}

/** Mode B: what the repository holds under a name, whoever advised it. */
async function findByName(names) {
  const linked = await linkedHandles();
  console.log(`searching ${RECORD_HOST} by author name\n`);

  for (const name of names) {
    const handles = await searchHandles('author', name);
    if (!handles.length) {
      console.log(`${name}: no record under this name at all`);
      console.log(`  -- never wrote a thesis at NTU, or wrote it under another name\n`);
      continue;
    }

    const records = [];
    for (const handle of handles) {
      records.push([handle, describeRecord(await fetchRecord(handle))]);
    }
    // An exact author match first, then this lab's own, because a substring
    // search buries the person being asked about among their namesakes.
    const mine = records.filter(([, r]) => r.byAdvisor && r.author.includes(name));
    const others = records.filter((row) => !mine.includes(row));

    console.log(`${name}: ${handles.length} candidate record(s), `
      + `${mine.length} under ${ADVISOR}`);
    for (const [handle, record] of [...mine, ...others]) {
      const already = linked.get(handle);
      if (already) console.log(`  (already linked from "${already}")`);
      report(handle, record);
    }
  }
}

async function main() {
  const names = process.argv.slice(2);
  if (names.length) await findByName(names);
  else await findUnlinked();
}

await main();
