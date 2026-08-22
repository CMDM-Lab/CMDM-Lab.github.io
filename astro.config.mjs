// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// The lab site is fully static and served from GitHub Pages behind the
// www.cmdm.tw custom domain. `site` must stay in sync with CNAME: the sitemap
// integration and every canonical/hreflang tag are derived from it.
const SITE = 'https://www.cmdm.tw';

export default defineConfig({
  site: SITE,
  output: 'static',
  trailingSlash: 'always',
  build: {
    // Emit `about/index.html` rather than `about.html` so that canonical URLs
    // and on-site links agree on a single directory-style form.
    format: 'directory',
  },
  i18n: {
    // Chinese is the primary content language, so it owns the bare paths
    // (`/members/`) and English is prefixed (`/en/members/`).
    defaultLocale: 'zh-Hant',
    locales: ['zh-Hant', 'en'],
    routing: { prefixDefaultLocale: false },
  },
  integrations: [
    sitemap({
      // Legacy jQuery entry points are redirect shims, not content.
      filter: (page) => !page.includes('index_zh.html'),
      // Emit xhtml:link alternates so the two locales are declared as
      // translations of one another in the sitemap as well as in each page's
      // head. `defaultLocale` here keys into `locales` below, not into the
      // top-level i18n config.
      i18n: {
        defaultLocale: 'zh',
        locales: { zh: 'zh-Hant-TW', en: 'en' },
      },
    }),
  ],
});
