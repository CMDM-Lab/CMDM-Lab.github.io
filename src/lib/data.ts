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

/**
 * A value that may be written once for both locales, or split per locale.
 *
 * Content migrated from the old site is Chinese-only, so a plain string has to
 * keep working; new entries are written as `{zh, en}` pairs. This mirrors the
 * design system's own `L(value, locale)` helper.
 */
export type Localized<T> = T | { zh?: T; en?: T };

/**
 * Resolve a possibly-localized value.
 *
 * Chinese is the fallback rather than English, because the untranslated content
 * on this site is Chinese: falling back the other way would render empty.
 */
export function pickLocale<T>(value: Localized<T> | undefined, locale: Locale): T | undefined {
  if (value === null || value === undefined) return undefined;
  if (typeof value !== 'object' || Array.isArray(value)) return value as T;
  const pair = value as { zh?: T; en?: T };
  if (!('zh' in pair) && !('en' in pair)) return value as T;
  return (locale === 'en' ? pair.en ?? pair.zh : pair.zh ?? pair.en);
}

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
  /**
   * Deposited thesis title, shown in the alumni table.
   *
   * Distinct from `research`: that is the vault's working topic and is never
   * rendered, while this is the public bibliographic record. Curated in
   * members-overrides.yml, so an entry only has one once someone has confirmed
   * the title that was actually deposited.
   */
  thesis?: string;
  /**
   * Coarse research area, always one of the pillars in data/pages.yml.
   *
   * The page shows this instead of `research`: the specific topic is working
   * shorthand from the vault, while the area keeps the entry searchable without
   * publishing what anyone is doing this month. Assigned by
   * scripts/sync-members.mjs.
   */
  area?: string;
  area_en?: string;
  /**
   * Named projects this person leads. Kept apart from `area` on purpose: a
   * project is a responsibility, often for an external system, and folding it
   * into the research area would blur what that column means.
   */
  projects?: Array<{ name: string; name_en?: string; short?: string; url?: string }>;
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

/**
 * Department codes used in the vault roster, spelled out for the public site.
 *
 * Declaration order is the order the affiliation line lists them: the lab's own
 * department first, then the institutes and degree programs its members come
 * from. `short` is the form the lab uses in running text.
 *
 * Official names and sites, so a correction has something to check against:
 *   CSIE  https://www.csie.ntu.edu.tw/
 *   BEBI  https://bebi.ntu.edu.tw/
 *   GSB   https://ntugsb.ntu.edu.tw/       基因體與系統生物學學位學程
 *   MHI   https://mhi.ntu.edu.tw/
 *   GINM  https://www.ginm.ntu.edu.tw/     資訊網路與多媒體研究所（網媒所）
 *
 * Keep in step with DEPARTMENT_CODES in scripts/lib/vault-members.mjs;
 * tests/members-privacy.test.mjs asserts the two agree.
 */
const DEPARTMENT_LABELS: Record<string, { 'zh-Hant': string; en: string; short: { 'zh-Hant': string; en: string } }> = {
  CSIE: {
    'zh-Hant': '資訊工程學系',
    en: 'Department of Computer Science and Information Engineering',
    short: { 'zh-Hant': '臺大資工系', en: 'NTU CSIE' },
  },
  BEBI: {
    'zh-Hant': '生醫電子與資訊學研究所',
    en: 'Graduate Institute of Biomedical Electronics and Bioinformatics',
    short: { 'zh-Hant': '生醫電資所', en: 'BEBI' },
  },
  GSB: {
    'zh-Hant': '基因體與系統生物學學位學程',
    en: 'Genome and Systems Biology Degree Program',
    // No abbreviation: the previous line called this 基因體所, which is not the
    // name of anything -- it is a degree program, not an institute.
    short: { 'zh-Hant': '基因體與系統生物學學位學程', en: 'GSB' },
  },
  MHI: {
    'zh-Hant': '智慧醫療與健康資訊學程',
    en: 'Master Program in Health Informatics',
    short: { 'zh-Hant': '智慧醫療學程', en: 'MHI' },
  },
  GINM: {
    'zh-Hant': '資訊網路與多媒體研究所',
    en: 'Graduate Institute of Networking and Multimedia',
    short: { 'zh-Hant': '網媒所', en: 'GINM' },
  },
};

export function departmentLabel(code: string | undefined, locale: Locale): string {
  if (!code) return '';
  const known = DEPARTMENT_LABELS[code.trim()];
  return known ? known[locale] : code;
}

/**
 * The year the lab was founded, and the year its output is counted from.
 *
 * The PI returned to NTU as an assistant professor in 2006 (see the biography
 * in data/pages.yml) and the site's colophon has carried "2006" since the
 * previous version. The journal list reaches back to 2003, but those four
 * entries are her own postdoc work at UIC and the NCBI -- before the lab
 * existed -- so the lab's own count starts here.
 */
