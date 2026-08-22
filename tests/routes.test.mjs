// Tripwire for URL continuity.
//
// The legacy `?content=<key>` mapping is duplicated in three places by
// necessity: src/lib/routes.ts (the source of truth), the inline shim on the
// home page (generated from it, so safe), and public/index_zh.html (a static
// file that cannot import anything, so hand-written).
//
// That third copy is the one that rots. These tests fail when it drifts, and
// when a declared route has no page to serve.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';

const ROOT = path.resolve(import.meta.dirname, '..');

/** Pull the two exported tables out of routes.ts without a TS toolchain. */
async function readRoutesSource() {
  const source = await readFile(path.join(ROOT, 'src', 'lib', 'routes.ts'), 'utf8');

  const legacyBlock = source.match(
    /LEGACY_CONTENT_MAP: Record<string, string> = \{([\s\S]*?)\n\};/,
  );
  assert.ok(legacyBlock, 'could not locate LEGACY_CONTENT_MAP in routes.ts');
  const legacy = {};
  for (const [, key, slug] of legacyBlock[1].matchAll(/^\s*(\w+):\s*'([^']*)',/gm)) {
    legacy[key] = slug;
  }

  const slugs = [...source.matchAll(/\{\s*slug:\s*'([^']*)'/g)].map((match) => match[1]);

  return { legacy, slugs };
}

/** Pull the map out of the static shim's inline script. */
async function readShimMap() {
  const source = await readFile(path.join(ROOT, 'public', 'index_zh.html'), 'utf8');
  const block = source.match(/var map = \{([\s\S]*?)\};/);
  assert.ok(block, 'could not locate the redirect map in public/index_zh.html');
  const map = {};
  for (const [, key, target] of block[1].matchAll(/(\w+):\s*'([^']*)'/g)) {
    map[key] = target;
  }
  return map;
}

test('the static shim covers every legacy content key', async () => {
  const { legacy } = await readRoutesSource();
  const shim = await readShimMap();

  assert.deepEqual(
    Object.keys(shim).sort(),
    Object.keys(legacy).sort(),
    'public/index_zh.html and LEGACY_CONTENT_MAP disagree on which keys exist',
  );

  for (const [key, slug] of Object.entries(legacy)) {
    const expected = slug ? `/${slug}/` : '/';
    assert.equal(
      shim[key], expected,
      `legacy key "${key}" should redirect to ${expected}, shim says ${shim[key]}`,
    );
  }
});

test('every legacy key maps to a declared route', async () => {
  const { legacy, slugs } = await readRoutesSource();
  for (const [key, slug] of Object.entries(legacy)) {
    assert.ok(
      slugs.includes(slug),
      `legacy key "${key}" points at slug "${slug}", which is not in ROUTES`,
    );
  }
});

test('every declared route has a page file in both locales', async () => {
  const { slugs } = await readRoutesSource();
  for (const slug of slugs) {
    const zh = slug ? `src/pages/${slug}.astro` : 'src/pages/index.astro';
    const en = slug ? `src/pages/en/${slug}.astro` : 'src/pages/en/index.astro';
    assert.ok(existsSync(path.join(ROOT, zh)), `missing ${zh}`);
    assert.ok(existsSync(path.join(ROOT, en)), `missing ${en}`);
  }
});

test('the two pages that were unreachable on the old site now have routes', async () => {
  // contents/publication*.html and contents/prof_acts*.html existed but no
  // router branch or nav link pointed at them. Regressing that would quietly
  // re-hide content the migration recovered.
  const { legacy } = await readRoutesSource();
  assert.equal(legacy.publication, 'publications');
  assert.equal(legacy.prof_acts, 'activities');
});

test('built output contains a page for every sitemap URL', {
  skip: existsSync(path.join(ROOT, 'dist', 'sitemap-0.xml'))
    ? false
    : 'run `npm run build` first',
}, async () => {
  const sitemap = await readFile(path.join(ROOT, 'dist', 'sitemap-0.xml'), 'utf8');
  const locs = [...sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)].map((match) => match[1]);
  assert.ok(locs.length > 0, 'sitemap lists no URLs');

  for (const loc of locs) {
    const pathname = new URL(loc).pathname;
    const file = path.join(ROOT, 'dist', pathname, 'index.html');
    assert.ok(existsSync(file), `sitemap lists ${loc} but ${pathname}index.html was not built`);
  }
});

test('the redirect shim is excluded from the sitemap and marked noindex', {
  skip: existsSync(path.join(ROOT, 'dist', 'sitemap-0.xml'))
    ? false
    : 'run `npm run build` first',
}, async () => {
  const sitemap = await readFile(path.join(ROOT, 'dist', 'sitemap-0.xml'), 'utf8');
  assert.ok(!sitemap.includes('index_zh.html'), 'the shim must not be advertised in the sitemap');

  const shim = await readFile(path.join(ROOT, 'dist', 'index_zh.html'), 'utf8');
  assert.match(shim, /<meta name="robots" content="noindex/,
    'the shim must be noindex so it does not compete with the real pages');
});
