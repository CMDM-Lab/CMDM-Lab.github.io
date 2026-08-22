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
  // the 2px focus offset and the 36px input height -- so those are named.
  const ALLOWED_PX = new Set(['0', '1', '2', '6', '7', '30', '36']);
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
