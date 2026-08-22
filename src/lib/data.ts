// Build-time loaders for everything under `data/`.
//
// The site has no runtime data fetching: every page is prerendered from these
// YAML files. That is the whole point of the rebuild -- the previous site put
// its content behind jQuery `.load()` calls, so a crawler fetching any URL got
// 2.6 KB of routing script and no content at all.
//
// Files marked `_generated` are written by the sync scripts and must not be
// hand-edited; their companion `-overrides.yml` files are where corrections go.

import { readFileSync, existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import YAML from 'yaml';

const DATA_DIR = path.resolve(fileURLToPath(new URL('../../data', import.meta.url)));

function loadYaml<T>(file: string, fallback: T): T {
  const full = path.join(DATA_DIR, file);
  if (!existsSync(full)) return fallback;
  return (YAML.parse(readFileSync(full, 'utf8')) ?? fallback) as T;
}

export type Locale = 'zh-Hant' | 'en';

// ---------------------------------------------------------------------------
// Members
// ---------------------------------------------------------------------------

export interface Member {
  name: string;
  name_en?: string;
  role: string;
  title?: string;
  title_en?: string;
  grade?: string;
  department?: string;
  graduated?: number;
  research?: string;
  research_en?: string;
  expertise?: string;
  honors?: string[];
  email?: string;
  phone?: string;
  office?: string;
  office_en?: string;
  orcid?: string;
  photo?: string;
  homepage?: string;
  education?: string[];
  education_en?: string[];
}

export interface HistoricalAlumnus {
  name: string;
  graduated?: number | null;
  department?: string;
  thesis?: string;
}

/** Department codes used in the vault roster, spelled out for the public site. */
const DEPARTMENT_LABELS: Record<string, { 'zh-Hant': string; en: string }> = {
  BEBI: { 'zh-Hant': '生醫電子與資訊學研究所', en: 'Graduate Institute of Biomedical Electronics and Bioinformatics' },
  CSIE: { 'zh-Hant': '資訊工程學系', en: 'Department of Computer Science and Information Engineering' },
  GSB: { 'zh-Hant': '基因體與系統生物學學位學程', en: 'Genome and Systems Biology Degree Program' },
  MHI: { 'zh-Hant': '智慧醫療與健康資訊學程', en: 'Master Program in Health Informatics' },
  GINM: { 'zh-Hant': '網路與多媒體研究所', en: 'Graduate Institute of Networking and Multimedia' },
};

export function departmentLabel(code: string | undefined, locale: Locale): string {
  if (!code) return '';
  const known = DEPARTMENT_LABELS[code.trim()];
  return known ? known[locale] : code;
}

export function getMembers(): Member[] {
  return loadYaml<{ members?: Member[] }>('members.yml', {}).members ?? [];
}

export function getHistoricalAlumni(): HistoricalAlumnus[] {
  return loadYaml<{ alumni?: HistoricalAlumnus[] }>('alumni-historical.yml', {}).alumni ?? [];
}

/** Members grouped by public role, in display order. */
export function membersByRole(): Map<string, Member[]> {
  const order = ['faculty', 'staff', 'postdoc', 'phd', 'masters', 'undergraduate', 'current'];
  const grouped = new Map<string, Member[]>(order.map((role) => [role, []]));
  for (const member of getMembers()) {
    if (member.role === 'alumni') continue;
    if (!grouped.has(member.role)) grouped.set(member.role, []);
    grouped.get(member.role)!.push(member);
  }
  for (const [role, list] of grouped) if (list.length === 0) grouped.delete(role);
  return grouped;
}

/**
 * All alumni, newest graduation year first.
 *
 * Two sources overlap here. The vault tracks people who left recently, while
 * `alumni-historical.yml` holds the 2008-2022 cohort recovered from the old
 * site -- and a few names appear in both (Pei-Hwa Wang graduated in 2022 and is
 * now a postdoc; Yu-Hao Ni is in both lists). The vault entry wins, and anyone
 * currently on staff is dropped from the alumni list entirely so they are not
 * listed twice on the page.
 */
export function allAlumni(): Array<Member | HistoricalAlumnus> {
  const members = getMembers();
  const currentNames = new Set(
    members.filter((member) => member.role !== 'alumni').map((member) => member.name),
  );
  const fromVault = members.filter((member) => member.role === 'alumni');
  const seen = new Set(fromVault.map((member) => member.name));

  const historical = getHistoricalAlumni()
    .filter((person) => !seen.has(person.name) && !currentNames.has(person.name));

  return [...fromVault, ...historical].sort(
    (a, b) => (b.graduated ?? 0) - (a.graduated ?? 0)
      || a.name.localeCompare(b.name, 'zh-Hant'),
  );
}

// ---------------------------------------------------------------------------
// Publications
// ---------------------------------------------------------------------------

export interface Author { family: string; given: string; orcid?: string }

export interface Publication {
  title: string;
  authors?: Author[];
  journal?: string;
  year?: number | null;
  month?: number | null;
  volume?: string;
  issue?: string;
  pages?: string;
  type?: string;
  doi?: string;
  pmid?: string;
  url?: string;
  sources?: string[];
}

export function getPublications(): Publication[] {
  return loadYaml<{ publications?: Publication[] }>('publications.yml', {}).publications ?? [];
}

/** Journal articles grouped by year, newest year first. */
export function publicationsByYear(): Array<[number, Publication[]]> {
  const grouped = new Map<number, Publication[]>();
  for (const publication of getPublications()) {
    const year = publication.year ?? 0;
    if (!grouped.has(year)) grouped.set(year, []);
    grouped.get(year)!.push(publication);
  }
  return [...grouped.entries()].sort((a, b) => b[0] - a[0]);
}

export interface LegacyPublications {
  conference_papers?: string[];
  patents?: string[];
  books?: string[];
}

export function getLegacyPublications(): LegacyPublications {
  return loadYaml<LegacyPublications>('publications-legacy.yml', {});
}

/**
 * Render an author list the way a citation does.
 *
 * Long lists are truncated because a page listing 112 papers with 20 authors
 * each becomes unreadable; the DOI link carries the complete record.
 */
export function formatAuthors(authors: Author[] | undefined, limit = 8): string {
  if (!authors?.length) return '';
  const names = authors.map((author) => {
    const initials = (author.given ?? '')
      .split(/[\s-]+/)
      .filter(Boolean)
      .map((part) => `${part[0]}.`)
      .join(' ');
    return [author.family, initials].filter(Boolean).join(', ');
  });
  if (names.length <= limit) return names.join('; ');
  return `${names.slice(0, limit).join('; ')}; et al.`;
}

// ---------------------------------------------------------------------------
// Everything else
// ---------------------------------------------------------------------------

export interface LinkRef { label: string; url: string }
export interface NewsItem { text: string; year?: number; links?: LinkRef[] }
export interface Achievement { citation: string; links?: LinkRef[] }
export interface Service { name: string; description?: string; image?: string; links?: LinkRef[] }

export function getNews(): NewsItem[] {
  return loadYaml<{ news?: NewsItem[] }>('news.yml', {}).news ?? [];
}

export function getHonors(): { achievements: Achievement[]; award_highlights: string[] } {
  const parsed = loadYaml<{ achievements?: Achievement[]; award_highlights?: string[] }>(
    'honors.yml',
    {},
  );
  return {
    achievements: parsed.achievements ?? [],
    award_highlights: parsed.award_highlights ?? [],
  };
}

export function getServices(): Service[] {
  return loadYaml<{ services?: Service[] }>('services.yml', {}).services ?? [];
}

export function getProfessionalActivities(): Record<string, string[]> {
  const parsed = loadYaml<Record<string, string[]>>('professional-activities.yml', {});
  // Drop the leading comment key if PyYAML ever emits one.
  return Object.fromEntries(
    Object.entries(parsed).filter(([key]) => !key.startsWith('_')),
  );
}

/** UI strings for one locale. */
export function getStrings(locale: Locale): Record<string, string> {
  const file = locale === 'en' ? 'i18n/en.yml' : 'i18n/zh.yml';
  return loadYaml<Record<string, string>>(file, {});
}

export interface ResearchGroup { group: string; items: string[] }
export interface ResearchHighlight { title: string; excerpt?: string; image?: string }

interface PagesFile {
  about?: {
    en?: { paragraphs?: string[] };
    zh?: { paragraphs?: string[]; same_as_en?: boolean };
  };
  research_areas?: { zh?: ResearchGroup[]; en?: ResearchGroup[] };
  research_highlights?: ResearchHighlight[];
  courses?: { zh?: string[]; en?: string[] };
  home_intro?: { zh?: string; en?: string };
  home_video?: string;
  professor?: ProfessorProfile;
}

export interface ProfessorProfile {
  appointments_zh?: string[];
  appointments_en?: string[];
  background_zh?: string[];
  background_en?: string[];
  biography_zh?: string[];
  biography_en?: string[];
}

function pages(): PagesFile {
  return loadYaml<PagesFile>('pages.yml', {});
}

/**
 * About-page prose for a locale.
 *
 * The Chinese block may be marked `same_as_en`, which is the honest state of
 * this content: the old site's `_zh` About page carried English text and no
 * Chinese version exists. Falling back is better than machine-translating the
 * lab's account of its own work.
 */
export function getAboutParagraphs(locale: Locale): string[] {
  const about = pages().about ?? {};
  if (locale === 'en') return about.en?.paragraphs ?? [];
  if (about.zh?.same_as_en || !about.zh?.paragraphs?.length) return about.en?.paragraphs ?? [];
  return about.zh.paragraphs;
}

export function getResearchAreas(locale: Locale): ResearchGroup[] {
  const areas = pages().research_areas ?? {};
  return (locale === 'en' ? areas.en : areas.zh) ?? [];
}

export function getResearchHighlights(): ResearchHighlight[] {
  return pages().research_highlights ?? [];
}

export function getCourses(locale: Locale): string[] {
  const courses = pages().courses ?? {};
  return (locale === 'en' ? courses.en : courses.zh) ?? [];
}

export function getHomeIntro(locale: Locale): string {
  const intro = pages().home_intro ?? {};
  return (locale === 'en' ? intro.en : intro.zh) ?? '';
}

export function getHomeVideo(): string {
  return pages().home_video ?? '';
}

/**
 * Principal-investigator profile lists for a locale.
 *
 * English copy for the appointment, background and biography lists does not
 * exist in the repository, so the English page falls back to the Chinese text
 * rather than showing nothing or an invented translation.
 */
export function getProfessorProfile(locale: Locale): {
  appointments: string[]; background: string[]; biography: string[];
} {
  const profile = pages().professor ?? {};
  const pick = (en: string[] | undefined, zh: string[] | undefined) => (
    locale === 'en' ? (en?.length ? en : zh ?? []) : zh ?? []
  );
  return {
    appointments: pick(profile.appointments_en, profile.appointments_zh),
    background: pick(profile.background_en, profile.background_zh),
    biography: pick(profile.biography_en, profile.biography_zh),
  };
}
