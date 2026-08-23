// Consistency between the data files and what actually renders.
//
// These catch the failure mode where a page's markup carries an assumption the
// data has outgrown. It has already happened once: the research pillars grew
// from two to five while the ledger indices came from a four-entry array, so the
// fifth pillar rendered with no index and nothing said so -- LedgerItem simply
// omits an undefined index.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, readdir, stat } from 'node:fs/promises';
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

test('no built page links to an internal path that was not built', async () => {
  // The news items carried the old site's relative URLs -- ../img/x.jpg,
  // ../SPARK, files/y.PDF -- which resolved against /news/ and every one 404'd.
  // A broken link renders as a perfectly ordinary link, so nothing said so.
  const files = [];
  const walk = async (dir) => {
    for (const entry of await readdir(dir, { withFileTypes: true })) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) await walk(full);
      else if (entry.name.endsWith('.html')) files.push(full);
    }
  };
  if (!existsSync(DIST)) return; // covered by the skip on the other build tests
  await walk(DIST);

  for (const file of files) {
    const html = await readFile(file, 'utf8');
    for (const [, href] of html.matchAll(/<a[^>]+href="([^"]+)"/g)) {
      if (/^(?:https?:|mailto:|#|\/\/)/.test(href)) continue;
      assert.ok(
        href.startsWith('/'),
        `${path.relative(ROOT, file)} links to "${href}", which is relative to wherever the `
        + 'page happens to sit -- internal links are root-absolute here',
      );
      const target = decodeURIComponent(href.split('#')[0]);
      const asFile = path.join(DIST, target);
      const asPage = path.join(DIST, target, 'index.html');
      assert.ok(
        existsSync(asFile) || existsSync(asPage),
        `${path.relative(ROOT, file)} links to ${href}, which was not built`,
      );
    }
  }
});

test('no news item prints its own link labels twice', async () => {
  // The migration kept the old page's bracketed anchor text inside the item and
  // also extracted the anchors, so each label rendered twice in a row.
  const news = YAML.parse(await readFile(path.join(ROOT, 'data', 'news.yml'), 'utf8')).news ?? [];
  // Both halves of the pair, and both halves of a label: the duplication can be
  // introduced in one language and not the other.
  const both = (value) => (typeof value === 'string' ? [value] : Object.values(value ?? {}));
  for (const item of news) {
    for (const link of item.links ?? []) {
      for (const text of both(item.text)) {
        for (const label of both(link.label)) {
          assert.ok(
            !text.includes(`[${label}]`),
            `a news item names its own link label in its text ("[${label}]"), `
            + 'which renders it twice',
          );
        }
      }
    }
  }
});

test('a member\'s awards are listed in both languages', async () => {
  // The members page falls back to the Chinese list when there is no English
  // one, so a half-translated entry renders Chinese awards under an English
  // heading. Same rule as the news items and the honours citations, and the same
  // reason: the fallback is what makes the omission invisible.
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];

  for (const member of members) {
    if (!member.honors?.length) continue;
    assert.ok(
      member.honors_en?.length,
      `"${member.name}" has awards in Chinese and none in English`,
    );
    assert.equal(
      member.honors.length, member.honors_en.length,
      `"${member.name}" lists ${member.honors.length} awards in Chinese and `
      + `${member.honors_en.length} in English`,
    );
  }
});

test('every news item is written in both languages', async () => {
  // The same rule the honours list has, and for the same reason: getNews() falls
  // back to the Chinese when there is no English, so an untranslated item shows
  // as Chinese in the middle of the English page and the page still renders.
  // Every item was a plain string until 2026-08-23, which is exactly how the
  // English archive came to be a Chinese one.
  const news = YAML.parse(await readFile(path.join(ROOT, 'data', 'news.yml'), 'utf8')).news ?? [];
  assert.ok(news.length, 'no news items declared');

  for (const [index, item] of news.entries()) {
    assert.equal(
      typeof item.text, 'object',
      `news item ${index + 1} is a plain string, so the English page will show `
      + `Chinese: ${String(item.text).slice(0, 40)} -- write it as a zh/en pair`,
    );
    for (const locale of ['zh', 'en']) {
      assert.ok(
        item.text?.[locale]?.trim(),
        `news item ${index + 1} has no ${locale} text`,
      );
    }
    // A label that is a name is the same in both languages and stays a string.
    // One that is a pair must be a whole pair.
    for (const link of item.links ?? []) {
      if (typeof link.label === 'string') continue;
      for (const locale of ['zh', 'en']) {
        assert.ok(
          link.label?.[locale]?.trim(),
          `news item ${index + 1} has a link label with no ${locale}`,
        );
      }
    }
  }
});

