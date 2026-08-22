// Symmetry and completeness of the two message dictionaries.
//
// The design system asks for this test by name: "keep the key sets symmetrical
// -- add a test that fails on asymmetry". Asymmetry is silent otherwise. A key
// present in Chinese but not English renders as `undefined` on the English
// page, or as nothing at all, and nobody notices until a reader does.
//
// It also catches the other direction: a key referenced by a component that
// exists in neither file.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, readdir } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';

const ROOT = path.resolve(import.meta.dirname, '..');
const I18N = path.join(ROOT, 'data', 'i18n');

async function load(locale) {
  return YAML.parse(await readFile(path.join(I18N, `${locale}.yml`), 'utf8')) ?? {};
}

async function walk(dir, ext) {
  const out = [];
  for (const entry of await readdir(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) out.push(...await walk(full, ext));
    else if (entry.name.endsWith(ext)) out.push(full);
  }
  return out;
}

test('the two dictionaries carry exactly the same keys', async () => {
  const zh = await load('zh');
  const en = await load('en');

  const missingInEn = Object.keys(zh).filter((key) => !(key in en));
  const missingInZh = Object.keys(en).filter((key) => !(key in zh));

  assert.deepEqual(missingInEn, [], 'keys present in zh.yml but missing from en.yml');
  assert.deepEqual(missingInZh, [], 'keys present in en.yml but missing from zh.yml');
});

test('no message is empty', async () => {
  for (const locale of ['zh', 'en']) {
    const messages = await load(locale);
    for (const [key, value] of Object.entries(messages)) {
      assert.ok(
        typeof value === 'string' && value.trim().length > 0,
        `${locale}.yml: "${key}" is empty`,
      );
    }
  }
});

test('every page declares a title and a description', async () => {
  // These are what a search engine reads. The site this replaced shipped one
  // shared title and an empty description across all eight of its pages.
  const routes = await readFile(path.join(ROOT, 'src', 'lib', 'routes.ts'), 'utf8');
  const keys = [...routes.matchAll(/(?:titleKey|descriptionKey|navKey):\s*'([^']+)'/g)]
    .map((match) => match[1]);
  assert.ok(keys.length > 0, 'no i18n keys found in ROUTES');

  for (const locale of ['zh', 'en']) {
    const messages = await load(locale);
    for (const key of keys) {
      assert.ok(key in messages, `${locale}.yml is missing "${key}", referenced by ROUTES`);
    }
  }
});

test('every i18n key referenced by a component exists', async () => {
  const zh = await load('zh');
  const files = [
    ...await walk(path.join(ROOT, 'src', 'components'), '.astro'),
    ...await walk(path.join(ROOT, 'src', 'layouts'), '.astro'),
  ];

  for (const file of files) {
    const source = await readFile(file, 'utf8');
    // Static lookups only: `t.some_key`. Dynamic ones like t[`role_${role}`]
    // cannot be resolved statically and are covered by the roles test below.
    for (const [, key] of source.matchAll(/\bt\.([a-z][a-z0-9_]*)\b/g)) {
      assert.ok(
        key in zh,
        `${path.relative(ROOT, file)} reads t.${key}, which no dictionary defines`,
      );
    }
  }
});

test('every member role has a label', async () => {
  // Roles are looked up dynamically as t[`role_${role}`], so the previous test
  // cannot see them. They come from SECTION_POLICY plus the roles the overrides
  // file introduces for faculty, staff and postdocs.
  const ROLES = ['faculty', 'staff', 'postdoc', 'phd', 'masters', 'undergraduate', 'current', 'alumni'];
  for (const locale of ['zh', 'en']) {
    const messages = await load(locale);
    for (const role of ROLES) {
      assert.ok(`role_${role}` in messages, `${locale}.yml is missing role_${role}`);
    }
  }
});

test('published member data uses only roles that have labels', async () => {
  const zh = await load('zh');
  const members = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members.yml'), 'utf8'),
  ).members ?? [];
  for (const member of members) {
    assert.ok(
      `role_${member.role}` in zh,
      `member "${member.name}" has role "${member.role}" with no role_${member.role} label`,
    );
  }
});
