# CMDM Lab Website

The website of the Computational Molecular Design and Metabolomics Lab,
National Taiwan University — <https://www.cmdm.tw>.

Static site, built with [Astro](https://astro.build) and deployed to GitHub
Pages. Content is Traditional Chinese first, with an English translation of
every page. Code comments, commit messages and pull requests are in English.

```bash
npm install
npm run dev      # local dev server
npm run build    # static build into dist/
npm test         # privacy and URL tripwires
```

## Why this was rebuilt

The previous site loaded every page through jQuery `.load()` calls behind a
single URL: `index_zh.html?content=member`, `?content=honor`, and so on. A
crawler fetching any of those URLs received the same 2,664 bytes of routing
script and no content. The only indexable text on the entire site was the
`<title>` of the home page, and there was no `robots.txt` and no sitemap.

That is why lab material did not show up in search results. It was not a
ranking problem; the content was never crawlable in the first place.

What changed:

| | Before | After |
|---|---|---|
| URLs | one, with a query string | 20 real paths, 10 per locale |
| Crawlable content | 0 bytes | full page text in the initial response |
| `<title>` / description | identical / empty on all pages | unique per page |
| `robots.txt`, `sitemap.xml` | 404 | present, sitemap with hreflang |
| `<html lang>` | `en` on Chinese pages | correct per locale, hreflang paired |
| Structured data | none | Organization, Person, ScholarlyArticle, SoftwareApplication |
| Unreachable pages | 2 (publications, activities) | routed |

## Architecture

```
data/          YAML — the single source of truth for all content
scripts/       sync scripts that regenerate the *_generated* data files
src/
  lib/         data loaders and the route table
  layouts/     the page shell, which emits all SEO metadata
  components/  one component per page, shared by both locales
  pages/       thin per-locale route entries
public/        static assets, robots.txt, the legacy redirect shim
legacy/        the pre-2026 site, retained for provenance, not served
tests/         privacy and URL tripwires
```

Each page body is a single component used by both `src/pages/x.astro` and
`src/pages/en/x.astro`, so the two locales cannot drift apart.

## Data files

Generated files carry a `_generated` key and must not be hand-edited. Fix them
by editing their companion overrides file and re-running the sync.

| File | Source | Edit via |
|---|---|---|
| `members.yml` | CMDM-Lobe vault | `members-overrides.yml` |
| `publications.yml` | ORCID + Crossref | `publications-overrides.yml` |
| `*-review.yml` | the sync scripts | resolve into the overrides file |
| `alumni-historical.yml` | old site, one-off migration | by hand |
| `publications-legacy.yml` | old site, one-off migration | by hand |
| `honors.yml`, `news.yml`, `services.yml`, `professional-activities.yml`, `pages.yml`, `i18n/*.yml` | — | by hand |

### Publications

`scripts/fetch-publications.mjs` builds the list in two tiers.

Tier 1 publishes unattended: the ORCID profile (101 works, back to 2003) unioned
with the PubMed full-name query. ORCID is primary because the obvious source
turns out to be the weaker one — PubMed only records a full author name when the
publisher supplied it, so `Tseng Yufeng Jane[Author]` retrieves 48 works and
silently drops 65 pre-2015 papers indexed only as `Tseng YJ`. The full-name
query still earns its place: it finds 12 papers ORCID does not have. Both are
then enriched from Crossref by DOI.

Tier 2 never publishes unattended. `Tseng YJ[Author] AND National Taiwan
University[Affiliation]` returns 127 hits, but that name collides with another
NTU researcher, so its extra results mix real lab output with tigecycline dosing
and retinal-ganglion-cell studies. Those land in `publications-review.yml` for a
human to sort into the `include:` or `exclude:` list of
`publications-overrides.yml`.

Conference abstracts, patents and books are not registered as DOIs and no API
can supply them. They live in `publications-legacy.yml`, migrated out of the old
pages, and are hand-maintained.

### Members

`scripts/sync-members.mjs` projects the CMDM-Lobe roster onto the site.

**CMDM-Lobe is a private repository.** Its roster mixes publishable facts with
material that must never leave the vault: oral-defense dates and outcomes,
thesis-draft Drive links, certification progress, leave-of-absence status. The
projection is therefore deny-by-default in two directions — a section is skipped
unless `SECTION_POLICY` names it, and a column is dropped unless that section
allowlists it. See `scripts/lib/vault-members.mjs`.

`tests/members-privacy.test.mjs` enforces this. Its strongest assertions read
the committed `data/members.yml`, so they run with no vault present and catch a
leak in CI rather than on the live site. Adding a new public field is a
deliberate act: the test fails until the field is declared safe.

Setting `publish: false` on someone in `members-overrides.yml` keeps them off
the site entirely. That is the mechanism for anyone who asks not to be listed.

## Automation

`.github/workflows/sync-data.yml` runs weekly, regenerates both datasets, and
opens a pull request. It never commits directly: the member data derives from a
private vault, and a human reading the diff is a cheap last line of defence. The
privacy tests and a full build must pass before the PR is opened.

`.github/workflows/deploy.yml` builds and publishes on push to `master`, running
the tests first.

### Setup still required

1. **Pages source.** Settings → Pages → Source must be changed from the `master`
   branch to **GitHub Actions**. Until then, `deploy.yml` builds but cannot
   deploy. `public/CNAME` carries the custom domain through the switch.
2. **Vault token.** Add a fine-grained PAT with `Contents: read` on
   `CMDM-Lab/CMDM-Lobe` as the secret `CMDM_LOBE_TOKEN`. Without it the member
   sync is skipped and only publications refresh; the run still succeeds.
3. **Search Console.** Add `www.cmdm.tw`, submit
   `https://www.cmdm.tw/sitemap-index.xml`, and request indexing for the new
   URLs. Nothing about the rebuild makes Google re-crawl on its own.

## Known gaps

- **No Chinese copy for About and Research.** The old site had `_zh` variants of
  both, but their bodies were English prose; no Chinese version exists anywhere
  in the repository. Rather than machine translate the lab's own account of its
  research, the English text is reused in both locales and flagged in
  `data/pages.yml`. Same for the professor page's appointment and biography
  lists.
- **Professor appointments need verifying.** The list in `data/pages.yml` is
  copied from a page last touched in 2022 and describes roles as of roughly
  2018. Several are fixed-term.
- **Email policy changed.** The old site published students' and graduates'
  personal Gmail addresses. Now: faculty, staff and postdocs list a professional
  contact, current students list institutional addresses only, alumni list none.
  Reverse it in `members-overrides.yml` if that is not wanted.
- **Romanised names are incomplete.** Only the names recoverable from the old
  site are in `members-overrides.yml`. These matter for search — people look for
  "Ying-An Lin", not only the Chinese name.
