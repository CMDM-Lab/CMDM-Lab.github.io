#!/usr/bin/env node
/**
 * Rebuild `data/members.yml` from the CMDM-Lobe vault roster.
 *
 * The vault is the single source of truth for who is in the lab; this script
 * projects the publishable subset of that roster onto the website. What may be
 * published, and what may not, is defined in `scripts/lib/vault-members.mjs`
 * and enforced by `tests/members-privacy.test.mjs` -- start there.
 *
 * The vault has no public-presentation details (English names, lab email
 * addresses, photos, personal sites), so those live in
 * `data/members-overrides.yml` and are merged in here, keyed by Chinese name.
 * An override may also set `publish: false` to keep someone off the site
 * entirely, which is the mechanism for anyone who asks not to be listed.
 *
 * Usage:
 *   CMDM_LOBE_PATH=/path/to/CMDM-Lobe node scripts/sync-members.mjs
 */

import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';
import YAML from 'yaml';
import { parseRoster } from './lib/vault-members.mjs';

const ROOT = path.resolve(import.meta.dirname, '..');
const DATA_DIR = path.join(ROOT, 'data');
const OVERRIDES = path.join(DATA_DIR, 'members-overrides.yml');
const OUT_MEMBERS = path.join(DATA_DIR, 'members.yml');
const OUT_REVIEW = path.join(DATA_DIR, 'members-review.yml');

const VAULT_ROOT = process.env.CMDM_LOBE_PATH
  || path.join(os.homedir(), 'Vaults', 'CMDM-Lobe');
const ROSTER_FILE = path.join(VAULT_ROOT, '20-Areas', '2026-04-13-cmdm-lab-members.md');

/**
 * Research-topic text that reads as internal shorthand rather than a public
 * summary. These are published as-is but flagged, so the sync PR puts them in
 * front of a human instead of letting vault jargon appear on the site.
 */
const INTERNAL_TOPIC_MARKERS = [
  { pattern: /新方向|方向待定案/, note: 'mentions an in-flux research direction' },
  { pattern: /共同作者/, note: 'references another member’s manuscript' },
  { pattern: /並行兩題/, note: 'internal shorthand for parallel projects' },
  { pattern: /；|;/, note: 'multiple topics crammed into one cell' },
];

/** Ordering of public role groups on the members page. */
const ROLE_ORDER = ['faculty', 'staff', 'postdoc', 'phd', 'masters', 'undergraduate', 'current', 'alumni'];

async function readYaml(file, fallback) {
  try {
    return YAML.parse(await readFile(file, 'utf8')) ?? fallback;
  } catch (error) {
    if (error.code !== 'ENOENT') throw error;
    return fallback;
  }
}

async function main() {
  const markdown = await readFile(ROSTER_FILE, 'utf8').catch((error) => {
    if (error.code === 'ENOENT') {
      throw new Error(
        `roster not found at ${ROSTER_FILE}\n`
        + 'Set CMDM_LOBE_PATH to a checkout of github.com/CMDM-Lab/CMDM-Lobe.',
      );
    }
    throw error;
  });

  const { members, review, skippedSections } = parseRoster(markdown);
  const overridesFile = await readYaml(OVERRIDES, {});
  const overrides = overridesFile.members ?? {};
  const extra = overridesFile.additional ?? [];

  const published = [];
  const withheld = [];
  const flagged = [...review];

  for (const record of members) {
    const override = overrides[record.name] ?? {};
    if (override.publish === false) {
      withheld.push(record.name);
      continue;
    }

    const entry = {
      name: record.name,
      name_en: override.name_en ?? record.alias ?? '',
      role: override.role ?? record.role,
      ...(record.grade ? { grade: record.grade } : {}),
      ...(record.department ? { department: record.department } : {}),
      ...(record.graduated ? { graduated: record.graduated } : {}),
      research: override.research ?? record.research ?? '',
      research_en: override.research_en ?? '',
      ...(override.email ? { email: override.email } : {}),
      ...(override.photo ? { photo: override.photo } : {}),
      ...(override.homepage ? { homepage: override.homepage } : {}),
      ...(override.expertise ? { expertise: override.expertise } : {}),
      ...(override.honors ? { honors: override.honors } : {}),
    };
    published.push(entry);

    // Only flag the vault's own wording; a curated override has already been
    // reviewed by definition.
    if (!override.research && record.research) {
      for (const { pattern, note } of INTERNAL_TOPIC_MARKERS) {
        if (pattern.test(record.research)) {
          flagged.push({ name: record.name, reason: `research topic ${note}` });
          break;
        }
      }
    }
  }

  // Faculty, staff and postdocs are not in the vault roster table at all, so
  // they are declared directly in the overrides file.
  for (const entry of extra) published.push(entry);

  published.sort((a, b) => {
    const byRole = ROLE_ORDER.indexOf(a.role) - ROLE_ORDER.indexOf(b.role);
    if (byRole !== 0) return byRole;
    if (a.role === 'alumni') {
      const byYear = (b.graduated ?? 0) - (a.graduated ?? 0);
      if (byYear !== 0) return byYear;
    }
    if (a.grade && b.grade && a.grade !== b.grade) return a.grade.localeCompare(b.grade);
    return a.name.localeCompare(b.name, 'zh-Hant');
  });

  const counts = {};
  for (const entry of published) counts[entry.role] = (counts[entry.role] ?? 0) + 1;

  await writeFile(OUT_MEMBERS, YAML.stringify({
    _generated: 'Generated by scripts/sync-members.mjs from the CMDM-Lobe vault -- do not '
      + 'hand-edit. Public-facing details and opt-outs belong in members-overrides.yml.',
    source: 'CMDM-Lab/CMDM-Lobe :: 20-Areas/2026-04-13-cmdm-lab-members.md',
    count: published.length,
    members: published,
  }, { lineWidth: 100 }), 'utf8');

  await writeFile(OUT_REVIEW, YAML.stringify({
    _generated: 'Rows that parsed cleanly but need a human decision before they read well '
      + 'in public. Resolve by adding a curated entry to members-overrides.yml.',
    private_sections_skipped: skippedSections,
    withheld_by_override: withheld,
    count: flagged.length,
    items: flagged,
  }, { lineWidth: 100 }), 'utf8');

  console.log(`vault roster     : ${members.length} rows`);
  console.log(`private skipped  : ${skippedSections.join(', ') || 'none'}`);
  console.log(`withheld         : ${withheld.length}`);
  console.log(`published        : ${published.length}`, counts);
  console.log(`needs review     : ${flagged.length}`);
}

await main();