export const LAB_FOUNDED = 2006;

/** Journal articles published since the lab was founded. */
export function labPublicationCount(): number {
  return getPublications().filter((entry) => (entry.year ?? 0) >= LAB_FOUNDED).length;
}

/**
 * The units the lab's members belong to, in declaration order.
 *
 * Read from the roster rather than written down: the hand-written version had
 * "基因體所", a name for a unit that does not exist, and was missing 網媒所
 * although a member has been in it. A department appears here the moment
 * someone from it joins, and disappears when the last of them leaves.
 */
export function labAffiliations(locale: Locale): string {
  const present = new Set(
    getMembers()
      .filter((member) => member.role !== 'alumni')
      .map((member) => (member.department ?? '').trim())
      .filter(Boolean),
  );
  return Object.entries(DEPARTMENT_LABELS)
    .filter(([code]) => present.has(code))
    .map(([, labels]) => labels.short[locale])
    .join(' · ');
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

interface RawAchievement { citation?: Localized<string>; links?: LinkRef[] }

/**
 * Awards, resolved for one locale, newest first as stored.
 *
 * One list. The old site kept a second, overlapping "highlights" list for its
 * home page, and carrying both over printed the same award twice on the
 * honours page. Callers that want a teaser take a slice.
 */
export function getHonors(locale: Locale): Achievement[] {
  const parsed = loadYaml<{ achievements?: RawAchievement[] }>('honors.yml', {});
  return (parsed.achievements ?? [])
    .map((entry) => ({
      citation: pickLocale(entry.citation, locale) ?? '',
      ...(entry.links ? { links: entry.links } : {}),
    }))
    .filter((entry) => entry.citation);
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

/**
 * A representative paper on the Research page.
 *
 * `excerpt` is the opening of the paper's own abstract, not a paraphrase.
 * `tag` is an instrument or assay classification, which is the register the
 * design system reserves brand-tone tags for.
 */
export interface ResearchHighlight {
  title: string;
  excerpt?: string;
  tag?: string;
  meta?: string;
  doi?: string;
  image?: string;
  /** Attribution for `image`. Required by the CC licences these are reused under. */
  image_credit?: string;
}

interface PagesFile {
  about?: {
    en?: { paragraphs?: string[] };
    zh?: { paragraphs?: string[]; same_as_en?: boolean };
  };
  research_areas?: { zh?: ResearchGroup[]; en?: ResearchGroup[] };
  research_highlights?: ResearchHighlight[];
  courses?: { zh?: string[]; en?: string[] };
  home_intro?: { zh?: string; en?: string };
  home_record?: { zh?: string[]; en?: string[] };
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
  return getAbout(locale).paragraphs;
}

/**
 * About-page prose plus whether it is a fallback.
 *
 * `translated` is false when the Chinese page is showing the English text, and
 * the layout needs to know: CJK rules set one narrow column, which is right for
 * Hanzi and wrong for five paragraphs of English -- it leaves half the page
 * empty. The About page reads this and lays the fallback out as Latin prose, so
 * the moment real Chinese copy lands the layout corrects itself.
 */
export function getAbout(locale: Locale): { paragraphs: string[]; translated: boolean } {
  const about = pages().about ?? {};
  if (locale === 'en') return { paragraphs: about.en?.paragraphs ?? [], translated: true };
  const hasChinese = !about.zh?.same_as_en && Boolean(about.zh?.paragraphs?.length);
  return hasChinese
    ? { paragraphs: about.zh!.paragraphs!, translated: true }
    : { paragraphs: about.en?.paragraphs ?? [], translated: false };
}

export function getResearchAreas(locale: Locale): ResearchGroup[] {
  const areas = pages().research_areas ?? {};
  return (locale === 'en' ? areas.en : areas.zh) ?? [];
}

/**
 * Roman numeral for a zero-based index, as the design system's ledgers use.
 *
 * Computed rather than read from a fixed array. A hardcoded list silently drops
 * the index off any item past its end -- LedgerItem renders nothing for an
 * undefined index -- which is exactly what happened when the research pillars
 * grew from two to five against a four-entry array.
 */
export function romanNumeral(index: number): string {
  const NUMERALS: Array<[number, string]> = [
    [1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'],
    [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'],
    [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
  ];
  let remaining = index + 1;
  let out = '';
  for (const [value, symbol] of NUMERALS) {
    while (remaining >= value) {
      out += symbol;
      remaining -= value;
    }
  }
  return out;
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
 * The home page's lab-record paragraphs.
 *
 * These exist in both locales because the design bundle supplied both. The
 * longer About text does not, which is why that page still falls back to
 * English -- see getAboutParagraphs.
 */
export function getHomeRecord(locale: Locale): string[] {
  const record = pages().home_record ?? {};
  return (locale === 'en' ? record.en : record.zh) ?? record.en ?? [];
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
