# Design source

`CMDM Lab Design System.zip` is the Claude Design bundle for the lab's
"Clinical Instrument" visual language. It is the authoritative reference for
every colour, size, weight, tracking value, spacing step and border width in
this site — its own handoff states that those values are final and must be
reproduced exactly, not snapped to a 4/8px grid or rounded.

It is kept here for provenance. Nothing in the build reads it.

## What was taken from it, and where it went

| Bundle path | Here |
| --- | --- |
| `tokens/fonts.css`, `colors.css`, `spacing.css`, `borders.css`, `layout.css` | `src/styles/tokens/` — copied verbatim |
| `tokens/typography.css` | `src/styles/tokens/typography.css` — verbatim, plus a resolution layer (below) |
| `tokens/base.css` | `src/styles/tokens/base.css` — verbatim, with a few extra element defaults |
| `components/**/*.jsx` | `src/styles/components.css` + `src/components/ds/` |
| `components/navigation/MobileNav.jsx`, `guidelines/mobile.html` | `src/components/ds/MobileNav.astro` + the mobile section of `src/styles/components.css` |
| `ui_kits/lab_site/messages.js` | `data/i18n/{zh,en}.yml`, and the home page's lab-record copy in `data/pages.yml` |
| `assets/` | already present as `public/img/` — the bundle sourced them from this repository |

## Deliberate departures

The bundle ships React prototypes with inline style objects and says plainly not
to ship them: *"Do not ship them as production code… recreate these designs in
the target codebase's own environment using its established patterns."* So:

1. **Astro components and CSS classes, not React with inline styles.** The specs
   are transcribed into `src/styles/components.css`; `src/components/ds/`
   applies them and fixes the element order the design system requires
   (`Masthead`, `LedgerItem`).

2. **CJK typography comes from `lang`, not a prop.** The bundle threads a `cjk`
   boolean through six components. The handoff says a lang-scoped CSS block is
   the cleaner production form, and it is: the `:root:lang(zh-Hant)` block in
   `tokens/typography.css` swaps the resolved measure, size, leading, alignment
   and kicker treatment, so no component branches and there is no prop to
   forget. `tests/design-adherence.test.mjs` asserts the selector still matches
   the `lang` attribute the layout emits — that is silent when broken.

3. **One bundled stylesheet, not seven `@import`s.** The bundle's `styles.css`
   imports seven token files at runtime. Here `src/styles/site.css` imports them
   at build time and Astro emits one hashed file, avoiding a render-blocking
   import chain.

4. **Ten routes, not five screens.** The bundle recreates five screens
   (Overview, Research, Tools, People, Publications). This site keeps ten
   routes, because giving every page a real URL is the entire point of the
   rebuild — the previous site served all of its pages from one URL and was
   therefore invisible to search engines. The five pages the bundle does not
   cover (About, Honors, Courses, Activities, and the PI page) use the same
   language. The bundle explicitly left the Honors page as a decision for the
   lab; it is its own page here.

5. **Emails are written out in full.** The bundle specifies the lab's old
   `name[at]host` convention and says to keep it. It is not kept, because as
   implemented it protected nothing: the `mailto:` href on the same element
   carries the real address, so a harvester reading hrefs — the easy path — was
   never slowed, while a reader trying to copy the address was. Publishing the
   address plainly is the normal choice for a lab page; the alternatives that
   actually work either need JavaScript (breaking no-JS readers and crawlers) or
   a form with a backend, and neither is worth it here.

6. **Real numbers instead of written-in ones.** The Overview colophon in the
   bundle lists figures like "40+ alumni". Those are counted from `data/` here,
   so they cannot go stale the way the old site's did.

7. **The mobile drawer is `<details>`, not component state.** The bundle's
   `MobileNav` holds `open` in React state and swaps itself in by calling
   `matchMedia` at runtime; a prerendered page has neither. So the drawer is a
   native `<details>`/`<summary>` disclosure and the swap is `display:none` on
   either side of the same 767px breakpoint. This is not a workaround, it is the
   better form here: the drawer opens, closes and announces its state with
   scripting off, which matters on a site whose whole reason for existing is
   that the previous one hid its content behind JavaScript. The body scroll lock
   is `body:has(.ds-mnav__menu[open])`. The one thing `<details>` does not give
   is Escape-to-close, and that -- eight lines -- is the only script in the
   component.

8. **Stacked-table labels come from `--col-1..--col-6`.** Rows are slot markup
   here, so `DataTable` never sees the cells and cannot put a `data-label` on
   each one. The column heads are handed to CSS as custom properties on the
   table element instead, and `td::before` reads them by `nth-child`, so a
   cell's mobile label and its `<th>` are the same string by construction. The
   stylesheet declares six; `DataTable` throws above that rather than dropping
   the last column's labels silently.

9. **ArticleFlow folds to one column below the breakpoint.** The bundle's token
   override collapses every auto-fit *grid*, because those read `--grid-min-*`.
   ArticleFlow is a CSS multi-column, so nothing re-points it, and at 390px the
   English About page kept two 170px justified columns that hyphenated mid-word
   on nearly every line. The system's own rule -- "everything below it is one
   column" -- is applied to it explicitly.

## Open questions the bundle raises

Its own "Open questions for the lab" section asks four things, none of which
this implementation can answer:

1. **Icons.** Lucide is a flagged substitution. This site currently uses no
   icons at all — the only glyph used iconographically is the `→` at the end of
   a link, which is what the system specifies. Nothing needs Lucide yet.
2. **Font delivery.** Fonts load from Google Fonts. Self-hosting would change
   `tokens/fonts.css` only; the family names stay.
3. **The accent `#8c1d2f`** is inherited from the MetaboMAIA frontend, not
   chosen for CMDM. Changing it is one token.
4. **Whether `#B4D6E0` should do more.** It is currently limited to `brand`
   tags on the Tools page, per the whitelist.

## Mobile

The bundle's mobile rules are ported as it asks: one breakpoint at 768px, and
the reflow is the token override in `tokens/layout.css` re-pointing
`--page-pad-*`, `--nav-pad-*`, `--column-gap` and `--grid-min-*` -- not a set of
`sm:` prefixes through the components. Two components change shape (the nav
becomes a drawer, a table becomes stacked records) and the type ramp is not
scaled at all.

Verified in a 390 x 844 viewport: all twenty routes have `scrollWidth` equal to
`innerWidth` -- nothing scrolls sideways -- the hamburger measures 44 x 44, the
drawer rows 52px, the scroll lock engages, and Escape closes the drawer.

## Enforcement

The rules in this bundle are prose, and its handoff records that four of them
"had already drifted" in the source project. They are tests here:
`tests/design-adherence.test.mjs` covers the prohibitions (no dark mode, no
radius but a true circle, no shadow, no bare hex in components, no framework
palettes, every rule inside `@layer`) and the four prose-only rules (the accent
whitelist, the 12px body-copy floor, spacing from tokens only, a 2px focus
ring).

The mobile rules are all negative -- one breakpoint, no scaled ramp, no
sideways-scrolling table, no scrim, no sheet -- and a negative rule produces no
output when it is broken, on the one viewport nobody has open while editing CSS.
So there are six more: one breakpoint only, the token override keeps the ramp
out, the 44px floor is read from the token rather than restated, the drawer is a
solid page with a bordered toggle, the drawer's rows and ordinals are computed
from the route table, and no stacked table loses a label or scrolls sideways.
Each was verified by breaking it and watching the suite go red.
