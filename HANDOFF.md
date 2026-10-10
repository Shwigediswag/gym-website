# Gym — Handoff voor Claude Code

> Dit document is de bron van waarheid voor de bouw van de Gym-website en het ledenportaal.
> Lees het volledig voordat je code schrijft. De visuele referentie is de Design-canvas
> "Gym — design E (Amber)" in Claude (8 artboards: Homepage, Boeken, Over ons, De studio,
> Aanmelden, en drie mobiele schermen). Exporteer die artboards als PNG en leg ze in `docs/design/`.
> Tokens staan in `design-tokens.css` naast dit bestand. In `reference-html/` staan statische HTML-exports van
> alle acht schermen (open `reference-html/index.html`); gebruik die als pixel-referentie voor opmaak, copy en
> volgorde van secties. De inline styles daar zijn bewust: vertaal ze naar Tailwind-tokens en Twig Components.

---

## 1. Context

**Wat is Gym?** Een nieuwe kleine trainingsstudio in [STAD]. Mensen trainen **op afspraak** in
small groups (max. [6]) met een personal trainer, of **1-op-1**. Er is geen vrije inloop. Leden
boeken losse sessies of leggen **vaste tijden** vast (bijv. elke maandag en donderdag 18:00,
tot 31 december). De branding draait om de persoonlijke benadering: *je kent je trainer en je
mede-trainees*.

**Merkzinnen (gebruik letterlijk):**
- Slogan: **„Op afspraak. Op naam.”**
- Over-ons-kop: **„Je trainer kent je, en je rechterknie.”**
- Terugkerend: „Geen pasje. Een afspraak.” · „Je trainer kent je naam.” · „Kom eerst een kop koffie drinken.”

**Toon:** warm, direct, informeel (je/jij), korte zinnen, geen superlatieven, geen sales-druk,
nooit "Welkom op onze website". Nederlands is de primaire taal, Engels de tweede.

**Wat het systeem is:** één Symfony-applicatie met drie gezichten:
1. **Marketing-site** (publiek): Homepage, Over ons, De studio, Aanmelden (gratis kennismaking).
2. **Ledenportaal** (ingelogd lid): boeken (vaste tijden / losse dag / 1-op-1), mijn sessies, mijn groep, profiel.
3. **Admin** (trainer/eigenaar): rooster & sessies, leden & groepen, kennismaking-aanvragen, abonnementen & facturatie.

---

## 2. Technische kaders

| Onderdeel | Keuze |
|---|---|
| Framework | Symfony 7.x, PHP 8.3 |
| Database | MySQL 8 / MariaDB 10.11+, Doctrine ORM + Migrations |
| Templates | **Twig + Symfony UX Twig Components** (`symfony/ux-twig-component`) voor component-based templates; **Live Components** waar server-state nodig is. (Blade is Laravel-only; Twig Components is het Symfony-equivalent — anonymous components in `templates/components/`.) |
| Interactie | **Stimulus** via Symfony UX (`symfony/stimulus-bundle`), geen andere JS-frameworks |
| CSS | **Tailwind CSS** (v4, CSS-first config via `@theme`; zie `design-tokens.css`). Via `symfonycasts/tailwind-bundle` + AssetMapper. |
| Auth | Symfony Security, e-mail + wachtwoord, rollen `ROLE_MEMBER`, `ROLE_TRAINER`, `ROLE_ADMIN`; wachtwoord-reset per e-mail |
| Mail | Symfony Mailer + Messenger (async), Twig-mailtemplates in dezelfde huisstijl |
| i18n | Symfony Translation, `nl` (default) en `en`; locale in URL-prefix (`/`, `/en/`); alle UI-strings via `trans` |
| Betalingen | **Nog niet integreren.** Wel modelleren: abonnementstypen, facturen, betaalstatus. Provider (waarschijnlijk Mollie) komt in fase 2; houd `Payment` als eigen entiteit met `provider`/`providerReference` velden. |
| Testen | PHPUnit + Panther/Browser tests voor de boekingsflows; fixtures via Foundry |
| Dev | Docker Compose (php-fpm, nginx, mysql, mailpit), `make` targets |

