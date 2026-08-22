// Ratchet tests for the "Clinical Instrument" design system.
//
// The design handoff asks for these by name. Its own source project guarded
// some of the rules with tests and left four in prose only -- and records that
// those four "had already drifted there". So they are tests here.
//
// Each rule below is one the system cannot enforce by structure alone: nothing
// stops someone adding a shadow, a radius, a dark-mode block or a bare hex.
// A red run naming the file is the only signal that arrives at the moment the
// rule is broken.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';

const ROOT = path.resolve(import.meta.dirname, '..');
const STYLES = path.join(ROOT, 'src', 'styles');
const COMPONENTS = path.join(ROOT, 'src', 'components');
const LAYOUTS = path.join(ROOT, 'src', 'layouts');

async function walk(dir, exts) {
  const out = [];
  for (const entry of await readdir(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) out.push(...await walk(full, exts));
    else if (exts.some((ext) => entry.name.endsWith(ext))) out.push(full);
  }
  return out;
}

const rel = (file) => path.relative(ROOT, file);

async function styleFiles() {
  return walk(STYLES, ['.css']);
}

async function authoredFiles() {
  return [
    ...await walk(COMPONENTS, ['.astro']),
    ...await walk(LAYOUTS, ['.astro']),
    ...await walk(STYLES, ['.css']),
  ];
}

/** Strip comments so a rule named in prose is not mistaken for a violation. */
function stripComments(css) {
  return css.replace(/\/\*[\s\S]*?\*\//g, '');
}

// ---------------------------------------------------------------------------
// Prohibitions
// ---------------------------------------------------------------------------

test('there is no dark mode', async () => {
  // Light-only is a design decision, not a gap. The handoff says so twice.
  for (const file of await authoredFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    assert.ok(
      !/prefers-color-scheme\s*:\s*dark/.test(source),
      `${rel(file)} has a dark-mode media query; this system is light-only by design`,
    );
    assert.ok(!/\bdark:[a-z-]/.test(source), `${rel(file)} uses a dark: variant`);
  }
});

test('every radius is zero, except a true circle', async () => {
  for (const file of await styleFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    for (const [, value] of source.matchAll(/border-radius\s*:\s*([^;}]+)/g)) {
      const radius = value.trim();
      assert.ok(
        radius === '0' || radius === '50%' || radius === 'var(--radius-circle)',
        `${rel(file)} sets border-radius: ${radius}. Flat geometry: only 0, or 50% for a real circle`,
      );
    }
  }
});

test('there is no elevation', async () => {
  // Layering is done with 1px rules, full stop.
  for (const file of await authoredFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    const shadows = [...source.matchAll(/box-shadow\s*:\s*([^;}]+)/g)]
      .map((m) => m[1].trim())
      .filter((value) => value !== 'none' && value !== 'var(--shadow-none)');
    assert.deepEqual(shadows, [], `${rel(file)} declares a shadow: ${shadows.join(', ')}`);
    assert.ok(!/\bfilter\s*:\s*drop-shadow/.test(source), `${rel(file)} uses drop-shadow`);
  }
});

