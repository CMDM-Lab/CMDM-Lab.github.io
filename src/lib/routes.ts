// The site's page inventory and URL mapping.
//
// Chinese owns the bare paths (`/members/`) and English is prefixed
// (`/en/members/`), matching astro.config.mjs. Every page is declared once,
// here, so the navigation, the hreflang pairs and the legacy redirect shim
// cannot drift apart.

import type { Locale } from './data';

export interface Route {
  /** Path segment shared by both locales; empty string is the home page. */
  slug: string;
  /** i18n key for the navigation label. */
  navKey: string;
  /** i18n keys for <title> and <meta name="description">. */
  titleKey: string;
  descriptionKey: string;
  /** Whether the page appears in the main navigation. */
  inNav: boolean;
}

export const ROUTES: Route[] = [
  { slug: '', navKey: 'nav_home', titleKey: 'home_title', descriptionKey: 'home_description', inNav: true },
  { slug: 'about', navKey: 'nav_about', titleKey: 'about_title', descriptionKey: 'about_description', inNav: true },
  { slug: 'research', navKey: 'nav_research', titleKey: 'research_title', descriptionKey: 'research_description', inNav: true },
  { slug: 'publications', navKey: 'nav_publications', titleKey: 'publications_title', descriptionKey: 'publications_description', inNav: true },
  { slug: 'honors', navKey: 'nav_honors', titleKey: 'honors_title', descriptionKey: 'honors_description', inNav: true },
  { slug: 'members', navKey: 'nav_members', titleKey: 'members_title', descriptionKey: 'members_description', inNav: true },
  { slug: 'services', navKey: 'nav_services', titleKey: 'services_title', descriptionKey: 'services_description', inNav: true },
  { slug: 'courses', navKey: 'nav_courses', titleKey: 'courses_title', descriptionKey: 'courses_description', inNav: true },
  { slug: 'activities', navKey: 'nav_activities', titleKey: 'activities_title', descriptionKey: 'activities_description', inNav: false },
  { slug: 'professor', navKey: 'professor_title', titleKey: 'professor_title', descriptionKey: 'professor_description', inNav: false },
];

/**
 * Legacy query-string routes, mapped to their replacement slug.
 *
 * The old site addressed every page as `index_zh.html?content=<key>`, which is
 * why a search engine saw one URL for the whole site. Two of these keys --
 * `publication` and `prof_acts` -- had content pages that no route and no
 * navigation link ever reached, so they were unreachable in practice; they get
 * real URLs now.
 */
export const LEGACY_CONTENT_MAP: Record<string, string> = {
  home: '',
  about: 'about',
  research: 'research',
  honor: 'honors',
  member: 'members',
  course: 'courses',
  service: 'services',
  janetseng: 'professor',
  publication: 'publications',
  prof_acts: 'activities',
};

/** Build a site-root-relative URL for a slug in a locale. */
export function pathFor(slug: string, locale: Locale): string {
  const prefix = locale === 'en' ? '/en' : '';
  return slug ? `${prefix}/${slug}/` : `${prefix}/`;
}

/** The BCP 47 tag to put in `lang` and `hreflang`. */
export function langTag(locale: Locale): string {
  return locale === 'en' ? 'en' : 'zh-Hant-TW';
}

export function otherLocale(locale: Locale): Locale {
  return locale === 'en' ? 'zh-Hant' : 'en';
}
