#!/usr/bin/env node
/**
 * Check the alumni table against NTU's thesis repository.
 *
 * The graduation years on the members page are calendar years -- the year the
 * 口試 was passed. Nothing in this repository can prove that. The year lives in
 * someone else's database, so the only honest check is to go and read it, which
 * is what this does: for every alumnus carrying a `thesis_url`, fetch the
 * record and compare what we publish against what NTU holds.
 *
 * WHY THIS IS NOT A TEST. `npm test` runs offline, in a second, on every commit
 * and inside the sync workflow. A check that reaches a university library's
 * DSpace fails when that server is slow, or migrating, or behind maintenance --
 * and a red light that goes red for reasons the author cannot fix is a red light
 * people learn to ignore. So this runs on its own schedule, and it distinguishes
 * "these two disagree", which is a failure, from "I could not ask", which is not.
 *
 * WHICH FIELD SETTLES THE YEAR. `dc.date.accepted`, the 口試通過日期. Not
 * `dc.date.issued`, which is the 出版年 and is inconsistent across a single
 * semester -- two students who defended a week apart in 113-1 are filed under
 * 2024 and 2025. Not `dc.date.submitted`, which is sometimes the deposit date
 * instead: 許至廷 passed in 2020 and submitted in February 2021.
 *
 * Usage:
 *   node scripts/check-thesis-records.mjs          # check everyone
 *   node scripts/check-thesis-records.mjs 蕭毅 王恆  # check named people only
 */

import { readFile } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';
import { fetchText } from './lib/fetch-util.mjs';

const ROOT = path.resolve(import.meta.dirname, '..');
const ADVISOR = '曾宇鳳';

/** The one host this script will talk to. A `thesis_url` elsewhere is a bug. */
const RECORD_HOST = 'tdr.lib.ntu.edu.tw';

const DEGREE_FROM_ZH = { 碩士: 'masters', 博士: 'phd' };

/**
 * Department codes, as the repository writes the unit out in full.
 *
 * Kept here rather than imported from src/lib/data.ts because that file is
 * TypeScript and this is a plain script; tests/members-privacy.test.mjs already
 * asserts the codes agree across the two places that matter. A unit missing
 * from this map is reported, not assumed wrong.
 */
const UNIT_FOR_CODE = {
  CSIE: '資訊工程學系',
  BEBI: '生醫電子與資訊學研究所',
  GSB: '基因體與系統生物學學位學程',
  MHI: '智慧醫療與健康資訊碩士學位學程',
  GINM: '資訊網路與多媒體研究所',
};

/** Parse the `?mode=full` page into its dc.* fields. */
function parseRecord(html) {
  const fields = {};
  for (const row of html.matchAll(/<tr>([\s\S]*?)<\/tr>/g)) {
    const cells = [...row[1].matchAll(/<td[^>]*>([\s\S]*?)<\/td>/g)]
      .map((cell) => cell[1].replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim())
      .filter(Boolean);
    if (cells.length >= 2 && cells[0].startsWith('dc.')) {
      (fields[cells[0]] ??= []).push(decodeEntities(cells[1]));
    }
  }
  return fields;
}