test('colour comes only from the three ramps, never a bare hex in a component', async () => {
  // The global stylesheet defines the tokens and is the one exception.
  const componentFiles = [
    ...await walk(COMPONENTS, ['.astro']),
    ...await walk(LAYOUTS, ['.astro']),
  ];
  for (const file of componentFiles) {
    const source = stripComments(await readFile(file, 'utf8'));
    const hexes = [...source.matchAll(/#[0-9a-fA-F]{3,8}\b/g)].map((m) => m[0]);
    assert.deepEqual(
      hexes, [],
      `${rel(file)} contains a bare hex colour (${hexes.join(', ')}); use a token`,
    );
  }
});

test('no framework default palette names appear', async () => {
  // slate-, blue-, red-… would mean colour arriving from somewhere other than
  // the accent, neutral and brand ramps.
  const FRAMEWORK_PALETTES = /\b(?:slate|gray|zinc|stone|blue|red|amber|emerald|indigo|violet|rose|teal|cyan|lime|sky)-\d{2,3}\b/;
  for (const file of await authoredFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    const match = source.match(FRAMEWORK_PALETTES);
    assert.equal(match, null, `${rel(file)} references ${match?.[0]}`);
  }
});

// ---------------------------------------------------------------------------
// Structure
// ---------------------------------------------------------------------------

test('every rule in the stylesheet lives inside a cascade layer', async () => {
  // Structural, not stylistic: an unlayered declaration beats every layered one
  // regardless of specificity. The design system records a bare unlayered
  // `a { color }` overriding .btn-primary and making a button's text match its
  // own fill. Only :root token blocks, @import, @layer statements and @media
  // are allowed at the top level.
  for (const file of await styleFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    let depth = 0;
    let buffer = '';
    for (const char of source) {
      if (char === '{') {
        if (depth === 0) {
          const selector = buffer.trim().split('\n').pop().trim();
          const allowed = selector.startsWith('@layer')
            || selector.startsWith('@media')
            || selector.startsWith('@supports')
            || selector === ':root'
            || selector.startsWith(':root:lang')
            || selector.startsWith(':root:lang(zh-Hant),')
            || /^:root/.test(selector);
          assert.ok(
            allowed,
            `${rel(file)} has an unlayered rule "${selector}". `
            + 'Wrap it in @layer -- unlayered rules beat every layered style.',
          );
        }
        depth += 1;
        buffer = '';
        continue;
      }
      if (char === '}') { depth -= 1; buffer = ''; continue; }
      if (depth === 0) buffer += char;
    }
  }
});

test('the CJK lang selector matches the lang attribute the layout emits', async () => {
  // This is load-bearing and silent when wrong: if the selector stops matching,
  // Chinese pages quietly fall back to Latin measure, leading and kickers with
  // no error anywhere.
  const typography = await readFile(path.join(STYLES, 'tokens', 'typography.css'), 'utf8');
  const selectors = [...typography.matchAll(/:root:lang\(([^)]+)\)/g)].map((m) => m[1]);
  assert.ok(selectors.length > 0, 'expected at least one :lang() block for CJK');

  const routes = await readFile(path.join(ROOT, 'src', 'lib', 'routes.ts'), 'utf8');
  const emitted = routes.match(/return locale === 'en' \? 'en' : '([^']+)'/)?.[1];
  assert.ok(emitted, 'could not find the emitted lang tag in routes.ts');

  // CSS :lang(C) matches C itself, or C followed by a hyphen. So at least one
  // selector must be the emitted tag or a hyphen-prefix of it.
  const matches = selectors.some((s) => emitted === s || emitted.startsWith(`${s}-`));
  assert.ok(
    matches,
    `lang="${emitted}" is not matched by any of :lang(${selectors.join('), :lang(')})`,
  );
});

// ---------------------------------------------------------------------------
// The four rules that were prose-only in the source project
// ---------------------------------------------------------------------------

test('body copy never goes below 12px, kickers excepted', async () => {
  const KICKER_TOKENS = new Set(['--text-kicker-sm', '--text-kicker']);
  const typography = await readFile(path.join(STYLES, 'tokens', 'typography.css'), 'utf8');

  for (const [, name, value] of typography.matchAll(/(--[a-z0-9-]*(?:text|font-size)[a-z0-9-]*)\s*:\s*([\d.]+)px/g)) {
    if (KICKER_TOKENS.has(name)) continue;
    assert.ok(
      Number(value) >= 12,
      `token ${name} is ${value}px; body copy floors at 12px and only kickers may go smaller`,
    );
  }

  // And no component may hard-code something smaller.
  for (const file of await styleFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    for (const [, value] of source.matchAll(/font-size\s*:\s*([\d.]+)px/g)) {
      assert.ok(Number(value) >= 12, `${rel(file)} sets font-size: ${value}px`);
    }
  }
});

