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

/**
 * An address is published only when its own entry opts in with
 * `publish_email: true` in members-overrides.yml.
 *
 * Opt-in per person, not per role: the reason to publish differs person by
 * person. The PI's is an institutional address already printed as the
 * corresponding-author contact in the lab's open-access papers, so withholding
 * it costs reachability and prevents nothing. One postdoc wants to be reachable
 * after an external group failed to find a working address for him. The other
 * postdoc, the staff and every student have made no such request, and their
 * addresses are personal accounts that exist nowhere but this site -- which
 * makes publishing here the entire exposure.
 *
 * Addresses without the flag are stripped before the file is written, not
 * hidden in a template, so they are absent from data/members.yml altogether.
 * tests/members-privacy.test.mjs enforces that.
 */
function emailIsPublic(entry) {
  return entry.publish_email === true && Boolean(entry.email);
}

/**
 * Coarse research areas, and the vault wording that maps onto each.
 *
 * The members page shows an area, not a topic. The lab asked for the specific
 * topics off -- they are working shorthand from the vault, not public summaries
 * -- but removing them entirely left each entry with nothing searchable on it.
 * An area keeps the page findable without publishing what anyone is working on
 * this month.
 *
 * The areas are exactly the research pillars in data/pages.yml, so the members
 * page and the research page cannot describe the lab differently. Rules are
 * ordered: the more specific pattern must come first, and at most two areas are
 * assigned so a genuinely cross-cutting member is not flattened to one.
 *
 * Anything unmatched is left blank and flagged for review rather than guessed.
 */
const RESEARCH_AREAS = [
  {
    zh: '步態分析',
    en: 'Gait Analysis',
    pattern: /gait|步態|parkinson|帕金森|螺旋繪圖/i,
  },
  {
    zh: 'AI Agent 系統',
    en: 'AI Agent Systems',
    pattern: /\bagent\b|\bllm\b|\brag\b|大型語言模型|文獻分析與引證|法規資料自動化|知識庫/i,
  },
  {
    zh: '量子計算',
    en: 'Quantum Computing',
    pattern: /quantum|量子|\bvqe\b/i,
  },
  {
    zh: '代謝體學',
    en: 'Metabolomics',
    pattern: /metabolom|代謝體|代謝物|lipidpedia|脂質體|breathomics|\bhbdb\b|retention time|\bpfas\b|\bmsi\b|質譜/i,
  },
  {
    zh: '計算分子設計',
    en: 'Computational Molecular Design',
    pattern: /computational chemistry|molecular modeling|\bqsar\b|分子設計|馬庫什|醣蛋白|receptor|peptide|nitrosamine|docking|計算化學/i,
  },
];

/**
 * Area terms accepted from an override but not derived by the classifier.
 *
 * The classifier may only emit the pillars, so it cannot invent a category. A
 * person describing their own interests is a different matter: methods that cut
 * across several pillars -- deep learning being the obvious one -- are not
 * pillars and should not become one, but they are legitimate self-descriptions.
 *
 * The list exists so a typo in an override still fails the test in
 * tests/content.test.mjs rather than shipping. Keep it short; a term that
 * belongs to several people probably wants to be a pillar instead.
 */
const EXTRA_AREAS = new Map([
  ['深度學習', 'Deep Learning'],
]);

