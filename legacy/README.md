# Legacy site (retained, not served)

This directory holds the pre-2026 site: the jQuery-loaded HTML fragments, the
Bootstrap 3 and Font Awesome vendor bundles, and the two `index*.html` entry
points that routed everything through `?content=<key>`.

Nothing here is served. It is not under `public/`, so the Astro build does not
copy it into `dist/`.

## Why it is still in the repository

It is kept for provenance, not for use. The content migration was mechanical
(`scripts/migration/extract-legacy-content.py`) and its output — the YAML files
under `data/` — is what the site now renders. Keeping the inputs around means
any migration question can be answered by diffing against the original rather
than by guessing.

Specifically, these files are the *only* record of:

- the 42 graduates listed for 2008–2022, now in `data/alumni-historical.yml`
- 64 conference papers and 17 patents, now in `data/publications-legacy.yml`,
  which no DOI-based API can supply
- 54 professional-activity entries, now in `data/professional-activities.yml`

Two of the pages here — `contents/publication*.html` and
`contents/prof_acts*.html` — were never reachable on the live site: no router
branch and no navigation link pointed at them. Their content is live now.

## Before deleting

Confirm each of these, then remove the directory in its own commit:

1. `data/publications.yml` still covers every journal paper listed in
   `contents/honor_zh.html` section B and `contents/publication_zh.html`
   section A. This was verified once during the migration (81 legacy citations,
   all matched); re-verify if the fetch script's source strategy changes.
2. The redirect shim in `public/index_zh.html` and on the home page covers every
   `?content=` key that appears in `LEGACY_CONTENT_MAP` (`src/lib/routes.ts`).
3. No image or file under `public/` is referenced only from here.