test('spacing comes only from the --space tokens', async () => {
  // "Never write a bare pixel value for spacing." A handful of non-spacing
  // pixel values are legitimate -- 1px rules, the 7px pulse dot, the 30px logo,
  // the 2px focus offset, the 36px input height and the 5px gap between the
  // hamburger's three bars, which is glyph geometry the design system gives
  // exactly and says not to round -- so those are named.
  const ALLOWED_PX = new Set(['0', '1', '2', '5', '6', '7', '30', '36']);
  for (const file of await styleFiles()) {
    if (file.endsWith(path.join('tokens', 'spacing.css'))) continue;
    if (file.endsWith(path.join('tokens', 'typography.css'))) continue;
    const source = stripComments(await readFile(file, 'utf8'));
    const properties = /(margin|padding|gap|row-gap|column-gap)(?:-(?:top|right|bottom|left|block|inline))?\s*:\s*([^;}]+)/g;
    for (const [, property, raw] of source.matchAll(properties)) {
      for (const token of raw.trim().split(/\s+/)) {
        const px = token.match(/^([\d.]+)px$/);
        if (!px) continue;
        assert.ok(
          ALLOWED_PX.has(px[1]),
          `${rel(file)} sets ${property}: ${token}. Use a --space-* token.`,
        );
      }
    }
  }
});

test('the focus ring is always 2px, and focus is never simply removed', async () => {
  const borders = await readFile(path.join(STYLES, 'tokens', 'borders.css'), 'utf8');
  assert.match(borders, /--focus-outline\s*:\s*2px solid/, 'focus outline must be 2px');
  assert.match(borders, /--focus-offset\s*:\s*2px/, 'focus offset must be 2px');

  for (const file of await styleFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    for (const [, value] of source.matchAll(/outline\s*:\s*([^;}]+)/g)) {
      const outline = value.trim();
      if (outline === 'none') continue; // paired with a :focus-visible rule below
      assert.ok(
        outline === 'var(--focus-outline)' || /^2px solid/.test(outline),
        `${rel(file)} sets outline: ${outline}; the focus ring is always 2px`,
      );
    }
    if (/outline\s*:\s*none/.test(source)) {
      assert.match(
        source, /:focus-visible/,
        `${rel(file)} clears the outline without a :focus-visible replacement`,
      );
    }
  }
});

test('the accent is never a large fill', async () => {
  // Whitelist: primary button fill, active nav item, live pulse dot,
  // positive-value emphasis, the numbers inside kickers and indices, must-fix
  // tags. The only tinted surface in the system is the notice banner at
  // --accent-100, so accent-100 is allowed as a background and the darker
  // steps are not.
  const ALLOWED_ACCENT_BACKGROUNDS = new Set([
    '.ds-btn--primary',
    '.ds-btn--primary:hover',
    '.ds-btn--primary:active',
    '.ds-pulse__dot',
    '.ds-tag--accent',
    '.ds-btn--ghost:hover',
    '::selection',
  ]);

  for (const file of await styleFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    // Match "selector { … background: <accent> … }" one rule at a time.
    for (const [, selector, body] of source.matchAll(/([^{}]+)\{([^{}]*)\}/g)) {
      const backgrounds = [...body.matchAll(/background(?:-color)?\s*:\s*([^;}]+)/g)]
        .map((m) => m[1].trim());
      for (const background of backgrounds) {
        const usesAccent = /--accent(?!-100)/.test(background)
          || /var\(--accent\)/.test(background);
        if (!usesAccent) continue;
        const name = selector.trim().split('\n').pop().trim();
        assert.ok(
          ALLOWED_ACCENT_BACKGROUNDS.has(name),
          `${rel(file)}: "${name}" fills with the accent (${background}). `
          + 'The signal colour is information, not a surface.',
        );
      }
    }
  }
});

// ---------------------------------------------------------------------------
// Content rules the design system states explicitly
// ---------------------------------------------------------------------------

test('no emoji anywhere in authored source', async () => {
  // "No emoji anywhere. No filled icon glyphs." The only glyph used
  // iconographically is the literal arrow at the end of a link.
  const EMOJI = /[\u{1F300}-\u{1FAFF}\u{1F000}-\u{1F0FF}\u{2600}-\u{27BF}\u{FE0F}]/u;
  for (const file of await authoredFiles()) {
    const source = await readFile(file, 'utf8');
    const match = source.match(EMOJI);
    assert.equal(match, null, `${rel(file)} contains the glyph ${match?.[0]}`);
  }
});