### Mappenstructuur (voorstel)
```
src/
  Booking/          # domein: Session, Booking, RecurringBooking, Waitlist, rules
  Membership/       # Member, MembershipType, Subscription, Invoice, Payment
  Intake/           # IntakeRequest (kennismaking), questionnaire
  Scheduling/       # Trainer, SlotTemplate -> Session generation
  Admin/            # controllers + Live Components voor admin
  Portal/           # controllers voor ledenportaal
  Marketing/        # publieke pagina's
templates/
  components/       # Twig Components (zie §5)
  marketing/  portal/  admin/  email/
assets/
  controllers/      # Stimulus controllers (zie §6)
  styles/app.css    # importeert design-tokens.css
translations/       # messages.nl.yaml, messages.en.yaml, validators.*
docs/design/        # PNG-exports van de canvas
```

---

## 3. Visuele taal (design E · Amber)

Alle waarden staan als tokens in `design-tokens.css`. Samenvatting:

- **Thema:** donker. Grond `#121214`, verhoogd vlak `#17171A`, diepere sectie `#0C0C0E`, placeholder-vlak `#1F1F23`.
- **Lijnen:** 1 px hairlines `#26262B` (secties, kaarten als 1px-grid met `gap:1px` op een `#26262B`-achtergrond), controls `#3A3A41`.
- **Tekst:** primair `#F4F4F2`, secundair `#C9C9CF`, gedempt `#8E8E96` (alleen voor labels/meta ≥ 12 px).
- **Accent:** amber `#F2A33A`. Gebruik: primaire knoppen (tekst `#121214` erop), actieve tabs, uppercase-labels, geselecteerde chips, kaders om "jouw" items, de logo-punt. Nooit als tekst op donker voor lopende tekst (alleen labels/links).
- **Vorm:** **geen afgeronde hoeken** (0 px) behalve avatars (cirkel). Geen schaduwen, behalve de uitstekende CTA (`0 8px 30px rgba(0,0,0,.45)`). Geen gradients als decor.
- **Typografie:**
  - Display: **Cormorant Garamond 600** (Google Fonts), rechtop, geen italic. H1 84–88 px desktop / 44–54 px mobiel, line-height 1.0; H2 56 px; kaartkoppen 36 px; cijfers (01/02/03) 48 px in accent.
  - Body/UI: **Barlow 400/500/600**. Body 17–20 px (lh 1.55–1.7), UI 14–15 px, labels 12 px uppercase, letter-spacing 0.18em, 600.
  - Logo: woordmerk "Gym" in Cormorant 600 + een amber vierkantje van 9×9 px op de baseline erachter. Geen beeldmerk.
- **Spacing:** desktop zijmarge 56 px, secties 96–128 px verticaal; mobiel zijmarge 20 px, secties 48–64 px. Grids met `gap`, nooit margins tussen siblings.
- **Fotografie:** warm, dichtbij, mensen in beeld (trainer + lid), nooit stock-fitness. Tot er foto's zijn: placeholder-vlak met diagonale hairline-arcering en een uppercase-bijschrift linksonder (component `ImagePlaceholder`).
- **Iconen:** inline stroke-SVG (2–2.4 px), geen icon-font, geen emoji.
- **Toegankelijkheid:** contrast ≥ 4.5:1 (gedempt grijs alleen op 12 px+ labels), touch targets ≥ 44 px, echte `<button>/<a>/<input>+<label>`, `aria-label` op icon-knoppen, focus-ring 2 px amber.

