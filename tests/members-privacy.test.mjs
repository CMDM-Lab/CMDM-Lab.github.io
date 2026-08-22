// Tripwire for the vault -> website privacy boundary.
//
// `data/members.yml` is a public artifact derived from a PRIVATE repository
// (CMDM-Lab/CMDM-Lobe). The allowlist in scripts/lib/vault-members.mjs is the
// policy; this file is the enforcement. A prose note in a comment would give no
// signal when someone widens the allowlist -- these assertions turn that into a
// red CI run naming the leaked field.
//
// The checks come in two halves:
//
//   * Committed-artifact checks run everywhere, with no vault present, because
//     they read data/members.yml -- the file that actually ships. This is the
//     backstop that matters.
//   * Parser checks run only where the vault is available, and verify the
//     policy still holds against the live roster.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import YAML from 'yaml';
import {
  DENIED_COLUMNS, SECTION_POLICY, cleanCell, parseRoster, splitRow,
} from '../scripts/lib/vault-members.mjs';

const ROOT = path.resolve(import.meta.dirname, '..');
const MEMBERS_YML = path.join(ROOT, 'data', 'members.yml');

const VAULT_ROOT = process.env.CMDM_LOBE_PATH
  || path.join(os.homedir(), 'Vaults', 'CMDM-Lobe');
const ROSTER_FILE = path.join(VAULT_ROOT, '20-Areas', '2026-04-13-cmdm-lab-members.md');

/**
 * Substrings that prove private vault material reached the public file.
 *
 * Document hosts cover the thesis-draft and slide links the roster is full of;
 * the Chinese terms cover defense logistics and leave-of-absence status.
 */
const FORBIDDEN_IN_PUBLIC = [
  'drive.google.com',
  'docs.google.com',
  'usp=drivesdk',
  '審定書',
  '預計口試',
  '離校',
  '休學',
  '口試PPT',
  '論文初稿',
  '論文草稿',
  'ThesisDraft',
  'OralDefenseSlides',
];

/**
 * Fields a member entry is allowed to carry on the public site.
 *
 * Deny-by-default: anything not listed here fails the run. Add a field only
 * after deciding it is safe to publish for everyone who could carry it.
 */
const ALLOWED_MEMBER_FIELDS = new Set([
  // Identity and grouping.
  'name', 'name_en', 'role', 'title', 'title_en', 'grade', 'department', 'graduated', 'order',
  // Research description. `research` is the vault's specific topic and is NOT
  // rendered; `area` is the coarse pillar that is. `thesis` is the deposited
  // title -- a public bibliographic record, curated in members-overrides.yml and
  // never taken from the vault's working fields.
  'research', 'research_en', 'thesis', 'area', 'area_en', 'expertise', 'honors',
  // Named projects: a responsibility, kept apart from `area`.
  'projects',
  // Professional contact and profile. Deliberate publications, not vault spill.
  'email', 'phone', 'office', 'office_en', 'orcid', 'photo', 'homepage',
  'education', 'education_en',
]);

const hasVault = existsSync(ROSTER_FILE);
const hasGeneratedMembers = existsSync(MEMBERS_YML);

// ---------------------------------------------------------------------------
// Policy shape -- always runs, no external files needed.
// ---------------------------------------------------------------------------

test('no section policy allowlists a denied column', () => {
  for (const [section, policy] of Object.entries(SECTION_POLICY)) {
    for (const column of policy.columns) {
      assert.ok(
        !DENIED_COLUMNS.has(column),
        `section "${section}" allowlists denied column "${column}"`,
      );
    }
  }
});

test('parseRoster refuses a policy that allowlists a denied column', () => {
  // Guard the guard: if the runtime check in parseRoster is ever removed, this
  // fails even though the shipped policy is still clean.
  const denied = [...DENIED_COLUMNS][0];
  const saved = SECTION_POLICY.博士班.columns;
  SECTION_POLICY.博士班 = { ...SECTION_POLICY.博士班, columns: [...saved, denied] };
  try {
    assert.throws(() => parseRoster('## 博士班\n'), /denied column/);
  } finally {
    SECTION_POLICY.博士班 = { ...SECTION_POLICY.博士班, columns: saved };
  }
});

test('cleanCell strips markdown links, which carry the Drive URLs', () => {
  const cell = '08-19 [檔案](https://drive.google.com/file/d/abc/view?usp=drivesdk)';
  assert.equal(cleanCell(cell), '08-19');
  assert.ok(!cleanCell(cell).includes('drive.google.com'));
});

