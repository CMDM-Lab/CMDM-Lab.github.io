// Consistency between the data files and what actually renders.
//
// These catch the failure mode where a page's markup carries an assumption the
// data has outgrown. It has already happened once: the research pillars grew
// from two to five while the ledger indices came from a four-entry array, so the
// fifth pillar rendered with no index and nothing said so -- LedgerItem simply
// omits an undefined index.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import YAML from 'yaml';

const ROOT = path.resolve(import.meta.dirname, '..');
const DIST = path.join(ROOT, 'dist');
const hasBuild = existsSync(DIST);

async function pages() {
  return YAML.parse(await readFile(path.join(ROOT, 'data', 'pages.yml'), 'utf8')) ?? {};
}

test('research pillars are declared symmetrically in both locales', async () => {
  const areas = (await pages()).research_areas ?? {};
  assert.ok(areas.zh?.length, 'no Chinese research pillars declared');
  assert.equal(
    areas.zh.length, areas.en?.length,
    `${areas.zh.length} Chinese pillars but ${areas.en?.length} English ones`,
  );
  for (const [index, group] of areas.zh.entries()) {
    assert.ok(group.group, `Chinese pillar ${index} has no name`);
    assert.ok(group.items?.length, `Chinese pillar "${group.group}" has no items`);
    assert.ok(areas.en[index].group, `English pillar ${index} has no name`);
    assert.ok(areas.en[index].items?.length, `English pillar ${index} has no items`);
  }
});

test('every research pillar renders with an index', {
  skip: hasBuild ? false : 'run `npm run build` first',
}, async () => {
  // The regression this file exists for. An index is dropped silently, so the
  // check is against the built HTML rather than the source.
  const areas = (await pages()).research_areas ?? {};
  const expected = areas.zh?.length ?? 0;

  for (const page of ['index.html', path.join('research', 'index.html')]) {
    const html = await readFile(path.join(DIST, page), 'utf8');

    // Isolate the pillars section. The research page carries a second ledger
    // for representative papers, whose indices restart at I -- counting both
    // would double the total and make this test lie about a real page.
    const start = html.search(/id="(?:areas|pillars)"/);
    assert.ok(start !== -1, `${page} has no research-pillar section`);
    const rest = html.slice(start);
    const end = rest.indexOf('</section>');
    const section = end === -1 ? rest : rest.slice(0, end);

    const indices = [...section.matchAll(/class="ds-kicker">([IVXLC]+)</g)].map((m) => m[1]);
    assert.equal(
      indices.length, expected,
      `${page} renders ${indices.length} pillar indices for ${expected} pillars: ${indices.join(', ')}`,
    );
    // And they must be the roman numerals in order, not repeated or skipped.
    assert.deepEqual(
      indices, ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'].slice(0, expected),
      `${page} pillar indices are out of sequence`,
    );
  }
});

test('every research pillar name appears on the built pages', {
  skip: hasBuild ? false : 'run `npm run build` first',
}, async () => {
  const areas = (await pages()).research_areas ?? {};
  const zhHtml = await readFile(path.join(DIST, 'research', 'index.html'), 'utf8');
  const enHtml = await readFile(path.join(DIST, 'en', 'research', 'index.html'), 'utf8');

  for (const group of areas.zh ?? []) {
    assert.ok(zhHtml.includes(group.group), `"${group.group}" is missing from the Chinese page`);
  }
  for (const group of areas.en ?? []) {
    assert.ok(enHtml.includes(group.group), `"${group.group}" is missing from the English page`);
  }
});