test('the built pages show no dark-mode or shadow rules', {
  skip: existsSync(path.join(ROOT, 'dist')) ? false : 'run `npm run build` first',
}, async () => {
  // The backstop: check what actually ships, not just what was authored.
  const assets = path.join(ROOT, 'dist', '_astro');
  if (!existsSync(assets)) return;
  for (const file of await walk(assets, ['.css'])) {
    const css = await readFile(file, 'utf8');
    assert.ok(!/prefers-color-scheme\s*:\s*dark/.test(css), `${rel(file)} ships a dark-mode block`);
    assert.ok(!/box-shadow\s*:\s*(?!none)/.test(css), `${rel(file)} ships a shadow`);
    for (const [, value] of css.matchAll(/border-radius:\s*([^;}]+)/g)) {
      const radius = value.trim();
      assert.ok(
        radius === '0' || radius === '50%' || radius.startsWith('var('),
        `${rel(file)} ships border-radius: ${radius}`,
      );
    }
  }
});

// ---------------------------------------------------------------------------
// Mobile (< 768px)
//
// The design system's mobile rules are almost all negative -- one breakpoint,
// no scaled type ramp, no sideways-scrolling table, no scrim, no sheet -- and a
// negative rule has no output when it is broken. A phone-shaped viewport is
// also the one nobody opens while editing a stylesheet, so these are the rules
// most likely to rot quietly.
// ---------------------------------------------------------------------------

