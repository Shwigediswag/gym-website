# Gym — Feature planning

Source of truth for scope and business rules: `HANDOFF.md`. Visual reference: `reference-html/` and `design-tokens.css`.
This file is the build plan. It is split into phases; **only Phase 1 is detailed**. Phase 2+ (member portal,
scheduling, billing, the "administrative" side of the admin) is planned after Phase 1 ships.

Additions on top of the handoff (agreed 2026-10-10):

| Addition | What it means |
|---|---|
| **PWA** | Installable site: web manifest, service worker (AssetMapper + Workbox-free, hand-written), offline fallback page, app icons, theme colour, install prompt. Marketing pages cached; portal/admin network-first. |
| **SEO** | Per-page meta (title/description/OG/Twitter), canonical + `hreflang` for nl/en, `sitemap.xml`, `robots.txt`, JSON-LD (`LocalBusiness`/`HealthClub`, `Person` for trainers), semantic headings, image `alt`, Lighthouse SEO ≥ 95. SEO fields live on the page entity and are editable in the page builder. |
| **Block page builder** | Admin CRUD where a page is an ordered list of typed **blocks**. Each block type = one Twig Component + one Symfony form. Pages are stored as entities (`Page` → `PageBlock[]`), rendered by a single catch-all controller, with preview, draft/publish, and nl/en content per block. |
| **Twig Components, not Blade** | The handoff and CLAUDE.md fix the stack as Symfony UX Twig Components. "Blade components" in the brief are read as Twig Components (`templates/components/`). |

---

## Phase 1 — Public site, page builder, PWA

**Goal:** the marketing site is live, fully editable by the owner through a block-based page builder, installable as a PWA,
and SEO-ready. Admin contains only what the site needs (login, pages, trainers, testimonials, gallery, site settings, intake inbox).
No portal, no scheduling, no billing.

**Definition of done for the phase:** all four public pages built from blocks match `reference-html/` on desktop and mobile;
`make test` green; Lighthouse ≥ 90 performance / ≥ 95 SEO / ≥ 95 accessibility / PWA installable; admin reachable only by the superadmin.

### 1.1 Foundation

- [x] Symfony 7.4 LTS skeleton (7.3 is blocked by Composer security advisories), PHP 8.3, `.env`, Docker Compose (php-fpm, nginx, mariadb, mailpit), `Makefile` + `make.ps1` (Windows shim). Done 2026-10-10.
- [x] Doctrine ORM + migrations, ULID ids, `TimestampableTrait` (`createdAt`/`updatedAt`).
- [x] AssetMapper + `symfonycasts/tailwind-bundle` (Tailwind v4). `assets/styles/app.css` imports `assets/styles/design-tokens.css` (copy of `design-tokens.css`).
- [x] Self-hosted fonts: Cormorant Garamond 500/600, Barlow 400/500/600 in `assets/fonts/`, `font-display: swap`, preload the two main files.
- [x] i18n: `nl` default, `en` second; locale in URL prefix (`/`, `/en`); `translations/messages.{nl,en}.yaml`. Translated route slugs per page follow with the pages (1.7).
- [x] Twig form theme `templates/form/gym_theme.html.twig` so Symfony Forms render as `Ui:*`.
- [x] Foundry fixtures + PHPUnit + Panther baseline; CI script (`make test` runs phpunit, lint:twig, lint:container, phpstan level 6).
- [x] `SiteSetting` entity (key/value, translatable) seeded with every `[PLACEHOLDER]` from HANDOFF §11 as TODO values.

### 1.0 Static front-end pass (done 2026-11-22, inserted before 1.2)

Order changed on request: all pages were first built as a **static front-end** inside the Symfony app, so the design
can be judged before deciding how content pages/blocks are implemented.

- [x] Pages as Twig templates, copy in `translations/messages.{nl,en}.yaml`: Homepage, Over ons, De studio, Aanmelden,
      Bedankt, Ledenportaal – Boeken. Localized slugs (`/over-ons` ↔ `/en/about`, …) in `PageController` / `BookingController`.
- [x] Components: `Ui:` Button, Label, Logo, Icon, ImagePlaceholder, Indicator, Avatar, Option, Chip, Field ·
      `Site:` Header (+ mobile menu), Footer, LocaleSwitch, StickyCta, Slider, CtaFrame · layouts `site` and `portal`.