/** Assign at most two coarse areas to a research description. */
function classifyAreas(text) {
  if (!text) return [];
  return RESEARCH_AREAS.filter((area) => area.pattern.test(text)).slice(0, 2);
}

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
  const withheldEmails = [];
  const flagged = [...review];

  for (const record of members) {
    const override = overrides[record.name] ?? {};
    if (override.publish === false) {
      withheld.push(record.name);
      continue;
    }

    const role = override.role ?? record.role;
    const research = override.research ?? record.research ?? '';
    // `area: ''` in an override means "deliberately none" and must survive the
    // classifier -- one research assistant is listed without an area at her
    // own request.
    const areas = override.area !== undefined ? [] : classifyAreas(research);

    const entry = {
      name: record.name,
      name_en: override.name_en ?? record.alias ?? '',
      role,
      ...(override.title ? { title: override.title } : {}),
      ...(override.title_en ? { title_en: override.title_en } : {}),
      ...(record.grade ? { grade: record.grade } : {}),
      ...(override.department || record.department
        ? { department: override.department ?? record.department }
        : {}),
      // Explicit position within a role group, lower first. Needed where
      // seniority is not recoverable from the data: the two postdocs have no
      // grade, and the graduating students are listed in a vault table whose
      // only publishable column is the name.
      ...(override.order !== undefined ? { order: override.order } : {}),
      ...(record.graduated ? { graduated: record.graduated } : {}),
      research,
      research_en: override.research_en ?? '',
      ...(override.area !== undefined
        ? (override.area ? { area: override.area, area_en: override.area_en ?? '' } : {})
        : (areas.length
          ? { area: areas.map((a) => a.zh).join(' · '), area_en: areas.map((a) => a.en).join(' · ') }
          : {})),
      ...(emailIsPublic(override) ? { email: override.email } : {}),
      // A named project is not a research area -- it is a thing someone is
      // responsible for, often an external system. Kept as its own field so the
      // area column keeps meaning one thing.
      ...(override.projects ? { projects: override.projects } : {}),
      ...(override.photo ? { photo: override.photo } : {}),
      ...(override.homepage ? { homepage: override.homepage } : {}),
      ...(override.expertise ? { expertise: override.expertise } : {}),
      ...(override.honors ? { honors: override.honors } : {}),
    };
    published.push(entry);

    // A topic that matched no area is not guessed at -- it is left blank and
    // raised, because a wrong area on a public page is worse than none.
    if (research && !entry.area && override.area === undefined
      && role !== 'faculty' && role !== 'alumni') {
      flagged.push({
        name: record.name,
        reason: `research topic matches no coarse area: "${research.slice(0, 60)}"`,
      });
    }

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
  // they are declared directly in the overrides file. The email rule applies to
  // them too -- staff and postdoc addresses in that file are personal accounts.
  for (const entry of extra) {
    const { publish_email: optedIn, ...rest } = entry;

    // Classify these too. They used to be pushed through untouched, which left
    // both postdocs with no area even though their descriptions match several
    // patterns.
    let withArea = rest;
    if (rest.area === undefined && rest.role !== 'faculty') {
      const areas = classifyAreas(rest.research ?? '');
      if (areas.length) {
        withArea = {
          ...rest,
          area: areas.map((a) => a.zh).join(' · '),
          area_en: areas.map((a) => a.en).join(' · '),
        };
      }
    } else if (rest.area === '') {
      const { area, area_en: areaEn, ...withoutArea } = rest;
      withArea = withoutArea;
    }

    if (entry.email && !emailIsPublic(entry)) {
      const { email, ...withoutEmail } = withArea;
      withheldEmails.push(entry.name);
      published.push(withoutEmail);
    } else {
      published.push(withArea);
    }
  }

  /** Year within a grade code, so D4 sorts ahead of D2. */
  const seniority = (grade) => Number(String(grade ?? '').match(/(\d+)/)?.[1] ?? -1);

  published.sort((a, b) => {
    const byRole = ROLE_ORDER.indexOf(a.role) - ROLE_ORDER.indexOf(b.role);
    if (byRole !== 0) return byRole;

    if (a.role === 'alumni') {
      const byYear = (b.graduated ?? 0) - (a.graduated ?? 0);
      if (byYear !== 0) return byYear;
      return a.name.localeCompare(b.name, 'zh-Hant');
    }

    // An explicit order wins, and anyone carrying one leads those who do not.
    const orderA = a.order ?? Number.POSITIVE_INFINITY;
    const orderB = b.order ?? Number.POSITIVE_INFINITY;
    if (orderA !== orderB) return orderA - orderB;

    // Otherwise most senior first: D4 before D2, M3 before M1. The previous
    // localeCompare sorted the codes as strings, which put the newest intake at
    // the top of every list.
    const bySeniority = seniority(b.grade) - seniority(a.grade);
    if (bySeniority !== 0) return bySeniority;

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
    emails_withheld: withheldEmails,
    count: flagged.length,
    items: flagged,
  }, { lineWidth: 100 }), 'utf8');

  console.log(`vault roster     : ${members.length} rows`);
  console.log(`private skipped  : ${skippedSections.join(', ') || 'none'}`);
  console.log(`withheld         : ${withheld.length}`);
  console.log(`emails withheld  : ${withheldEmails.length}`);
  console.log(`published        : ${published.length}`, counts);
  console.log(`needs review     : ${flagged.length}`);
}

await main();