test('every image the site serves has alternative text and a sane weight', {
  skip: existsSync(DIST) ? false : 'run `npm run build` first',
}, async () => {
  // Two rules that only a test can hold.
  //
  // Alt text: an <img> with none is invisible to a screen reader and to a search
  // engine, and this site exists because its content was invisible to search
  // engines. The lab's own photographs are the likeliest to arrive without it.
  //
  // Weight: nothing here processes images -- public/ is copied verbatim -- so a
  // 4 MB photograph straight off a phone ships as a 4 MB photograph. The largest
  // image the site serves today is 332 KB, and the widest column it can land in
  // is 1180px, so 500 KB is generous and still catches an unresized original.
  const MAX_KB = 500;
  const files = [];
  const walk = async (dir) => {
    for (const entry of await readdir(dir, { withFileTypes: true })) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) await walk(full);
      else if (entry.name.endsWith('.html')) files.push(full);
    }
  };
  await walk(DIST);

  const seen = new Set();
  for (const file of files) {
    const html = await readFile(file, 'utf8');
    // The whole tag, so alt is found whether it sits before or after src.
    for (const [tag] of html.matchAll(/<img\b[^>]*>/g)) {
      const src = tag.match(/src="(\/[^"]+)"/)?.[1];
      if (!src) continue;
      const alt = tag.match(/alt="([^"]*)"/);
      assert.ok(
        alt,
        `${path.relative(ROOT, file)} has an <img src="${src}"> with no alt attribute`,
      );
      // The logo lockup is decorative and sits beside the lab's name in text, so
      // an empty alt is correct there -- that is what an empty alt is for.
      if (!src.endsWith('/logo.png')) {
        assert.ok(
          alt[1].trim(),
          `${path.relative(ROOT, file)}: <img src="${src}"> has an empty alt; `
          + 'describe the image, or leave it empty only if it is decorative',
        );
      }

      if (seen.has(src)) continue;
      seen.add(src);
      const asset = path.join(DIST, decodeURIComponent(src));
      if (!existsSync(asset)) continue; // the test below reports this
      const kb = (await stat(asset)).size / 1024;
      assert.ok(
        kb <= MAX_KB,
        `${src} is ${Math.round(kb)} KB, over the ${MAX_KB} KB ceiling -- resize it `
        + 'before committing; nothing in the build will do it for you',
      );
    }
  }
});