test('no message claims a pillar count that the data contradicts', async () => {
  // A heading reading "Two Research Pillars" over five of them is the kind of
  // thing nobody re-reads after adding the third.
  const areas = (await pages()).research_areas ?? {};
  const count = areas.zh?.length ?? 0;
  const WORDS = {
    2: [/\btwo\b/i, /兩/],
    3: [/\bthree\b/i, /三(?!維)/],
    4: [/\bfour\b/i, /四/],
    5: [/\bfive\b/i, /五/],
  };

  for (const locale of ['zh', 'en']) {
    const messages = YAML.parse(
      await readFile(path.join(ROOT, 'data', 'i18n', `${locale}.yml`), 'utf8'),
    ) ?? {};
    for (const [key, value] of Object.entries(messages)) {
      if (!/^(research|about|home)_/.test(key)) continue;
      for (const [n, patterns] of Object.entries(WORDS)) {
        if (Number(n) === count) continue;
        for (const pattern of patterns) {
          if (!pattern.test(String(value))) continue;
          // Only a number sitting next to the word for a pillar is a problem.
          const nearPillar = /pillar|主軸|研究方向/i.test(String(value));
          assert.ok(
            !nearPillar,
            `${locale}.yml "${key}" says "${n}" next to a pillar word, but there are ${count}`,
          );
        }
      }
    }
  }
});

test('the built pages reference no image that is not shipped', {
  skip: hasBuild ? false : 'run `npm run build` first',
}, async () => {
  // A missing figure is invisible in a diff and shows as a broken image.
  const files = [];
  const walk = async (dir) => {
    for (const entry of await readdir(dir, { withFileTypes: true })) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) await walk(full);
      else if (entry.name.endsWith('.html')) files.push(full);
    }
  };
  await walk(DIST);

  for (const file of files) {
    const html = await readFile(file, 'utf8');
    for (const [, src] of html.matchAll(/<img[^>]+src="(\/[^"]+)"/g)) {
      const asset = path.join(DIST, decodeURIComponent(src));
      assert.ok(
        existsSync(asset),
        `${path.relative(ROOT, file)} references ${src}, which was not built`,
      );
    }
  }
});

test('every member area is one of the declared research pillars', async () => {
  // The members page and the research page must not describe the lab
  // differently. An area invented in the sync script, or a pillar renamed in
  // pages.yml without the classifier following, shows up here.
  const areas = (await pages()).research_areas ?? {};
  const zhPillars = new Set((areas.zh ?? []).map((group) => group.group));
  const enPillars = new Set((areas.en ?? []).map((group) => group.group));

  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];

  // Methods that cut across pillars are allowed, but only if declared in
  // EXTRA_AREAS in the sync script -- so a typo in an override still fails here.
  const sync = await readFile(path.join(ROOT, 'scripts', 'sync-members.mjs'), 'utf8');
  const extraBlock = sync.match(/const EXTRA_AREAS = new Map\(\[([\s\S]*?)\]\);/);
  assert.ok(extraBlock, 'could not find EXTRA_AREAS in sync-members.mjs');
  for (const [, zh, en] of extraBlock[1].matchAll(/\['([^']+)',\s*'([^']+)'\]/g)) {
    zhPillars.add(zh);
    enPillars.add(en);
  }

  for (const member of members) {
    for (const one of (member.area ?? '').split(' · ').filter(Boolean)) {
      assert.ok(
        zhPillars.has(one),
        `"${member.name}" has area "${one}", which is neither a pillar nor in EXTRA_AREAS`,
      );
    }
    for (const one of (member.area_en ?? '').split(' · ').filter(Boolean)) {
      assert.ok(
        enPillars.has(one),
        `"${member.name}" has area_en "${one}", which is neither a pillar nor in EXTRA_AREAS`,
      );
    }
    assert.equal(
      (member.area ?? '').split(' · ').filter(Boolean).length,
      (member.area_en ?? '').split(' · ').filter(Boolean).length,
      `"${member.name}" lists a different number of areas per locale`,
    );
    // Both or neither, so the English page never falls back to Chinese here.
    assert.equal(
      Boolean(member.area), Boolean(member.area_en),
      `"${member.name}" has an area in only one language`,
    );
  }
});

test('nobody appears twice in the member list', async () => {
  // A graduate who returns as staff is entered as an override on the vault row,
  // not as a second person. Adding them to `additional` instead produces two
  // entries with the same name -- one alumni, one staff.
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  const seen = new Map();
  for (const member of members) {
    assert.ok(
      !seen.has(member.name),
      `"${member.name}" appears twice: as ${seen.get(member.name)} and as ${member.role}`,
    );
    seen.set(member.name, member.role);
  }
});