test('there is exactly one breakpoint, and it is 767px', async () => {
  // "One breakpoint only: 768px." A second number anywhere is the beginning of
  // a tablet tier the system does not have.
  for (const file of await authoredFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    for (const [, condition] of source.matchAll(/@media\s*([^{]+)\{/g)) {
      const query = condition.trim().replace(/\s+/g, '');
      if (!/width/.test(query)) continue; // prefers-reduced-motion, hover
      assert.equal(
        query, '(max-width:767px)',
        `${rel(file)} has the media query ${query}; the system has one breakpoint, (max-width:767px)`,
      );
    }
  }
});

test('mobile reflow is a token override, and the type ramp is not in it', async () => {
  // The handoff: "Port this block verbatim; do not scatter `sm:` prefixes
  // through components to reproduce it." And the type ramp is deliberately
  // absent -- scaling it would break the tables' tabular alignment.
  const layout = await readFile(path.join(STYLES, 'tokens', 'layout.css'), 'utf8');
  const block = stripComments(layout).match(/@media\s*\(max-width:767px\)\s*\{([\s\S]*?)\n\}/);
  assert.ok(block, 'tokens/layout.css has no mobile token override');
  for (const token of [
    '--page-pad-x', '--page-pad-y', '--nav-pad-x', '--nav-pad-y',
    '--column-gap', '--grid-min-ledger', '--grid-min-wide',
  ]) {
    assert.ok(
      block[1].includes(token),
      `the mobile token override does not re-point ${token}`,
    );
  }
  for (const [, value] of block[1].matchAll(/--(?:page-pad|nav-pad|column-gap)[a-z-]*\s*:\s*([^;]+)/g)) {
    assert.match(
      value.trim(), /^var\(--space-\d\)$/,
      `mobile padding is ${value.trim()}; it must come from a --space token`,
    );
  }

  // No media query anywhere may resize the ramp. Reading a ramp token inside a
  // mobile block is fine and necessary -- the stacked record's label is set at
  // --text-kicker-sm, the same size as the <th> it replaces. What is forbidden
  // is redefining a size token per breakpoint, or inventing a size in px that
  // exists nowhere in the ramp.
  for (const file of await styleFiles()) {
    const source = stripComments(await readFile(file, 'utf8'));
    for (const [, body] of source.matchAll(/@media\s*\(max-width:767px\)\s*\{([\s\S]*?)\n {2}\}/g)) {
      const redefined = body.match(/(--(?:text|font-size)[a-z0-9-]*)\s*:/);
      assert.ok(
        !redefined,
        `a mobile media query redefines ${redefined?.[1]}; the ramp is not scaled per breakpoint`,
      );
      const literal = body.match(/font-size\s*:\s*([\d.]+px)/);
      assert.ok(
        !literal,
        `a mobile media query sets font-size: ${literal?.[1]}; use a ramp token`,
      );
    }
  }
});

test('tap targets floor at 44px, and the drawer rows clear it', async () => {
  const layout = await readFile(path.join(STYLES, 'tokens', 'layout.css'), 'utf8');
  const value = (name) => Number(layout.match(new RegExp(`${name}\\s*:\\s*([\\d.]+)px`))?.[1]);
  assert.equal(value('--tap-min'), 44, '--tap-min must be 44px; it is a floor, not a target');
  assert.ok(
    value('--drawer-row-h') >= value('--tap-min'),
    `--drawer-row-h is ${value('--drawer-row-h')}px, below the ${value('--tap-min')}px floor`,
  );

  // And the components must read the tokens rather than restating the numbers,
  // so raising the floor raises every target with it.
  const components = stripComments(await readFile(path.join(STYLES, 'components.css'), 'utf8'));
  const rule = (selector) => components.match(
    new RegExp(`${selector.replace(/[.\\-]/g, (c) => `\\${c}`)}\\s*\\{([^}]*)\\}`),
  )?.[1] ?? '';
  assert.match(rule('.ds-mnav__toggle'), /var\(--tap-min\)/, 'the hamburger does not use --tap-min');
  assert.match(rule('.ds-mnav__row'), /var\(--drawer-row-h\)/, 'drawer rows do not use --drawer-row-h');
  assert.match(rule('.ds-mnav__lang'), /var\(--tap-min\)/, 'the drawer language link has no 44px hit box');
});

test('the drawer is the page: no scrim, no sheet, no elevation', async () => {
  // "No bottom tab bar, no scrim, no sheet radius, no elevation, no icon-only
  // nav." Radius and shadow are covered site-wide; the scrim is not, because a
  // translucent black overlay is a plausible thing for someone to add.
  const components = stripComments(await readFile(path.join(STYLES, 'components.css'), 'utf8'));
  const drawer = components.match(/\.ds-mnav__drawer\s*\{([^}]*)\}/)?.[1];
  assert.ok(drawer, 'components.css has no .ds-mnav__drawer rule');
  assert.match(drawer, /background:var\(--bg\)/, 'the drawer must be a solid page, not a translucent sheet');
  assert.ok(!/border-radius/.test(drawer), 'the drawer has a radius');
  assert.ok(!/position:fixed/.test(drawer), 'the drawer is positioned against the nav, not the viewport');

  for (const [, selector, body] of components.matchAll(/([^{}]*ds-mnav[^{}]*)\{([^{}]*)\}/g)) {
    assert.ok(
      !/rgba\(\s*0\s*,\s*0\s*,\s*0/.test(body),
      `${selector.trim()} paints a translucent black; there is no scrim in this system`,
    );
  }

  // The hamburger keeps a visible border: an icon with no box is icon-only nav.
  const toggle = components.match(/\.ds-mnav__toggle\s*\{([^}]*)\}/)?.[1] ?? '';
  assert.match(toggle, /border:var\(--border-hairline\)/, 'the hamburger has no visible border');
});

test('the mobile nav opens without JavaScript, and its links are in the HTML', {
  skip: existsSync(path.join(ROOT, 'dist')) ? false : 'run `npm run build` first',
}, async () => {
  // A nav that needs a script to open is a nav a crawler cannot walk -- which
  // is the exact failure this whole rebuild exists to undo. `<details>` is the
  // native disclosure control, so the drawer works with scripting off.
  const source = await readFile(path.join(COMPONENTS, 'ds', 'MobileNav.astro'), 'utf8');
  assert.match(source, /<details/, 'the drawer must be a <details>, not script state');
  assert.match(source, /<summary/, 'the toggle must be a <summary>');

  const routes = await readFile(path.join(ROOT, 'src', 'lib', 'routes.ts'), 'utf8');
  const inNav = [...routes.matchAll(/navKey: '([a-z_]+)'[^}]*inNav: true/g)].map((m) => m[1]);
  assert.ok(inNav.length > 1, 'could not read the nav routes');

  const html = await readFile(path.join(ROOT, 'dist', 'members', 'index.html'), 'utf8');
  const nav = html.match(/<nav class="ds-mnav"[\s\S]*?<\/nav>/)?.[0];
  assert.ok(nav, 'the built page has no mobile nav');
  const rows = [...nav.matchAll(/class="ds-mnav__row"/g)].length;
  assert.equal(
    rows, inNav.length,
    `the drawer renders ${rows} rows for ${inNav.length} nav routes`,
  );
  // The ordinals are computed, not a fixed list: a tenth route must number 10.
  const indices = [...nav.matchAll(/class="ds-mnav__index">(\d+)</g)].map((m) => m[1]);
  assert.deepEqual(
    indices,
    inNav.map((_, index) => String(index + 1).padStart(2, '0')),
    'the drawer ordinals are out of sequence',
  );
});