### Vaste UI-patronen
- **Sticky CTA „Gratis kennismaking”**: desktop een amber tab die aan de rechter viewport-rand uitsteekt (pijl-icoon + tekst), verschijnt na ~400 px scroll; mobiel een amber balk onderaan over de volle breedte. Linkt naar `/aanmelden`.
- **Slider**: horizontale strip met kaarten (60%/40% breedtes desktop, 300 px mobiel), onderschrift links in Cormorant ("Maandag 18:00 — Sam, Lotte, Rik, Maud, Tom en jij."), rechts streepjes-indicator + vierkante ‹ › knoppen van 44 px. Swipe + knoppen, geen autoplay-verplichting (optioneel 6 s).
- **Segmented control**: knoppen in één 1px-rand, actieve knop amber met donkere tekst.
- **Chips (pill)**: 1px-rand, 44 px hoog, geselecteerd = amber vlak.
- **Kaartengrid**: cellen op een `#26262B` achtergrond met `gap:1px` zodat er hairlines tussen ontstaan.

---

## 4. Pagina's & schermen

Bouw desktop fluid (max-width container, zijmarge 56 px) en mobiel ≥ 390 px. Alle teksten via translation keys; onderstaande NL-copy is de default.

### 4.1 Homepage `/`
1. **Header**: links nav (Ons verhaal · Trainen · Trainers · Ervaringen), midden logo, rechts `Inloggen` (outline) + `Gratis kennismaking` (amber). Mobiel: logo, Inloggen, hamburger (44 px) → full-screen menu.
2. **Hero** (gecentreerd): label „SMALL GROUP & PERSONAL TRAINING · [STAD]”, H1 „Op afspraak. Op naam.”, intro „Gym is een kleine trainingsstudio. Je boekt een vast uur, traint in een groep van maximaal [6] en hebt één trainer die je echt leert kennen. Of 1-op-1, als je dat liever hebt.”, knoppen „Plan je gratis kennismaking” (amber) + „Bekijk het aanbod” (outline).
3. **Hero-slider** (3+ foto's) met onderschrift en indicator.
4. **Verhaal** (2 kolommen): H2 „Geen pasje. Een afspraak.” + twee alinea's (zie canvas) + link „LEES ONS VERHAAL →”.
5. **Aanbod** (3 cellen in hairline-grid): Small group (vanaf [PRIJS]/maand) · 1-op-1 (vanaf [PRIJS]/sessie) · Vaste tijden (inbegrepen). Elk met foto, Cormorant-kop, tekst, amber prijsregel.
6. **Trainers** (donkerder sectie `#0C0C0E`): H2 „Maak kennis met je trainers”, 3 portretten met naam en specialisatie/dagen. Data uit `Trainer`-entiteit.
7. **Ervaringen**: label, één groot citaat in Cormorant 44 px, naam + „lid sinds [maand]”, indicator (slider van testimonials; data uit admin, geen verzonnen quotes).
8. **CTA-kader** (1px amber rand): „Kom eerst een kop koffie drinken.” + „Gratis kennismaking plannen”.
9. **Footer**: adres · telefoon · Instagram · Privacy · Voorwaarden · taalwissel NL/EN.

### 4.2 Over ons `/over-ons`
Hero H1 „Je trainer kent je, en je rechterknie.” met intro; oprichter-blok (portret + verhaal + handtekeningregel „— [Naam oprichter]”); „Drie dingen die we nooit loslaten” (01 Op afspraak, nooit op inloop · 02 Maximaal [6], altijd dezelfde · 03 Eén trainer, voor de lange termijn); trainerprofielen (portret, specialisatie + dagen, bio, citaat) uit `Trainer`; CTA-kader „Kom kennismaken. Met ons, en met je groep.”

### 4.3 De studio `/de-studio`
Hero „Kijk even binnen.”; grote slider; „Alle foto's” met filterchips (Alles · De zaal · Een sessie · De mensen · 1-op-1) en een grid met gemengde celgroottes (span 2 rows/cols), lightbox bij klik; praktische strip (Adres · Trainingstijden · „Zelf komen kijken?” met CTA). Foto's en categorieën beheerbaar in admin (`GalleryImage`).

### 4.4 Aanmelden `/aanmelden` (gratis kennismaking)
Twee kolommen: links intro + 3 stappen (formulier → wij bellen binnen één werkdag → een uur meekijken) + foto; rechts het formulier:
1. **Jouw gegevens**: voornaam, achternaam, e-mail, telefoon.
2. **Wat wil je bereiken?**: multi-select chips (Sterker worden · Betere conditie · Afvallen · Blessurevrij bewegen · Meer energie in de week · Gewoon weer in een ritme komen); radio „Hoe vaak train je nu?” (Zelden of nooit · Af en toe · Regelmatig); textarea „Iets wat je trainer moet weten? (blessures, je rechterknie, een operatie)”.
3. **Wanneer kun je?**: beschikbaarheidsgrid dagen (ma–za) × dagdelen (ochtend/middag/avond) als checkboxes; radio voorkeur (Small group · 1-op-1 · Weet ik nog niet); tekstveld „Wanneer kunnen we je het beste bellen?”.
4. Privacy-checkbox + knop „Verstuur en plan mijn kennismaking”.
Mobiel: één lange scroll met 1-2-3 voortgangsindicator bovenaan en een vaste submit-balk onderaan.
Na versturen: bedankpagina `/aanmelden/bedankt` („We bellen je binnen één werkdag”), e-mail naar aanvrager (bevestiging) en naar admin (nieuwe aanvraag). Opslaan als `IntakeRequest`. Spam: honeypot + rate limit, geen captcha.

### 4.5 Ledenportaal (ingelogd) `/mijn`
Layout: desktop linker sidebar (logo; Boeken · Mijn sessies · Mijn groep · Voortgang · Profiel; onderaan avatar + naam + „groep van [trainer]”), mobiel bottom-tabbar (Boeken · Sessies · Groep · Profiel) en header met „Hoi, [Voornaam]”.

**Boeken `/mijn/boeken`** — H1 „Boeken”, segmented control **Vaste tijden · Losse dag · 1-op-1**.
- *Vaste tijden* (kaart op `#17171A`): stap 1 kies dagen (chips ma–za, multi), stap 2 kies uur (chips per beschikbare slot incl. trainer; „vol” gedimd), stap 3 voor hoe lang (4 weken · Tot [einde kwartaal/jaar] · Tot ik opzeg), samenvattingsregel („Ma + Do · 18:00 · groep van Sam · 22 sessies · t/m 31 dec · [PRIJS]/maand”) en knop „Vaste tijden bevestigen”. Bestaande vaste tijden worden bovenin getoond met „Aanpassen” / „Verlengen”.
- *Losse dag*: weekstrip met dagknoppen (stip op dagen met eigen sessie), lijst van sessies van die dag (tijd, type, trainer, bezetting x van 6, avatars van wie er al zijn) met „Boek deze plek”; vol → „Wachtlijst”; 1-op-1-slots met amber knop „Boek 1-op-1”.
- *1-op-1*: zelfde dagstrip, alleen vrije 1-op-1-slots van (standaard) de eigen trainer; andere trainer kiesbaar.
- Rechterkolom: **Jouw groep** (trainer-kaart + avatars/namen van mensen die hetzelfde vaste uur hebben + „Bericht aan de groep →”) en **Komende sessies** (lijst met „Afmelden”; voetnoot „Afmelden kan tot 24 uur van tevoren”).

**Mijn sessies `/mijn/sessies`**: komende en afgelopen sessies, inhaal-credits saldo, afmeldknop.
**Mijn groep `/mijn/groep`**: per vast uur de vaste medeleden + trainer; eenvoudige groepsberichten (trainer → groep; leden kunnen reageren) — mag in fase 2.
**Voortgang `/mijn/voortgang`**: fase 2 (notities van trainer, meetmomenten). Toon nu een lege staat.
**Profiel `/mijn/profiel`**: gegevens, wachtwoord, intake-antwoorden/blessures (bewerkbaar), abonnement + facturen (alleen inzien), taal.

### 4.6 Admin `/admin` (ROLE_TRAINER / ROLE_ADMIN)
Zelfde huisstijl, dichter opgezet (14 px UI). Overweeg EasyAdmin **niet**; bouw met Twig/Live Components zodat het één taal spreekt met het portaal.
- **Rooster** `/admin/rooster`: weekraster trainer × tijd; sessies aanmaken uit `SlotTemplate`s (bijv. „ma 18:00, Sam, small group, cap 6, elke week”) → sessies gegenereerd 12 weken vooruit (console command + cron). Per sessie: deelnemers, wachtlijst, annuleren (met mail naar deelnemers), eenmalig verplaatsen.
- **Leden** `/admin/leden`: lijst/zoek; profiel met intake-antwoorden, blessurenotities (alleen trainers zichtbaar), vaste tijden, boekingen, credits, abonnement, facturen; lid aanmaken vanuit een `IntakeRequest` („Lid maken” → uitnodigingsmail met wachtwoord-instellen).
- **Kennismaking-aanvragen** `/admin/aanvragen`: inbox met status **Nieuw → Te bellen → Gepland (datum) → Lid geworden / Afgewezen**; notities; één klik „Plan kennismaking” maakt een `IntroSession`-afspraak.
- **Abonnementen & facturatie** `/admin/facturatie`: `MembershipType`s beheren (naam, prijs/maand, sessies/week, 1-op-1 tarief, bundelgroottes), per lid een `Subscription`, maandelijkse `Invoice`-generatie (command), factuur-PDF (huisstijl, nummering `GYM-2026-0001`), betaalstatus handmatig op „betaald” zetten. **Geen betaalprovider in fase 1.**
- **Content**: trainers (naam, specialisatie, dagen, bio, citaat, foto), testimonials, galerij-foto's met categorie, prijzen die op de site staan, adres/openingstijden. Simpele CRUD.

---

## 5. Twig Components (templates/components/)

Maak anonymous components; props via `{% props %}`. Naamgeving `Ui:*` voor primitieven, `Site:*`, `Portal:*`, `Admin:*` voor samengestelde.

| Component | Props (indicatief) | Opmerking |
|---|---|---|
| `Ui:Button` | `variant: primary\|outline\|ghost`, `size`, `href?`, `type` | amber / 1px rand / tekst; 0 radius; 44 px min |
| `Ui:Label` | – | 12 px uppercase 0.18em, kleur via `class` (default amber) |
| `Ui:Heading` | `level`, `size: display\|xl\|lg\|md` | Cormorant 600 |
| `Ui:Logo` | `size` | woordmerk + amber vierkant |
| `Ui:HairlineGrid` | `cols` | wrapper met `bg-line gap-px border` |
| `Ui:Chip` | `selected`, `disabled`, `name`, `value`, `type: checkbox\|radio\|button` | |
| `Ui:Segmented` | `options`, `active`, `name` | |
| `Ui:Avatar` / `Ui:AvatarStack` | `initials`, `src?`, `size` | overlap −8 px |
| `Ui:ImagePlaceholder` | `label`, `ratio?` | arcering + bijschrift |
| `Ui:Input`, `Ui:Textarea`, `Ui:Field` | form-theme wrappers | 48 px hoog, focus-ring amber |
| `Ui:Icon` | `name` | inline SVG sprite |
| `Site:Header`, `Site:Footer`, `Site:MobileMenu` | | |
| `Site:StickyCta` | `href` | desktop rechts-tab / mobiel onderbalk |
| `Site:Slider` | `caption`, slot met `Site:Slide` | Stimulus `slider` |
| `Site:OfferCard`, `Site:TrainerCard`, `Site:Testimonial`, `Site:CtaFrame`, `Site:Steps` | | |
| `Site:GalleryGrid`, `Site:GalleryFilter`, `Site:Lightbox` | | |
| `Portal:Shell` (sidebar/tabbar), `Portal:SessionCard`, `Portal:DayStrip`, `Portal:GroupCard`, `Portal:UpcomingList` | | |
| `Portal:RecurringWizard` | **Live Component** | stappen dagen → uur → duur, server-side beschikbaarheid |
| `Portal:DayBooking` | **Live Component** | dagstrip + sessies, boeken/wachtlijst zonder reload |
| `Admin:*` | | tabel, filterbalk, statusbadge, weekraster |

Formulieren: eigen Twig **form theme** (`templates/form/gym_theme.html.twig`) zodat Symfony Forms dezelfde `Ui:*`-opmaak krijgen.

---

## 6. Stimulus controllers (assets/controllers/)

- `slider_controller.js` — scroll-snap strip, ‹ › knoppen, indicator, swipe, optioneel autoplay.
- `sticky_cta_controller.js` — toont de uitstekende CTA na scroll-drempel; verbergt op `/aanmelden`.
- `mobile_menu_controller.js` — open/dicht, focus-trap, `Escape`.
- `segmented_controller.js` — tabs wisselen (met `aria-selected`), URL-hash sync.
- `chip_group_controller.js` — visuele selectie van checkbox/radio-chips (echte inputs blijven de bron).
- `availability_grid_controller.js` — dag × dagdeel grid op aanmeldpagina (toggle, hele rij/kolom).
- `day_strip_controller.js` — dagkeuze → laadt sessies (Turbo frame of Live Component).
- `lightbox_controller.js` — galerij, pijltjestoetsen, sluiten.
- `confirm_controller.js` — bevestiging bij afmelden/annuleren (eigen modal, geen `window.confirm`).
- `form_progress_controller.js` — 1-2-3 indicator op mobiel aanmeldformulier volgt de scrollpositie.

Gebruik Turbo (`symfony/ux-turbo`) voor navigatie; Live Components voor de boekingsflows.

---

## 7. Datamodel (Doctrine)

Alle entiteiten `id` (ULID), `createdAt`, `updatedAt`. Namen in het Engels, labels in NL.

- **User** — email (uniek), password, roles[], locale (`nl|en`), isActive. 1:1 met `Member` of `Trainer`.
- **Trainer** — user, displayName, specialisation (translatable), workingDays[], bio, quote, photo, sortOrder, isPublic.
- **Member** — user, firstName, lastName, phone, dateOfBirth?, notes (trainer-only), injuries (vrije tekst, trainer-only), goals[] (enum), experienceLevel (enum), preferredTrainer?, makeUpCredits (int), source (`intake|admin`), joinedAt.
- **IntakeRequest** — firstName, lastName, email, phone, goals[], experienceLevel, injuriesNote, availability (json: `{ma:[ochtend,avond],…}`), preferredFormat (`group|one_on_one|unknown`), callPreference, consentAt, status (`new|to_call|scheduled|converted|rejected`), scheduledAt?, assignedTrainer?, internalNotes, convertedMember?.
- **SessionType** — enum/entiteit: `small_group` (capacity default 6, 60 min), `one_on_one` (capacity 1), `intro` (kennismaking, capacity 1, 30 min, geen boeking door lid).
- **SlotTemplate** — trainer, sessionType, weekday, startTime, durationMinutes, capacity, validFrom, validUntil?, isActive. Bron voor generatie.
- **Session** — slotTemplate?, trainer, sessionType, startsAt, endsAt, capacity, status (`scheduled|cancelled|completed`), cancelReason?. Uniek op (trainer, startsAt).
- **Booking** — session, member, kind (`recurring|single|one_on_one|make_up`), status (`booked|cancelled|attended|no_show`), recurringBooking?, cancelledAt?, creditIssued (bool), createdBy (`member|admin`). Uniek op (session, member) voor status `booked`.
- **RecurringBooking** — member, slotTemplate, startsOn, endsOn? (null = tot opzeg), status (`active|ended`). Bij aanmaken en bij elke nieuwe sessiegeneratie worden `Booking`s (kind `recurring`) aangemaakt zolang capaciteit het toelaat; conflicten → wachtlijst + melding.
- **WaitlistEntry** — session, member, position, status (`waiting|offered|accepted|expired|cancelled`), offeredAt?, expiresAt?.
- **GroupMessage** — session-reeks (slotTemplate) of session, author (user), body, createdAt. (fase 2 mogelijk)
- **MembershipType** — name, description, pricePerMonth (int, cents), sessionsPerWeek?, oneOnOnePrice (cents), bundleSizes[] (bijv. 5/10), isActive, isPublic (voor „vanaf”-prijs op site).
- **Subscription** — member, membershipType, startsOn, endsOn?, status (`active|paused|ended`), billingDay.
- **Invoice** — member, number (`GYM-YYYY-NNNN`), periodStart, periodEnd, lines (json of `InvoiceLine`), totalCents, vatCents, status (`draft|sent|paid|overdue|void`), issuedAt, dueAt, paidAt?, pdfPath.
- **Payment** — invoice, amountCents, method (`manual|provider`), provider? (`mollie`), providerReference?, paidAt. *Fase 1 alleen `manual`.*
- **Testimonial** — member?/name, quote, memberSince, isPublic, sortOrder.
- **GalleryImage** — path, alt (translatable), category (`room|session|people|one_on_one`), span (`1x1|2x1|1x2`), sortOrder, consentConfirmed (bool).
- **SiteSetting** — key/value (adres, telefoon, openingstijden, „vanaf”-prijzen, stad), translatable waar nodig.

---

## 8. Bedrijfsregels (implementeer als domain services + tests)

1. **Capaciteit**: small group max. `capacity` (default [6]); 1-op-1 altijd 1. Boeken boven capaciteit → `WaitlistEntry`.
2. **Vaste tijden**: een `RecurringBooking` reserveert de plek in elke toekomstige `Session` van dat `SlotTemplate` tot `endsOn`. Sessies worden 12 weken vooruit gegenereerd (`app:sessions:generate`, dagelijks via cron); nieuwe sessies krijgen automatisch de recurring bookings. Verlengen kan vanaf 4 weken voor `endsOn` (banner in portaal).
3. **Losse dag**: elk lid kan een vrije plek in elke sessie boeken (geen vaste groepen in de data). „Jouw groep” = de andere leden met een actieve `RecurringBooking` op hetzelfde `SlotTemplate` + de trainer.
4. **Afmelden**: tot **24 uur** voor `startsAt` kosteloos via portaal. Resultaat: booking `cancelled`, lid krijgt **1 make-up credit** (`creditIssued=true`, `member.makeUpCredits++`), plek gaat naar de wachtlijst. Binnen 24 uur: alleen via admin; geen credit (admin kan handmatig alsnog toekennen). Credits verlopen na [90] dagen (configureerbaar).
5. **Inhaalles**: een losse-dag-boeking met kind `make_up` verbruikt 1 credit en wordt niet gefactureerd.
6. **Wachtlijst**: FIFO op `position`. Bij vrijkomen: eerste entry krijgt status `offered` + e-mail; **2 uur** om te accepteren (link in mail), daarna `expired` en door naar de volgende. Minder dan 6 uur voor aanvang: plek direct toewijzen aan de eerste op de lijst met notificatie (geen aanbodvenster).
7. **1-op-1**: boekbaar in vrije 1-op-1-slots van een trainer; afrekening per sessie of uit bundel (`Subscription`/`MembershipType`), in fase 1 alleen op de factuur gezet.
8. **Sessie annuleren (trainer)**: alle deelnemers krijgen e-mail + credit; recurring bookings blijven intact voor volgende weken.
9. **Facturatie**: maandelijks op `billingDay` (`app:invoices:generate`): abonnementsbedrag + losse 1-op-1's/losse dagen van de vorige maand die niet door abonnement/credit gedekt zijn. Status handmatig naar `paid`.
10. **Privacy**: blessurenotities en intake-opmerkingen alleen zichtbaar voor `ROLE_TRAINER`/`ROLE_ADMIN` en het lid zelf; nooit in groepsweergaven. Foto's in de galerij alleen met `consentConfirmed`.
11. **E-mails** (Twig, huisstijl, NL/EN per `user.locale`): bevestiging aanmelding (aanvrager + admin), uitnodiging account, boekingsbevestiging, herinnering 24 uur vooraf (ook de afmeld-deadline noemen), afmeldbevestiging + credit, wachtlijst-aanbod, sessie geannuleerd, factuur verstuurd, wachtwoord-reset.

---

## 9. Routes (samenvatting)

```
GET  /                      marketing_home
GET  /over-ons              marketing_about
GET  /de-studio             marketing_studio
GET|POST /aanmelden         intake_new        -> /aanmelden/bedankt
GET  /inloggen  POST        security_login     (+ wachtwoord-vergeten flow)
GET  /mijn                  portal_dashboard   (redirect naar /mijn/boeken)
GET  /mijn/boeken           portal_booking     (?tab=vast|los|een)
POST /mijn/boeken/vast      portal_recurring_create
POST /mijn/boeken/sessie/{id}  portal_booking_create   (losse dag / 1-op-1 / make-up)
POST /mijn/boeken/{id}/afmelden portal_booking_cancel
POST /mijn/wachtlijst/{id}  portal_waitlist_join / accept
GET  /mijn/sessies  /mijn/groep  /mijn/voortgang  /mijn/profiel
GET  /admin/...             rooster, sessies, leden, aanvragen, facturatie, content
GET  /wachtlijst/accepteren/{token}   waitlist_accept (vanuit e-mail)
```
Engels onder `/en/...` met vertaalde slugs (`/en/about`, `/en/the-studio`, `/en/sign-up`, `/en/my/...`).

---

## 10. Fasering (voorgestelde volgorde voor Claude Code)

1. **Fundament**: Symfony-skelet, Docker, Tailwind v4 + tokens, fonts, `Ui:*`-components, form theme, i18n-setup, Security (users/rollen), fixtures.
2. **Marketing-site**: 4 pagina's + sticky CTA + slider + aanmeldformulier → `IntakeRequest` + mails. (Hiermee kan de site live.)
3. **Scheduling-kern**: Trainer, SlotTemplate, Session-generatie, admin rooster.
4. **Portaal boeken**: Live Components voor vaste tijden, losse dag, 1-op-1; afmelden/credits/wachtlijst; e-mails.
5. **Admin leden & aanvragen**: inbox met statussen, lid maken uit aanvraag, ledenprofiel.
6. **Abonnementen & facturatie**: types, subscriptions, maandelijkse facturen, PDF, handmatige betaalstatus.
7. **Afwerking**: Mijn groep (berichten), profielpagina, EN-vertalingen compleet, Panther-tests op de boekingsflows, Lighthouse ≥ 90.

---

## 11. Open plekken (invullen door opdrachtgever)

`[STAD]`, `[Adres]`, `[Telefoon]`, `[PRIJS]` small group p/m, `[PRIJS]` 1-op-1 p/sessie, maximale groepsgrootte (`[6]`), trainersnamen/bio's/foto's, oprichtersverhaal, echte testimonials, openingstijden, credit-vervaltermijn (`[90]` dagen), btw-percentage voor facturen, KvK/btw-nummer in footer en factuur, privacyverklaring/voorwaarden. Laat deze in code als `SiteSetting`s of translation keys met duidelijke TODO's; verzin nooit waarden.
