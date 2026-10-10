# Gym — project instructions for Claude Code

Read `HANDOFF.md` first; it is the source of truth for scope, design, data model and business rules.
Then read `PLANNING.md`: the phased feature plan. **Only Phase 1 is in scope until it is done** (public site,
block page builder, PWA, SEO, minimal admin). Phase 2+ is planned afterwards.

Design tokens live in `design-tokens.css` (copy to `assets/styles/design-tokens.css` in the Symfony app).
Visual reference: the static HTML pages in `./reference-html/` (open `reference-html/index.html`) — use them as the
pixel reference for layout, copy and section order; translate their inline styles into Tailwind tokens and Twig Components.
PNG exports of the design canvas go in `docs/design/` once available.

## Stack (do not deviate without asking)
- Symfony 7, PHP 8.3, MySQL/MariaDB, Doctrine ORM + migrations, Messenger for async mail.
- Templates: Twig + Symfony UX Twig Components (anonymous components in `templates/components/`), Live Components for
  the admin page editor and booking flows. No Blade, no React/Vue. ("Blade components" in briefs = Twig Components.)
- JS: Stimulus controllers only (`assets/controllers/`), Turbo for navigation.
- CSS: Tailwind v4 via symfonycasts/tailwind-bundle + AssetMapper. Use the tokens; never hard-code colours, radii or font names in templates.
- i18n: all user-facing strings through `trans` with `nl` default and `en`. Copy in `translations/messages.{nl,en}.yaml`.
- PWA: web manifest + hand-written service worker (no Workbox), offline page, install prompt via a Stimulus controller.
- SEO: `Seo:Head` component (meta, canonical, hreflang, OG), JSON-LD, `sitemap.xml`, `robots.txt`, admin-editable SEO fields per page.
- Page builder: `Page` → ordered `PageBlock[]`; each block type is one PHP class in `src/PageBuilder/Block/` + one form + one
  `Block:*` Twig Component. Adding a block never requires touching the renderer.

## House style rules
- Dark theme, square corners (0 px) everywhere except avatars, 1 px hairlines, amber accent only for primary actions, active states, labels and "yours" items.
- Cormorant Garamond 600 for headings, Barlow for everything else. Uppercase 12 px labels with 0.18em tracking.
- Real `<button>`, `<a>`, `<input>` + `<label>`; touch targets ≥ 44 px; contrast ≥ 4.5:1; respect `prefers-reduced-motion`.
- Dutch copy as written in the handoff; never invent prices, names, addresses or testimonials — use the `[PLACEHOLDER]` values and `SiteSetting`s with TODOs.

## Working agreements
- Build in the order of `PLANNING.md` §1.12; each step ends with migrations, fixtures and tests passing (`make test`).
- Business rules in HANDOFF §8 are implemented as domain services with unit tests (Phase 2+).
- Mails are Twig templates in the house style, per-locale.
- Ask before adding dependencies beyond Symfony UX, Doctrine, Foundry, Panther, dompdf/mpdf (invoices).
- Optional animation Stimulus controllers (PLANNING §1.11) are built only after the owner picks them.