test('every honour photograph is declared with a source and alt text', async () => {
  // getHonors() drops an image that has no src or no alt rather than rendering
  // half of one, which would be silent. This is the loud half.
  const honors = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'honors.yml'), 'utf8'),
  ).achievements ?? [];

  for (const [index, entry] of honors.entries()) {
    for (const image of entry.images ?? []) {
      assert.ok(image.src, `honour ${index + 1} has an image with no src`);
      assert.ok(
        String(image.src).startsWith('/img/'),
        `honour ${index + 1} image "${image.src}" must live under /img/`,
      );
      const alt = image.alt;
      assert.ok(alt, `honour ${index + 1} image "${image.src}" has no alt text`);
      for (const locale of ['zh', 'en']) {
        assert.ok(
          typeof alt === 'string' || alt?.[locale]?.trim(),
          `honour ${index + 1} image "${image.src}" has no ${locale} alt text`,
        );
      }
      assert.ok(
        existsSync(path.join(ROOT, 'public', image.src)),
        `honour ${index + 1} image "${image.src}" is not in public/`,
      );
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

test('an English thesis title has a title behind it', async () => {
  // Unlike `area`, the two thesis fields are not both-or-neither: most titles
  // were deposited in English, exist once, and are shown on both pages. What
  // cannot happen is the other way round -- `thesis_en` alone leaves the
  // Chinese page's cell empty while the English one carries a title, and an
  // empty cell in that column reads as "no thesis on record".
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  const historical = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'alumni-historical.yml'), 'utf8'),
  ).alumni ?? [];

  for (const person of [...members, ...historical]) {
    if (!person.thesis_en) continue;
    assert.ok(
      person.thesis,
      `"${person.name}" has an English thesis title and no title in the deposited language`,
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

test('every commissioned system states who commissioned it and when', async () => {
  // These entries exist to hold the facts a catalogue entry cannot: the agency,
  // the start date, and whether it still runs. An entry missing any of them
  // would render as a lab tool with a government URL, which is the confusion the
  // separate section exists to prevent.
  const data = YAML.parse(await readFile(path.join(ROOT, 'data', 'services.yml'), 'utf8')) ?? {};
  for (const [index, system] of (data.commissioned ?? []).entries()) {
    const where = `commissioned system ${index + 1}`;
    assert.ok(system.name, `${where} has no name`);
    assert.match(
      String(system.since ?? ''), /^\d{4}-\d{2}-\d{2}$/,
      `${where} needs a full start date as YYYY-MM-DD, not "${system.since}"`,
    );
    assert.equal(typeof system.ongoing, 'boolean', `${where} must say whether it is ongoing`);
    for (const locale of ['zh', 'en']) {
      assert.ok(system.agency?.[locale]?.trim(), `${where} has no ${locale} commissioning agency`);
      assert.ok(system.description?.[locale]?.trim(), `${where} has no ${locale} description`);
      // Optional, but never half-written: an agency note in one language only
      // would show as Chinese on the English page, the way six honours did.
      if (system.agency_note) {
        assert.ok(
          system.agency_note?.[locale]?.trim(),
          `${where} has an agency note with no ${locale} text`,
        );
      }
    }
    const url = system.links?.[0]?.url ?? '';
    assert.match(url, /^https?:\/\//, `${where} needs an absolute URL, not "${url}"`);
  }
});

test('every honour is written in both languages', async () => {
  // getHonors() falls back to the Chinese citation when there is no English one,
  // which is why six entries sat in the English list in Chinese for as long as
  // they did: the page rendered, and the fallback is invisible unless you read
  // the English page. There are no string-only entries left, so this keeps it
  // that way.
  const honors = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'honors.yml'), 'utf8'),
  ).achievements ?? [];
  assert.ok(honors.length, 'no honours declared');

  for (const [index, entry] of honors.entries()) {
    const citation = entry.citation;
    assert.equal(
      typeof citation, 'object',
      `honour ${index + 1} is a plain string, so the English page will show Chinese: `
      + `${String(citation).slice(0, 40)} -- write it as a zh/en pair`,
    );
    for (const locale of ['zh', 'en']) {
      assert.ok(
        citation?.[locale]?.trim(),
        `honour ${index + 1} has no ${locale} citation`,
      );
    }
    // House style: the year leads, and the sentence closes.
    assert.match(
      citation.zh, /^(19|20)\d{2} 年，/,
      `honour ${index + 1} does not open with "YYYY 年，": ${citation.zh.slice(0, 24)}`,
    );
    assert.match(
      citation.zh, /。$/,
      `honour ${index + 1} does not end with a full stop: ${citation.zh.slice(-24)}`,
    );
  }
});

test('no data file carries markup, script or anchor text from the old site', async () => {
  // The migration scraped the legacy pages, and twice now it brought their
  // machinery with it: publisher markup inside a fetched abstract, and -- found
  // on the rendered Tools page -- the old page's anchor text plus a jQuery
  // carousel init, published as a tool's description and inside its
  // SoftwareApplication node.
  //
  // Nothing about that is visible in a diff of a 400-line YAML file, and the
  // page still builds, so this is the only thing that would say so.
  const RESIDUE = [
    { pattern: /<\/?[a-z][a-z0-9]*[\s/>]/i, what: 'an HTML tag' },
    { pattern: /&(?:[a-z]+|#\d+);/i, what: 'an HTML entity' },
    { pattern: /\$\(['"]/, what: 'a jQuery call' },
    { pattern: /\bfunction\s*\(/, what: 'a function literal' },
    { pattern: /javascript:|onclick=/i, what: 'an inline handler' },
    { pattern: /Link\s*>>/, what: "the old site's anchor text" },
  ];

  const files = (await readdir(path.join(ROOT, 'data'))).filter((f) => f.endsWith('.yml'));
  for (const file of files) {
    const source = await readFile(path.join(ROOT, 'data', file), 'utf8');
    const walk = (node, trail) => {
      if (typeof node === 'string') {
        for (const { pattern, what } of RESIDUE) {
          const hit = node.match(pattern);
          assert.ok(
            !hit,
            `data/${file} at ${trail} contains ${what} (${JSON.stringify(hit?.[0])}): `
            + `${JSON.stringify(node.slice(0, 90))}`,
          );
        }
        return;
      }
      if (Array.isArray(node)) {
        node.forEach((item, index) => walk(item, `${trail}[${index}]`));
        return;
      }
      if (node && typeof node === 'object') {
        for (const [key, value] of Object.entries(node)) walk(value, `${trail}.${key}`);
      }
    };
    // Keys named for a URL or a file path legitimately hold neither markup nor
    // script, so the whole tree is walked -- comments are not parsed at all.
    walk(YAML.parse(source), file.replace(/\.yml$/, ''));
  }
});

test('the numbers in the About prose match the data files', async () => {
  // Prose numbers go stale silently: the About paragraph claimed 117 journal
  // articles since 2003, 41 members and 66 alumni. The first was wrong because
  // the lab was founded in 2006 and the four earliest articles are the PI's own
  // postdoc work; the other two were simply out of date by four people. Nothing
  // said so, and nothing would have.
  const data = await pages();
  const LAB_FOUNDED = Number(
    (await readFile(path.join(ROOT, 'src', 'lib', 'data.ts'), 'utf8'))
      .match(/LAB_FOUNDED = (\d{4})/)?.[1],
  );
  assert.ok(LAB_FOUNDED, 'could not read LAB_FOUNDED from src/lib/data.ts');

  const publications = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'publications.yml'), 'utf8'),
  ).publications ?? [];
  const legacy = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'publications-legacy.yml'), 'utf8'),
  ) ?? {};
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  const historical = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'alumni-historical.yml'), 'utf8'),
  ).alumni ?? [];
  const services = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'services.yml'), 'utf8'),
  ).services ?? [];

  const current = members.filter((member) => member.role !== 'alumni');
  const vaultAlumni = members.filter((member) => member.role === 'alumni');
  const alumniNames = new Set(vaultAlumni.map((member) => member.name));

  const expected = {
    journal: publications.filter((entry) => (entry.year ?? 0) >= LAB_FOUNDED).length,
    recent: publications.filter((entry) => (entry.year ?? 0) >= 2020).length,
    conference: (legacy.conference_papers ?? []).length,
    patents: (legacy.patents ?? []).length,
    services: services.length,
    members: current.length,
    // Same rule as allAlumni(): the vault's own, plus the historical rows for
    // people it does not already carry. Someone who graduated here and stayed
    // on is in both sections and is counted, which is the point of counting
    // alumni. This clause used to exclude them and the page did too, and when
    // the page stopped, nothing failed -- hence the row count below.
    alumni: vaultAlumni.length
      + historical.filter((person) => !alumniNames.has(person.name)).length,
  };

  // The paragraph that carries the counts is the one naming cmdm.tw.
  for (const locale of ['zh', 'en']) {
    const paragraph = (data.about?.[locale]?.paragraphs ?? []).find((text) => text.includes('cmdm.tw'));
    assert.ok(paragraph, `the ${locale} About text has no paragraph of counts`);
    const numbers = new Set([...paragraph.matchAll(/\d+/g)].map((m) => Number(m[0])));

    for (const [what, value] of Object.entries(expected)) {
      if (what === 'services') continue; // written as a word ("eight" / 8 項)
      assert.ok(
        numbers.has(value),
        `the ${locale} About paragraph does not state ${value} for ${what}; it says: ${paragraph}`,
      );
    }
    assert.ok(
      numbers.has(LAB_FOUNDED),
      `the ${locale} About paragraph counts from a year other than ${LAB_FOUNDED}`,
    );
    assert.ok(
      !numbers.has(2003),
      `the ${locale} About paragraph still counts from 2003; the lab was founded in ${LAB_FOUNDED}`,
    );
  }
});