function decodeEntities(text) {
  return text
    .replace(/&#(\d+);/g, (_, code) => String.fromCharCode(Number(code)))
    .replace(/&#x([0-9a-f]+);/gi, (_, code) => String.fromCharCode(parseInt(code, 16)))
    .replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"').replace(/&apos;/g, "'").replace(/&nbsp;/g, ' ');
}

/**
 * Compare titles loosely, because we transcribe deliberately.
 *
 * 許洸誠's title keeps P-醣蛋白 where the abstract says P-糖蛋白, and 古庭榮's
 * loses the spaces a subscript leaves behind in 5-HT 2 A. Both are correct here
 * and neither matches character for character, so spacing and punctuation come
 * out before comparing and a difference is reported as a note, never a failure.
 */
function titleKey(text) {
  return (text ?? '').toLowerCase().replace(/[^\p{L}\p{N}]+/gu, '');
}

async function main() {
  const only = new Set(process.argv.slice(2));

  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  const historical = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'alumni-historical.yml'), 'utf8'),
  ).alumni ?? [];

  const vaultNames = new Set(members.filter((m) => m.role === 'alumni').map((m) => m.name));
  const people = [
    ...members.filter((m) => m.role === 'alumni'),
    // Same rule as allAlumni(): a historical row the vault already carries is
    // not on the page, so there is nothing to check it against.
    ...historical.filter((p) => !vaultNames.has(p.name)),
  ].filter((p) => p.thesis_url && (only.size === 0 || only.has(p.name)));

  const onPage = members.filter((m) => m.role === 'alumni').length
    + historical.filter((p) => !vaultNames.has(p.name)).length;

  console.log(`checking ${people.length} of ${onPage} alumni rows against ${RECORD_HOST}\n`);

  const failures = [];
  const notes = [];
  let unreachable = 0;
  let uncheckedUnits = 0;

  for (const person of people) {
    let record;
    try {
      const url = new URL(person.thesis_url);
      if (url.host !== RECORD_HOST) {
        failures.push(`${person.name}: thesis_url points at ${url.host}, not ${RECORD_HOST}`);
        continue;
      }
      url.searchParams.set('mode', 'full');
      record = parseRecord(await fetchText(url.href));
    } catch (error) {
      unreachable += 1;
      notes.push(`${person.name}: could not read the record (${error.message})`);
      continue;
    }

    const first = (key) => (record[key] ?? [])[0] ?? '';
    const all = (key) => (record[key] ?? []).join(' ');
    const say = (what) => failures.push(`${person.name}: ${what}`);

    // Is this even the right record? Everything below is meaningless if not.
    if (!all('dc.contributor.advisor').includes(ADVISOR)) {
      say(`the record's advisor is not ${ADVISOR} -- wrong thesis linked`);
      continue;
    }
    if (!all('dc.contributor.author').includes(person.name)) {
      say(`the record's author is not ${person.name} -- wrong thesis linked`);
      continue;
    }

    const accepted = first('dc.date.accepted') || first('dc.date.submitted');
    const year = Number(accepted.slice(0, 4));
    if (!/^\d{4}-\d{2}-\d{2}$/.test(accepted) || year < 2005) {
      notes.push(`${person.name}: no usable 口試 date in the record (${accepted || 'empty'})`);
    } else if (person.graduated !== year) {
      say(`page says ${person.graduated}, 口試 was ${accepted} `
        + `(學年度 ${first('dc.date.schoolyear') || '?'}, 出版年 ${first('dc.date.issued') || '?'})`);
    }

    const degree = DEGREE_FROM_ZH[first('dc.description.degree')];
    // Historical rows fuse the degree into a free-text department; only the
    // structured field is checkable.
    if (person.degree && degree && person.degree !== degree) {
      say(`page says ${person.degree}, the record says ${first('dc.description.degree')}`);
    }

    const unit = first('dc.contributor.author-dept');
    const expected = UNIT_FOR_CODE[person.department];
    if (expected && unit && expected !== unit) {
      say(`page says ${person.department} (${expected}), the record says ${unit}`);
    } else if (person.department && !expected) {
      // The rows recovered from the previous site fuse the unit and the degree
      // into free text, which no map can check. Counted rather than listed:
      // thirty identical lines are how a log stops being read.
      uncheckedUnits += 1;
    }

    const titles = (record['dc.title'] ?? []).map(titleKey);
    for (const [field, value] of [['thesis', person.thesis], ['thesis_en', person.thesis_en]]) {
      if (value && titles.length && !titles.includes(titleKey(value))) {
        notes.push(`${person.name}: ${field} differs from the record's title`);
      }
    }
  }

  for (const note of notes) console.log(`note    ${note}`);
  if (notes.length) console.log('');
  for (const failure of failures) console.log(`MISMATCH ${failure}`);

  console.log(`\n${people.length - unreachable} records read, `
    + `${failures.length} mismatches, ${notes.length} notes`);
  if (uncheckedUnits) {
    console.log(`${uncheckedUnits} units not checked: free text from the previous site`);
  }

  // Could not ask anybody: the repository is down or blocking us. That is not
  // the site being wrong, and failing here would only teach people to rerun.
  if (people.length > 0 && unreachable === people.length) {
    console.log(`\nevery request to ${RECORD_HOST} failed -- reporting no result rather than a failure`);
    return;
  }
  if (failures.length) process.exitCode = 1;
}

await main();