test('below the breakpoint no table scrolls sideways, and every cell is labelled', {
  skip: existsSync(path.join(ROOT, 'dist')) ? false : 'run `npm run build` first',
}, async () => {
  // "Tables stack to label/value records. Never horizontal scroll."
  const components = stripComments(await readFile(path.join(STYLES, 'components.css'), 'utf8'));
  const mobile = components.match(/@media\s*\(max-width:767px\)\s*\{[\s\S]*?\.ds-table-wrap\s*\{([^}]*)\}/);
  assert.ok(mobile, 'no mobile rule for .ds-table-wrap');
  assert.match(mobile[1], /overflow-x:visible/, 'the table wrapper still scrolls sideways on mobile');

  // The stacked labels come from --col-N, and components.css declares a fixed
  // number of them. A table with more columns than that would lose the last
  // label with no error, so DataTable throws -- and this keeps the two numbers
  // talking to each other.
  const declared = [...components.matchAll(/--col-label:var\(--col-(\d+)\)/g)].map((m) => Number(m[1]));
  const dataTable = await readFile(path.join(COMPONENTS, 'ds', 'DataTable.astro'), 'utf8');
  const limit = Number(dataTable.match(/MAX_STACKED_COLUMNS = (\d+)/)?.[1]);
  assert.equal(
    Math.max(...declared), limit,
    `components.css labels ${Math.max(...declared)} columns but DataTable allows ${limit}`,
  );

  for (const file of await walk(path.join(ROOT, 'dist'), ['.html'])) {
    const html = await readFile(file, 'utf8');
    for (const [table] of html.matchAll(/<table class="ds-table[^"]*"[^>]*>[\s\S]*?<\/table>/g)) {
      if (!/ds-table--stack/.test(table)) continue;
      const heads = [...table.matchAll(/<th\b/g)].length;
      const labels = [...table.matchAll(/--col-(\d+):/g)].length;
      assert.equal(labels, heads, `${rel(file)}: a stacked table has ${heads} heads but ${labels} labels`);

      for (const [, row] of table.matchAll(/<tr>([\s\S]*?)<\/tr>/g)) {
        const cells = [...row.matchAll(/<td\b/g)].length;
        if (cells === 0) continue; // the head row
        assert.equal(
          cells, heads,
          `${rel(file)}: a row has ${cells} cells for ${heads} columns, so one record loses its label`,
        );
        // A blank cell must be exactly empty, or its label shows on mobile with
        // nothing beside it: td:empty is what drops it, and whitespace defeats.
        for (const [, inner] of row.matchAll(/<td[^>]*>([\s\S]*?)<\/td>/g)) {
          if (inner === '' || inner.trim() !== '') continue;
          assert.fail(
            `${rel(file)}: a cell renders blank but is not empty (${JSON.stringify(inner)}); `
            + 'write the cell on one line so it emits <td></td>',
          );
        }
      }
    }
  }
});