test('an alumnus\'s programme is a code or a pair, never bare Chinese', async () => {
  // The label helper turns a code into each locale's name and passes anything
  // else through as it stands, so a free-text department renders in Chinese on
  // the English page. Forty-one rows did exactly that until 2026-08-23, and
  // nothing failed, because from the code's point of view a string is a string.
  const dataTs = await readFile(path.join(ROOT, 'src', 'lib', 'data.ts'), 'utf8');
  const codes = [...(dataTs.match(/DEPARTMENT_LABELS[^=]*=\s*\{([\s\S]*?)\n\};/)?.[1] ?? '')
    .matchAll(/^ {2}([A-Z]+):/gm)].map((m) => m[1]);
  assert.ok(codes.length, 'could not read DEPARTMENT_LABELS from src/lib/data.ts');

  const historical = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'alumni-historical.yml'), 'utf8'),
  ).alumni ?? [];

  for (const person of historical) {
    const department = person.department;
    if (!department) continue;
    if (typeof department === 'string') {
      assert.ok(
        codes.includes(department),
        `"${person.name}" has department "${department}", which is neither one of `
        + `${codes.join(', ')} nor a zh/en pair -- the English page would show it as it is`,
      );
      continue;
    }
    // Not a programme, so not a code: it has to carry both languages itself.
    for (const locale of ['zh', 'en']) {
      assert.ok(
        department?.[locale]?.trim(),
        `"${person.name}" has a department pair with no ${locale}`,
      );
    }
  }
});