test('cleanCell keeps only the display label of a wiki link', () => {
  assert.equal(cleanCell('[[2026-05-05-王又可|王又可]]'), '王又可');
  assert.equal(cleanCell('[[林首志]]'), '林首志');
});

test('splitRow honours escaped pipes inside wiki links', () => {
  const row = '| [[slug\\|王又可]] | D2 | BEBI |';
  assert.deepEqual(splitRow(row), ['[[slug|王又可]]', 'D2', 'BEBI']);
});

// ---------------------------------------------------------------------------
// Committed artifact -- the backstop. Runs without the vault.
// ---------------------------------------------------------------------------

test('data/members.yml leaks no private vault material', {
  skip: hasGeneratedMembers ? false : 'data/members.yml not generated yet',
}, async () => {
  const raw = await readFile(MEMBERS_YML, 'utf8');
  for (const needle of FORBIDDEN_IN_PUBLIC) {
    assert.ok(
      !raw.includes(needle),
      `data/members.yml contains private marker "${needle}" -- `
      + 'check the allowlist in scripts/lib/vault-members.mjs',
    );
  }
});

test('data/members.yml carries no unexpected fields', {
  skip: hasGeneratedMembers ? false : 'data/members.yml not generated yet',
}, async () => {
  const parsed = YAML.parse(await readFile(MEMBERS_YML, 'utf8'));
  for (const entry of parsed.members ?? []) {
    for (const field of Object.keys(entry)) {
      assert.ok(
        ALLOWED_MEMBER_FIELDS.has(field),
        `member "${entry.name}" has unexpected field "${field}"; `
        + 'add it to ALLOWED_MEMBER_FIELDS only if it is safe to publish',
      );
    }
  }
});

test('member entries carry no ISO dates, which would imply defense scheduling', {
  skip: hasGeneratedMembers ? false : 'data/members.yml not generated yet',
}, async () => {
  // Scope this to the member entries. The file header names its vault source
  // file, whose filename legitimately contains a date.
  const parsed = YAML.parse(await readFile(MEMBERS_YML, 'utf8'));
  const dates = JSON.stringify(parsed.members ?? []).match(/\d{4}-\d{2}-\d{2}/g) ?? [];
  assert.deepEqual(dates, [], `unexpected dates in public member data: ${dates.join(', ')}`);
});

// ---------------------------------------------------------------------------
// Live roster -- verifies the policy against the real vault where available.
// ---------------------------------------------------------------------------

test('private roster sections are never parsed', {
  skip: hasVault ? false : 'CMDM-Lobe vault not available',
}, async () => {
  const { skippedSections } = parseRoster(await readFile(ROSTER_FILE, 'utf8'));
  for (const section of ['投稿追蹤中', '休學']) {
    assert.ok(
      skippedSections.includes(section),
      `section "${section}" is private and must be skipped, got: ${skippedSections.join(', ')}`,
    );
  }
});

test('nobody on leave of absence is emitted', {
  skip: hasVault ? false : 'CMDM-Lobe vault not available',
}, async () => {
  const markdown = await readFile(ROSTER_FILE, 'utf8');
  const { members } = parseRoster(markdown);

  // Read the leave-of-absence names straight out of the vault rather than
  // hard-coding them, so the test keeps working as that table changes.
  const section = markdown.split(/^## /m).find((block) => block.startsWith('休學'));
  assert.ok(section, 'expected a leave-of-absence section in the roster');
  const onLeave = section
    .split('\n')
    .filter((line) => line.trim().startsWith('|'))
    .map((line) => cleanCell(splitRow(line)[0]))
    .filter((name) => name && name !== '姓名' && !/^[\s:-]+$/.test(name));
  assert.ok(onLeave.length > 0, 'expected at least one name in the leave-of-absence table');

  const publishedNames = new Set(members.map((member) => member.name));
  for (const name of onLeave) {
    assert.ok(!publishedNames.has(name), `"${name}" is on leave and must not be published`);
  }
});

test('parsed records carry no private field values', {
  skip: hasVault ? false : 'CMDM-Lobe vault not available',
}, async () => {
  const { members } = parseRoster(await readFile(ROSTER_FILE, 'utf8'));
  const serialized = JSON.stringify(members);
  for (const needle of FORBIDDEN_IN_PUBLIC) {
    assert.ok(!serialized.includes(needle), `parsed roster contains "${needle}"`);
  }
  // Milestone glyphs are the visible shape of the private progress columns.
  for (const glyph of ['✅', '🔄', '⬜', '📄', '📝', '📊']) {
    assert.ok(!serialized.includes(glyph), `parsed roster contains milestone glyph "${glyph}"`);
  }
});

// ---------------------------------------------------------------------------
// Email publication is opt-in
// ---------------------------------------------------------------------------

test('only members who opted in carry an email in the public data', async () => {
  // The failure this guards is silent and irreversible: an address that reaches
  // data/members.yml gets built into the page, crawled, and archived. Most of
  // these are personal accounts that exist nowhere but this site, so the site's
  // choice is the whole of the exposure.
  const overrides = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members-overrides.yml'), 'utf8'),
  ) ?? {};

  const optedIn = new Set();
  for (const [name, entry] of Object.entries(overrides.members ?? {})) {
    if (entry?.publish_email === true) optedIn.add(name);
  }
  for (const entry of overrides.additional ?? []) {
    if (entry?.publish_email === true) optedIn.add(entry.name);
  }

  const published = YAML.parse(await readFile(MEMBERS_YML, 'utf8')).members ?? [];
  for (const member of published) {
    if (!member.email) continue;
    assert.ok(
      optedIn.has(member.name),
      `"${member.name}" has an email in data/members.yml but no publish_email: true `
      + 'in members-overrides.yml',
    );
  }
});

