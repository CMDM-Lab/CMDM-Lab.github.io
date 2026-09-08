// Reading NTU's thesis repository.
//
// Shared by the two scripts that talk to it: check-thesis-records.mjs, which
// verifies the years we publish against the records we already link, and
// find-thesis-records.mjs, which goes looking for records we do not.
//
// NTU's repository is a DSpace JSPUI instance with no API. Everything here is
// therefore HTML scraping of two public pages -- the `?mode=full` record view
// and `simple-search` -- and it will break the day the library upgrades DSpace.
// That is a known cost, accepted because the alternative is publishing
// graduation years nobody can check.

import { fetchText } from './fetch-util.mjs';

/** The one host these scripts will talk to. A `thesis_url` elsewhere is a bug. */
export const RECORD_HOST = 'tdr.lib.ntu.edu.tw';

/**
 * The advisor whose students this lab's page is about.
 *
 * Every check here turns on this name. A record that does not carry it is
 * either the wrong record or someone else's thesis, and in both cases it says
 * nothing about a person on this site: several former members took a graduate
 * degree in another lab afterwards, and their records are real, checkable, and
 * not evidence of anything the members page claims.
 */
export const ADVISOR = '曾宇鳳';

export const DEGREE_FROM_ZH = { 碩士: 'masters', 博士: 'phd' };

/**
 * Department codes, as the repository writes the unit out in full.
 *
 * Kept here rather than imported from src/lib/data.ts because that file is
 * TypeScript and these are plain scripts; tests/members-privacy.test.mjs already
 * asserts the codes agree across the two places that matter. A unit missing
 * from this map is reported, not assumed wrong.
 */
export const UNIT_FOR_CODE = {
  // A list, because the repository's own wording for the home department moved:
  // a thesis accepted in September 2022 is filed under 資訊工程學研究所 and one
  // accepted in July 2023 under 資訊工程學系. Either is right for its own
  // graduate. Which of the two the page prints is decided by `bareBefore` in
  // src/lib/data.ts, off the graduation year -- and the year is checked here, so
  // matching either name costs nothing.
  //
  // These are the repository's strings, not the site's: it writes MHI out as
  // 智慧醫療與健康資訊碩士學位學程 where DEPARTMENT_LABELS has
  // 智慧醫療與健康資訊學程.
  CSIE: ['資訊工程學系', '資訊工程學研究所'],
  BEBI: ['生醫電子與資訊學研究所'],
  GSB: ['基因體與系統生物學學位學程'],
  MHI: ['智慧醫療與健康資訊碩士學位學程'],
  GINM: ['資訊網路與多媒體研究所'],
  PHARM: ['藥學研究所'],
  EE: ['電機工程學研究所'],
};

export function decodeEntities(text) {
  return text
    .replace(/&#(\d+);/g, (_, code) => String.fromCharCode(Number(code)))
    .replace(/&#x([0-9a-f]+);/gi, (_, code) => String.fromCharCode(parseInt(code, 16)))
    .replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"').replace(/&apos;/g, "'").replace(/&nbsp;/g, ' ');
}

/** Parse the `?mode=full` page into its dc.* fields. */
export function parseRecord(html) {
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

/** The numeric handle at the end of a record URL, which is its identity. */
export function handleOf(url) {
  return String(url ?? '').trim().replace(/[?#].*$/, '').replace(/\/$/, '').split('/').pop();
}

export function recordUrl(handle) {
  return `https://${RECORD_HOST}/jspui/handle/123456789/${handle}`;
}

/** Fetch one record and return its dc.* fields. */
export async function fetchRecord(handle) {
  const url = new URL(recordUrl(handle));
  url.searchParams.set('mode', 'full');
  return parseRecord(await fetchText(url.href));
}

/**
 * Search the repository and return the handles it matched, newest page first.
 *
 * `field` is a DSpace search index -- `advisor` and `author` are the two this
 * lab needs. `contains` rather than `equals` on purpose: a record whose advisor
 * field carries the Latin name alongside the Chinese one, or two advisors, does
 * not equal any single string. On the advisor index that widened the result
 * from 26 records to 49, and the 49 turned out to include every record this
 * site already links, which is how we know the search sees the whole set.
 *
 * A name is a substring match, so results are candidates, not answers: a search
 * for 陳琤 returns 陳琬琤 as well. Read the record before believing it.
 */
export async function searchHandles(field, value, { pages = 5, perPage = 20 } = {}) {
  const handles = [];
  const seen = new Set();

  for (let page = 0; page < pages; page += 1) {
    const url = new URL(`https://${RECORD_HOST}/jspui/simple-search`);
    url.searchParams.set('query', '');
    url.searchParams.set('filter_field_1', field);
    url.searchParams.set('filter_type_1', 'contains');
    url.searchParams.set('filter_value_1', value);
    url.searchParams.set('rpp', String(perPage));
    url.searchParams.set('start', String(page * perPage));

    const html = await fetchText(url.href);
    const onPage = [...html.matchAll(/\/jspui\/handle\/123456789\/(\d+)/g)].map((m) => m[1]);
    let added = 0;
    for (const handle of onPage) {
      if (seen.has(handle)) continue;
      seen.add(handle);
      handles.push(handle);
      added += 1;
    }
    // DSpace repeats the last page rather than returning an empty one, so the
    // stop condition is "this page told us nothing new", not "this page was
    // empty". Without it the loop pages forever on a one-result search.
    if (added === 0 || onPage.length === 0) break;
  }

  return handles;
}

/** A one-line summary of a record, for a report a human reads. */
export function describeRecord(fields) {
  const first = (key) => (fields[key] ?? [])[0] ?? '';
  const all = (key) => (fields[key] ?? []).join(' / ');
  const accepted = first('dc.date.accepted') || first('dc.date.submitted');
  return {
    author: all('dc.contributor.author'),
    advisor: all('dc.contributor.advisor'),
    accepted,
    year: Number(accepted.slice(0, 4)) || null,
    degree: DEGREE_FROM_ZH[first('dc.description.degree')] ?? '',
    degreeZh: first('dc.description.degree'),
    unit: first('dc.contributor.author-dept'),
    title: all('dc.title'),
    byAdvisor: all('dc.contributor.advisor').includes(ADVISOR),
  };
}

/** The department code whose repository wording matches `unit`, if any. */
export function codeForUnit(unit) {
  for (const [code, names] of Object.entries(UNIT_FOR_CODE)) {
    if (names.includes(unit)) return code;
  }
  return '';
}