- [x] Stimulus: `slider`, `sticky-cta`, `mobile-menu`, `gallery` (filter), `lightbox`, `form-progress`.
      Chips, option rows and the availability grid are real inputs styled with `:has()`, no JS.
- [x] Every `<section>` in a page template is one future block (see catalogue in 1.5).

Static-only shortcuts to undo later (all marked `STATIC FRONT-END` / `TODO` in code):
- Sign-up POST is accepted and discarded, then redirects to the thank-you page (real handling: 1.8).
- The portal route is public; the `ROLE_MEMBER` access rule in `security.yaml` is commented out until login exists (1.2).
- Trainers, testimonials, gallery photos and portal data are placeholders/sample data from the design reference.
- Header nav is unified on all pages: Ons verhaal · Trainen · Trainers · De studio (the reference home used "Ervaringen" as 4th item).

**Open decision:** how the content pages/blocks are implemented in Symfony (block page builder per 1.5, or simpler).

### 1.2 Security & admin shell

- [ ] `User` entity (email, password, roles, locale, isActive), roles `ROLE_MEMBER`, `ROLE_TRAINER`, `ROLE_ADMIN`, `ROLE_SUPER_ADMIN`.
- [ ] Form login at `/inloggen` in house style, remember-me, CSRF, login throttling, password-reset by e-mail (Messenger async).
- [ ] Console command `app:user:create-superadmin` + fixture superadmin (credentials in `.env.local` / fixture, never in docs).
- [ ] Admin shell `/admin`: `Admin:Shell` (sidebar: Pagina's · Trainers · Ervaringen · Galerij · Aanvragen · Instellingen), 14 px UI, same tokens. Access `ROLE_ADMIN`.
- [ ] Shared admin components: `Admin:Table`, `Admin:FilterBar`, `Admin:StatusBadge`, `Admin:Toolbar`, `Admin:Flash`, `Admin:ConfirmButton`.

### 1.3 Ui:* component library (Twig Components)

Build from `design-tokens.css` and the reference HTML. Each component gets a Twig snapshot test and a Storybook-style preview page at `/admin/_components` (dev env only).

| Component | Notes |
|---|---|
| `Ui:Button` | `variant: primary\|outline\|ghost`, `size`, `href?`, `type`, `icon?` — renders `<a>` or `<button>` |
| `Ui:Label` | 12 px uppercase, 0.18em, default amber |
| `Ui:Heading` | `level 1–3`, `size: display-xl\|lg\|md\|sm`, Cormorant 600 |
| `Ui:Logo` | wordmark + 9 px amber square |
| `Ui:HairlineGrid` / `Ui:HairlineCell` | `cols`, `gap-px` on `line` background |
| `Ui:Chip` | `type: checkbox\|radio\|button`, `selected`, `disabled` |
| `Ui:Segmented` | options + active |
| `Ui:Avatar` / `Ui:AvatarStack` | circle, −8 px overlap |
| `Ui:ImagePlaceholder` | hatch + caption; swapped for `Ui:Image` when a file exists |
| `Ui:Image` | responsive `<picture>`, lazy, width/height set, LiipImagine or GD resize presets |
| `Ui:Input`, `Ui:Textarea`, `Ui:Select`, `Ui:Checkbox`, `Ui:Field` | 48 px, amber focus ring, error state |
| `Ui:Icon` | inline stroke-SVG sprite (arrow, chevron-l/r, close, menu, check, phone, mail, instagram, download) |
| `Ui:Indicator` | dash indicator for sliders |

### 1.4 Site chrome

- [ ] `Site:Header` (nav links are anchors or pages, configurable in settings), `Site:MobileMenu` (full-screen, 44 px hamburger), `Site:Footer` (adres · telefoon · Instagram · Privacy · Voorwaarden · NL/EN switch, all from `SiteSetting`).
- [ ] `Site:StickyCta` (desktop right-edge tab after ~400 px scroll, mobile bottom bar, hidden on `/aanmelden`).
- [ ] Base layout `templates/base.html.twig`: SEO head partial, manifest link, theme-color, fonts preload, Turbo meta, skip-link, `lang` + `dir`.
- [ ] Error pages 404 / 500 / offline in house style.

### 1.5 Block page builder (admin CRUD)

**Data model**

- `Page` — slug (per locale, unique), title (translatable), status (`draft|published`), publishedAt, template (`default|landing`), seoTitle, seoDescription, ogImage, noIndex, canonicalOverride, sortOrder, isSystem (home/about/studio/signup cannot be deleted, only edited).
- `PageBlock` — page, type (string, maps to registry), position, data (json, per-locale keys `{nl:{…},en:{…}}`), isVisible, anchorId.
- `MediaFile` — path, originalName, mime, width, height, alt (translatable), focalPoint?, consentConfirmed. Used by blocks, trainers, gallery.

**Block registry** (`src/PageBuilder/Block/*`): one class per type implementing `BlockTypeInterface` (`getType()`, `getLabel()`, `buildForm()`, `getComponentName()`, `getDefaults()`, `validate()`). Registry is a tagged-service collection; adding a block = one PHP class + one form + one Twig Component.

**Block catalogue for Phase 1** (derived from `reference-html/`):

| Block type | Twig Component | Fields |
|---|---|---|
| `hero` | `Block:Hero` | label, heading, intro, primary CTA (text+link), secondary CTA, alignment |
| `slider` | `Block:Slider` | images[] (media + caption), autoplay bool, caption text |
| `story` | `Block:Story` (2 col) | heading, paragraphs (rich text, restricted marks), link text + target |
| `offer_grid` | `Block:OfferGrid` | cells[] (image, heading, text, price line from `SiteSetting` key or free text) |
| `trainers` | `Block:Trainers` | heading, intro, source = all public `Trainer`s or hand-picked, variant `cards\|profiles` |
| `testimonials` | `Block:Testimonials` | heading label, source = public `Testimonial`s, limit |
| `cta_frame` | `Block:CtaFrame` | heading, button text + link |
| `founder` | `Block:Founder` | portrait (media), heading, story, signature line |
| `numbered_principles` | `Block:Principles` | items[] (number auto, heading, text), dark background bool |
| `gallery` | `Block:Gallery` | heading, filter chips on/off, categories to show, lightbox on/off |
| `practical_strip` | `Block:PracticalStrip` | 3 cells each (label, heading, body, optional CTA); defaults from `SiteSetting` |
| `intake_form` | `Block:IntakeForm` | intro heading, steps[] (3), image; embeds the `IntakeRequest` form |
| `rich_text` | `Block:RichText` | heading?, body (for Privacy / Voorwaarden pages) |
| `spacer_divider` | `Block:Divider` | size, hairline bool |

**Admin UX**

- [ ] `/admin/paginas` list (title, slug, status, updated, actions); create / edit / duplicate / publish / unpublish / delete (non-system).
- [ ] Page editor = **Live Component** `Admin:PageEditor`: left column ordered block list (drag-sort via `sortable_controller`, add-block palette, duplicate, hide, remove), right column the block's form (per-locale tabs nl/en), top bar: title, slug, status, "Bekijk preview", "Opslaan", "Publiceren".
- [ ] Preview route `/admin/paginas/{id}/preview` renders draft with the real public layout (signed URL, noindex).
- [ ] Revision snapshot on every publish (`PageRevision`, json dump; restore button). Keep last 20.
- [ ] Media library `/admin/media`: upload (drag-drop, `media_upload_controller`), alt per locale, consent checkbox, resize presets, picker component reused inside block forms.
- [ ] Validation: every block type validates its data; publishing with errors is blocked with inline messages.
- [ ] Public rendering: `PageController::show` resolves slug + locale → `Page` → iterates `PageBlock`s → `<twig:Block:{Type} :data="…" />`. Full-page HTTP cache (`Cache-Control` + ETag) invalidated on publish.

### 1.6 Content CRUD needed by blocks

- [ ] **Trainers** `/admin/trainers`: displayName, specialisation (nl/en), workingDays[], bio (nl/en), quote, photo (media), sortOrder, isPublic. (Linked `User` optional in Phase 1.)
- [ ] **Testimonials** `/admin/ervaringen`: name, quote (nl/en), memberSince (month), isPublic, sortOrder. Seeded empty; fixtures use clearly fake `[PLACEHOLDER]` text.
- [ ] **Gallery** `/admin/galerij`: `GalleryImage` (media, category `room|session|people|one_on_one`, span `1x1|2x1|1x2`, sortOrder, consentConfirmed). Only consented images render publicly.
- [ ] **Site settings** `/admin/instellingen`: grouped form (Studio: stad, adres, telefoon, e-mail, Instagram, openingstijden · Prijzen: "vanaf" small group, 1-op-1 · Juridisch: KvK, btw-nr · Navigatie: header links · SEO: default title suffix, default OG image, GA/Plausible id optional).

### 1.7 Pages to build (content, via the builder)

Each page is seeded as a fixture of blocks so a fresh install already matches the reference HTML. Copy comes verbatim from HANDOFF §4 and `reference-html/`.

1. **Homepage `/`** — hero · slider · story · offer_grid · trainers (dark) · testimonials · cta_frame. Reference: `home.html`, `mobile-home.html`.
2. **Over ons `/over-ons`** — hero (2 col variant) · founder · numbered_principles (dark) · trainers (profiles variant) · cta_frame. Reference: `over-ons.html`.
3. **De studio `/de-studio`** — hero · slider · gallery (chips + lightbox) · practical_strip. Reference: `de-studio.html`.
4. **Aanmelden `/aanmelden`** — intake_form block; thank-you page `/aanmelden/bedankt` (rich_text). Reference: `aanmelden.html`, `mobile-aanmelden.html`. Mobile: 1-2-3 progress indicator + fixed submit bar.
5. **Privacy `/privacy`**, **Voorwaarden `/voorwaarden`** — rich_text with `[PLACEHOLDER]` text and TODO.
6. English equivalents under `/en/`: `/en/about`, `/en/the-studio`, `/en/sign-up`, `/en/privacy`, `/en/terms`.

### 1.8 Intake form (the only transactional feature in Phase 1)

- [ ] `IntakeRequest` entity exactly as HANDOFF §7; Symfony Form with the three sections; chips, availability grid, radios as real inputs.
- [ ] Honeypot field + rate limiter (`symfony/rate-limiter`, 5/h per IP), no captcha.
- [ ] Mails via Messenger: confirmation to requester (nl/en per form locale) + notification to admin address from `SiteSetting`. Twig mail layout in house style, `templates/email/`.
- [ ] Admin inbox `/admin/aanvragen`: list with status filter `new|to_call|scheduled|converted|rejected`, detail with notes and status change. (Conversion to member is Phase 2.)
- [ ] Tests: form validation, honeypot, rate limit, mail dispatched, Panther happy path desktop + mobile.

### 1.9 SEO

- [ ] `Seo:Head` component: title (page seoTitle | title + suffix), description, canonical, `hreflang` nl/en/x-default, OG + Twitter cards, `robots` (noindex for drafts/preview/admin).
- [ ] JSON-LD: `HealthClub`/`LocalBusiness` on home (name, address, phone, opening hours from settings), `Person` per trainer on Over ons, `BreadcrumbList`.
- [ ] `sitemap.xml` (published pages × locales, lastmod) and `robots.txt` (disallow `/admin`, `/mijn`), both as controllers.
- [ ] Image hygiene: `alt` required on media, `width`/`height` attributes, lazy loading below the fold, WebP variants.
- [ ] Redirects: admin-managed `Redirect` entity (from → to, 301/302) for slug changes; slug change auto-creates a redirect.
- [ ] Lighthouse CI step in `make test` (optional, non-blocking) with thresholds above.

### 1.10 PWA

- [ ] `manifest.webmanifest` served by a controller (name "Gym", short_name, start_url `/`, display `standalone`, background `#121214`, theme `#121214`, icons 192/512 + maskable, `lang` per locale).
- [ ] Service worker `assets/sw.js` (served from root scope via a route): precache app shell (CSS, JS, fonts, logo, offline page); stale-while-revalidate for marketing pages and images; network-only for `/admin`, `/mijn`, POSTs; versioned cache name from asset manifest hash; skipWaiting + clients.claim with a "nieuwe versie" toast.
- [ ] Offline fallback page `/offline` in house style with address + phone.
- [ ] `pwa_controller.js` Stimulus: registers SW, captures `beforeinstallprompt`, shows a dismissible install hint (settable per visitor in localStorage), handles update toast.
- [ ] iOS meta (`apple-touch-icon`, `apple-mobile-web-app-*`), `theme-color`, splash colours.
- [ ] Turbo compatibility checked (SW does not break Turbo drive/frames).
- [ ] Panther test: manifest reachable, SW registers, offline page renders.

### 1.11 Stimulus controllers

**Required for Phase 1** (functional, from HANDOFF §6):

| Controller | Used by |
|---|---|
| `slider_controller.js` | `Block:Slider`, hero slider, testimonials |
| `sticky_cta_controller.js` | `Site:StickyCta` |
| `mobile_menu_controller.js` | `Site:MobileMenu` |
| `chip_group_controller.js` | intake form, gallery filter |
| `availability_grid_controller.js` | intake form |
| `lightbox_controller.js` | `Block:Gallery` |
| `form_progress_controller.js` | mobile intake form |
| `gallery_filter_controller.js` | `Block:Gallery` chips → cells |
| `pwa_controller.js` | base layout |
| `sortable_controller.js` | admin page editor (block ordering) |
| `media_upload_controller.js` | admin media library |
| `confirm_controller.js` | admin delete/unpublish |
| `autosave_controller.js` | admin page editor draft save |

**Optional animation controllers — owner decides** (none are needed for the reference design; all respect `prefers-reduced-motion`):

| Controller | Effect | Suggested default |
|---|---|---|
| `reveal_controller.js` | fade/translate-up of sections on scroll (IntersectionObserver) | yes, subtle (12 px, 250 ms) |
| `counter_controller.js` | counts up the 01/02/03 numbers or stats | no |
| `parallax_controller.js` | slow vertical offset on hero/slider images | no (fights the "no decor" rule) |
| `marquee_controller.js` | horizontal running strip of brand phrases ("Geen pasje. Een afspraak.") | maybe |
| `header_shrink_controller.js` | header compacts after scroll, hairline appears | yes |
| `cursor_controller.js` | custom amber cursor dot on desktop | no |
| `text_split_controller.js` | per-line reveal of the H1 | maybe |
| `smooth_scroll_controller.js` | eased anchor scrolling for nav links | yes (CSS `scroll-behavior` first) |
| `image_hover_controller.js` | slight scale on offer/trainer images | no |

Decision pending: tick the ones to build; the rest are dropped from Phase 1.

### 1.12 Phase 1 build order

1. Foundation (1.1) → 2. Security + admin shell (1.2) → 3. `Ui:*` + chrome (1.3, 1.4) → 4. Page builder core + media (1.5) → 5. Content CRUDs (1.6) → 6. Blocks + the four pages (1.7) → 7. Intake form + mails (1.8) → 8. SEO (1.9) → 9. PWA (1.10) → 10. Optional animations (1.11) → 11. EN translations, Lighthouse, Panther, fixtures, `make test` green.

Each step ends with migrations, fixtures and tests passing.

---

## Phase 2+ (outline only, to be planned after Phase 1)

- **Phase 2 — Scheduling core:** Trainer↔User link, `SessionType`, `SlotTemplate`, `Session` generation command + cron, admin rooster week grid.
- **Phase 3 — Member portal booking:** Live Components `Portal:RecurringWizard`, `Portal:DayBooking`; cancel/credits/waitlist domain services with unit tests; booking mails.
- **Phase 4 — Admin members & intake conversion:** member profiles, "Lid maken" from `IntakeRequest`, invitation mail, injury notes with trainer-only visibility.
- **Phase 5 — Subscriptions & invoicing:** `MembershipType`, `Subscription`, monthly `Invoice` generation, PDF (`GYM-YYYY-NNNN`), manual paid status, `Payment` modelled for Mollie later.
- **Phase 6 — Finishing:** group messages, progress page, full EN, Panther booking tests, Lighthouse ≥ 90 across portal.
- **Administrative admin (cross-cutting, planned with Phase 2):** user & role management, audit log, settings for business rules (capacity, 24 h window, credit expiry, waitlist offer window), exports.

---

## Open questions for the owner

1. Which optional animation controllers from 1.11 should be built?
2. Rich text in blocks: minimal (bold/italic/link/list) via a small editor, or plain textarea with paragraphs? Proposal: minimal editor, no new JS framework dependency.
3. Image processing: LiipImagineBundle (adds a dependency) or a plain GD/Imagick service? Proposal: plain service, WebP + 3 widths.
4. Analytics: none, Plausible, or GA4? Affects consent banner (none needed for Plausible).
5. Who is the admin notification recipient for intake requests (a `SiteSetting`, needs a real address before launch)?