test('no member email that was withheld appears in the built pages', {
  skip: existsSync(path.join(ROOT, 'dist')) ? false : 'run `npm run build` first',
}, async () => {
  // Checks the shipped HTML, not the data: a template could reintroduce an
  // address from the overrides file directly and bypass the sync entirely.
  const overrides = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'members-overrides.yml'), 'utf8'),
  ) ?? {};

  const withheld = [];
  for (const entry of Object.values(overrides.members ?? {})) {
    if (entry?.email && entry.publish_email !== true) withheld.push(entry.email);
  }
  for (const entry of overrides.additional ?? []) {
    if (entry?.email && entry.publish_email !== true) withheld.push(entry.email);
  }
  assert.ok(withheld.length > 0, 'expected at least one withheld address to check');

  const pages = [];
  const walk = async (dir) => {
    const { readdir } = await import('node:fs/promises');
    for (const entry of await readdir(dir, { withFileTypes: true })) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) await walk(full);
      else if (entry.name.endsWith('.html')) pages.push(full);
    }
  };
  await walk(path.join(ROOT, 'dist'));

  for (const page of pages) {
    const html = await readFile(page, 'utf8');
    for (const address of withheld) {
      assert.ok(
        !html.includes(address),
        `${path.relative(ROOT, page)} contains withheld address ${address}`,
      );
    }
  }
});

test('member research topics are not rendered on the page', {
  skip: existsSync(path.join(ROOT, 'dist')) ? false : 'run `npm run build` first',
}, async () => {
  // Structural, not substring. An earlier version of this test searched the HTML
  // for each topic string and false-positived on "Metabolomics" -- a one-word
  // topic that also appears inside the lab's own English name in the structured
  // data. Whether a topic is rendered is a question about the markup, so ask the
  // markup.
  const html = await readFile(path.join(ROOT, 'dist', 'members', 'index.html'), 'utf8');

  // Research was the only thing that filled a ledger item's body on this page.
  const bodies = html.match(/class="ds-ledger-item__body"/g) ?? [];
  assert.deepEqual(bodies, [], 'a ledger item on the members page still renders a body');

  // The roster tables carried a research column; they are name + programme now.
  for (const table of html.match(/<table class="ds-table"[\s\S]*?<\/thead>/g) ?? []) {
    const headers = (table.match(/<th /g) ?? []).length;
    assert.ok(
      headers <= 4,
      `a roster table has ${headers} columns; research and year of study were removed`,
    );
  }

  // And the Person nodes must not claim a topic the page does not show.
  assert.ok(!html.includes('knowsAbout'), 'Person nodes still claim knowsAbout');
});

test('year of study is not rendered on the members page', {
  skip: existsSync(path.join(ROOT, 'dist')) ? false : 'run `npm run build` first',
}, async () => {
  // Grades are distinctive enough to match directly: D4, M2 and so on appear
  // nowhere else on this page. They stay in the data because the sort uses them.
  const published = YAML.parse(await readFile(MEMBERS_YML, 'utf8')).members ?? [];
  const html = await readFile(path.join(ROOT, 'dist', 'members', 'index.html'), 'utf8');
  const grades = [...new Set(published.map((m) => m.grade).filter(Boolean))];
  assert.ok(grades.length > 0, 'expected the data to carry grades to check against');

  for (const grade of grades) {
    assert.ok(
      !new RegExp(`>\\s*${grade}\\s*<`).test(html),
      `members page renders the year of study "${grade}"`,
    );
  }
});