test('a thesis_url is a handle in the one repository the checker reads', async () => {
  // scripts/check-thesis-records.mjs runs monthly and talks to NTU. A typo here
  // would surface as one line in a scheduled job's log a fortnight later, or as
  // a request to a host we never meant to touch, so the shape is checked on
  // every commit instead. What the URL *contains* is the scheduled job's
  // business; that it is the right kind of URL is this one's.
  const HANDLE = /^https:\/\/tdr\.lib\.ntu\.edu\.tw\/jspui\/handle\/123456789\/\d+$/;
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  const historical = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'alumni-historical.yml'), 'utf8'),
  ).alumni ?? [];

  const seen = new Map();
  for (const person of [...members, ...historical]) {
    if (!person.thesis_url) continue;
    assert.match(
      person.thesis_url, HANDLE,
      `"${person.name}" has thesis_url "${person.thesis_url}", which is not an NTU handle`,
    );
    // One thesis, one author. The same handle on two people means a record was
    // copied onto the wrong row, which the scheduled check would only catch for
    // whichever of them the advisor and author fields disagree with.
    const other = seen.get(person.thesis_url);
    assert.ok(
      !other,
      `"${person.name}" and "${other}" both point at ${person.thesis_url}`,
    );
    seen.set(person.thesis_url, person.name);
    if (person.role) {
      assert.equal(
        person.role, 'alumni',
        `"${person.name}" has a thesis_url but is not an alumnus`,
      );
    }
  }
});

test('every thesis record reachable from the data is linked on the page', {
  skip: existsSync(DIST) ? false : 'run `npm run build` first',
}, async () => {
  // The title renders through a fallback -- English title, else the deposited
  // one, else nothing -- and the link hangs off whether that produced anything.
  // A row with a record and no title silently drops its link, which is how
  // 高紀威 was found: 45 links against 46 records.
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  const historical = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'alumni-historical.yml'), 'utf8'),
  ).alumni ?? [];
  const vault = new Set(members.filter((m) => m.role === 'alumni').map((m) => m.name));
  const onPage = [
    ...members.filter((m) => m.role === 'alumni'),
    ...historical.filter((p) => !vault.has(p.name)),
  ].filter((p) => p.thesis_url);

  for (const locale of [['members'], ['en', 'members']]) {
    const html = await readFile(path.join(DIST, ...locale, 'index.html'), 'utf8');
    for (const person of onPage) {
      assert.ok(
        html.includes(`href="${person.thesis_url}"`),
        `/${locale.join('/')}/ does not link "${person.name}" to their thesis record`,
      );
    }
  }
});

test('the About page\'s alumni count is the length of the alumni table', {
  skip: existsSync(DIST) ? false : 'run `npm run build` first',
}, async () => {
  // The test above computes the count from the data files, which means it holds
  // a second copy of the rule allAlumni() implements. The two drifted once: the
  // page started listing graduates who stayed on and the count did not follow,
  // and nothing said so because both sides of that assertion were wrong in the
  // same way. This one counts the rows that actually rendered.
  const html = await readFile(path.join(DIST, 'members', 'index.html'), 'utf8');
  const start = html.indexOf('id="role-alumni"');
  assert.ok(start > -1, 'the members page has no alumni section');
  const table = html.slice(start, html.indexOf('</section>', start));
  // The year cell, which every row has and the header does not.
  const rows = (table.match(/<td class="ds-num"/g) ?? []).length;
  assert.ok(rows > 0, 'found no rows in the alumni table');

  const paragraph = ((await pages()).about?.zh?.paragraphs ?? [])
    .find((text) => text.includes('cmdm.tw'));
  const numbers = new Set([...paragraph.matchAll(/\d+/g)].map((m) => Number(m[0])));
  assert.ok(
    numbers.has(rows),
    `the alumni table has ${rows} rows and the About paragraph states none of `
    + `${[...numbers].join(', ')}`,
  );
});

test('the front page defers to the news archive without losing an item', {
  skip: existsSync(DIST) ? false : 'run `npm run build` first',
}, async () => {
  // The home page shows the newest few news items; the point of capping it was
  // to stop a seven-year-old item leading the front page, not to drop the older
  // ones. Every item must therefore still be on /news/, and the front page must
  // link there whenever it is holding some back.
  //
  // What this does NOT check is that the cap is small or that the news is fresh.
  // Raising HOME_NEWS_ITEMS is a deliberate edit, and a test that failed because
  // nothing had happened lately would be a nag, not a tripwire. The rule here is
  // only that nothing is published to no page, which is the failure that hides.
  const news = YAML.parse(await readFile(path.join(ROOT, 'data', 'news.yml'), 'utf8')).news ?? [];
  assert.ok(news.length, 'no news items declared');

  const home = await readFile(path.join(DIST, 'index.html'), 'utf8');
  const archive = await readFile(path.join(DIST, 'news', 'index.html'), 'utf8');

  // The five characters Astro escapes. The double quote was missing until the
  // news was translated: every Chinese item quotes with 「」 and never produced
  // one, so the gap could not show until an English title did.
  const escape = (text) => text
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  // Per locale, since the item exists twice now. An untranslated item would
  // still pass this -- getNews() falls back -- which is what the bilingual test
  // above is for; this one is about nothing being published to no page.
  const enArchive = await readFile(path.join(DIST, 'en', 'news', 'index.html'), 'utf8');
  for (const item of news) {
    for (const [page, where] of [[archive, 'zh'], [enArchive, 'en']]) {
      const text = item.text?.[where] ?? item.text;
      assert.ok(
        page.includes(escape(text)),
        `a news item is on no ${where} page: ${text.slice(0, 40)}`,
      );
    }
  }

  const cap = Number(
    (await readFile(path.join(ROOT, 'src', 'components', 'HomePage.astro'), 'utf8'))
      .match(/HOME_NEWS_ITEMS = (\d+)/)?.[1],
  );
  assert.ok(cap, 'could not read HOME_NEWS_ITEMS from HomePage.astro');

  const section = home.slice(home.indexOf('id="news"'));
  const list = section.slice(0, section.indexOf('</ul>'));
  const shown = [...list.matchAll(/<li>/g)].length;
  assert.ok(shown <= cap, `the front page shows ${shown} news items, over the cap of ${cap}`);
  if (news.length > cap) {
    assert.ok(
      section.slice(0, section.indexOf('</section>')).includes('/news/'),
      'the front page holds news items back but does not link the archive',
    );
  }
});

test('no page writes out the list of affiliated units by hand', {
  skip: existsSync(DIST) ? false : 'run `npm run build` first',
}, async () => {
  // The list of units the lab draws students from was written out twice -- the
  // home colophon and the members lede -- and both copies were wrong in the same
  // two ways: 基因體所, which is not the name of anything, and no 網媒所 although
  // a member is in one. Both are derived from the roster now, and the name that
  // does not exist must not come back.
  //
  // This checks the rendered pages rather than the source, because the failure is
  // a string appearing on a page, wherever it came from.
  for (const file of await readdir(DIST, { recursive: true, withFileTypes: true })) {
    if (!file.name.endsWith('.html')) continue;
    const full = path.join(file.parentPath ?? file.path, file.name);
    const html = await readFile(full, 'utf8');
    assert.ok(
      !html.includes('基因體所'),
      `${path.relative(ROOT, full)} says 基因體所; the unit is 基因體與系統生物學學位學程 (GSB)`,
    );
  }

  // And the members lede must be filled in, not printed with its placeholder.
  const members = await readFile(path.join(DIST, 'members', 'index.html'), 'utf8');
  assert.ok(!members.includes('{units}'), 'the members lede still shows its {units} placeholder');
});
