
# Ontwikkelplan — website Spaans-kenteken-service

Dit document is bedoeld als startpunt voor Claude Code. Plaats het als `PLAN.md` of `CLAUDE.md` in de root van je project, zodat Claude Code er bij elke sessie naar kan refereren.

## Projectcontext

Een Nederlandstalige website voor een dienst die Nederlandse auto's op Spaans kenteken zet. Doelgroep: Nederlanders in regio Málaga. Bezoekers vullen hun kenteken in, krijgen direct hun voertuiggegevens te zien plus een indicatie van BPM-teruggave en kosten, en kunnen daarna een offerte aanvragen of een dossier aanmaken.

De site heeft twee lagen:

1. **Publieke marketingsite** (homepage, dienstenpagina's, blog, kostencalculator)
2. **Klantenportaal achter login** (dossier, documentupload, status, berichten)

## Techstack

- **Backend:** Laravel 11 (PHP 8.3+)
- **Frontend:** Vue 3 met Composition API en `<script setup>` syntax
- **Build tooling:** Vite (komt standaard mee met Laravel)
- **Integratie:** Inertia.js als brug tussen Laravel en Vue (geen aparte SPA, geen losse API routes voor frontend) — dit houdt routing, auth en validatie aan de Laravel-kant en geeft Vue-componenten als pages
- **Styling:** Tailwind CSS v4 met een custom configuratie voor de huisstijl
- **Database:** MySQL of PostgreSQL
- **Auth:** Laravel Breeze met de Vue + Inertia variant
- **Bestanden:** Laravel filesystem met S3-driver of lokaal met afgeschermde toegang
- **Queue:** Database queue voor mail en BPM-aanvragen

Waarom Inertia in plaats van een losse Vue SPA met Laravel API: scheelt enorm veel boilerplate, geen aparte auth-flow nodig, validatie blijft in Laravel form requests, en je houdt SEO via server-side rendering van de eerste payload.

## Huisstijl

### Kleurenpalet

Het palet combineert het vertrouwen van diepblauw met een warme Spaanse accentkleur en rustige neutralen.

| Rol | Naam | Hex | Gebruik |
|---|---|---|---|
| Primary | Diepblauw | `#1B2B4A` | Headers, primaire knoppen, navigatie |
| Primary licht | Mistblauw | `#4A6FA5` | Hover-states, secundaire elementen |
| Accent | Terracotta | `#C97B5C` | Call-to-actions, highlights, BPM-bedragen |
| Accent licht | Zandsteen | `#E8C9B8` | Achtergronden van blokken, badges |
| Neutraal achtergrond | Warm off-white | `#F7F4EE` | Pagina-achtergrond |
| Neutraal kaart | Wit | `#FFFFFF` | Cards, modals |
| Tekst donker | Antraciet | `#2A2D34` | Body tekst |
| Tekst licht | Grijsblauw | `#6B7280` | Secundaire tekst, captions |
| Succes | Zachtgroen | `#5D8B6E` | Bevestigingen, statusindicatoren |
| Waarschuwing | Oker | `#D4A24C` | Aandacht-states |
| Fout | Diep rood | `#B84545` | Foutmeldingen |

### Typografie

- **Display / koppen:** Fraunces (variable serif, weight 400-700) — geeft karakter en gravitas, past bij vertrouwen
- **Body / UI:** Inter (variable sans, weight 400-600) — modern, hyperleesbaar
- Beide via Google Fonts met `font-display: swap`

Hiërarchie:

| Element | Font | Weight | Grootte |
|---|---|---|---|
| H1 | Fraunces | 600 | 48-64px |
| H2 | Fraunces | 600 | 32-40px |
| H3 | Fraunces | 500 | 24-28px |
| Body | Inter | 400 | 16-18px |
| Klein | Inter | 400 | 14px |
| Knop | Inter | 600 | 16px |

### Visuele taal

- Veel witruimte
- Rustige micro-animaties (fade, subtle slide, geen bouncy effects)
- Border-radius `rounded-xl` (12px) voor cards en knoppen, `rounded-2xl` voor grote blokken
- Subtiele schaduwen (`shadow-sm` standaard, `shadow-lg` voor hover)
- Iconen via Heroicons (outline-stijl voor rustige uitstraling)
- Beelden: warme zonnige Spaanse foto's met blauwe luchten, geen stockfoto-clichés

## RDW API integratie

### Het endpoint

`https://opendata.rdw.nl/resource/m9d7-ebf2.json?kenteken={KENTEKEN_ZONDER_STREEPJES_HOOFDLETTERS}`

Geen API-key vereist voor lichte gebruikers, maar registreer een Socrata App Token op `https://evergreen.data.socrata.com/signup` zodra je serieus gaat — dit verhoogt de rate limit significant. Stuur de token mee als header `X-App-Token` of als query parameter `$$app_token`.

De respons is een JSON-array (meestal met één object). Belangrijke velden voor onze toepassing:

- `kenteken` — kenteken
- `voertuigsoort` — Personenauto / Bedrijfsauto / etc.
- `merk` — bv. VOLKSWAGEN
- `handelsbenaming` — bv. GOLF
- `inrichting` — carrosserie
- `eerste_kleur` — primaire kleur
- `aantal_zitplaatsen`
- `datum_eerste_toelating` — formaat YYYYMMDD, cruciaal voor BPM-berekening
- `datum_eerste_tenaamstelling_in_nederland`
- `vervaldatum_apk` — APK-vervaldatum
- `massa_ledig_voertuig` — leeggewicht
- `cilinderinhoud`
- `catalogusprijs` — basis voor BPM-berekening
- `wam_verzekerd` — verzekeringsstatus

### Aanvullende endpoints

Voor brandstof en CO2 (nodig voor BPM-berekening) heb je een tweede endpoint:

`https://opendata.rdw.nl/resource/8ys7-d773.json?kenteken={KENTEKEN}`

Velden:
- `brandstof_omschrijving` — Benzine/Diesel/Elektriciteit/Hybride
- `co2_uitstoot_gecombineerd` — gram/km, primaire input voor BPM
- `co2_uitstoot_gewogen` — voor PHEV's
- `emissiecode_omschrijving`

Beide responses koppelen op `kenteken`.

### Architectuur in Laravel

Maak een service-class `app/Services/RdwService.php` met methodes:

```php
class RdwService {
    public function lookupVehicle(string $kenteken): ?VehicleData;
    public function lookupFuel(string $kenteken): ?FuelData;
    public function fullLookup(string $kenteken): VehicleLookupResult;
}
```

Implementatiepunten:

- Normaliseer het kenteken: strip streepjes/spaties, uppercase, regex-validatie tegen Nederlands kentekenformaat
- Cache de RDW-respons per kenteken voor 7 dagen via Laravel's `Cache::remember()` — RDW data wijzigt zelden en je beperkt zo zowel rate limits als laadtijd
- Wrap calls in `Http::timeout(5)` met `retry(2, 200)` voor robuustheid
- Geef bij niet-bestaand kenteken een gestructureerde `null` terug, geen exception
- Log fouten naar een aparte channel zodat je RDW-uitval kunt monitoren

### BPM-teruggave berekening

De RDW levert geen BPM-bedrag — die moet je zelf berekenen. Maak `app/Services/BpmCalculator.php`. Hoofdlijn:

- Bereken het oorspronkelijke BPM-bedrag op basis van CO2 (en brandstof + datum eerste toelating, want tarieven verschillen per jaar)
- Pas afschrijvingstabel toe op basis van leeftijd
- Hou rekening met minimumwaarde voor diesel
- Resultaat = restwaarde BPM = teruggave bij export

Belangrijk: dit is een **indicatie**, niet bindend. Toon altijd een disclaimer en bouw de berekening flexibel zodat je hem jaarlijks kunt updaten. Plaats de tarieven in een aparte config-file `config/bpm.php` of database-tabel `bpm_rates`.

## Datamodel

Hieronder de minimale set tabellen voor de MVP:

```
users
  - id, name, email, password, phone, role (klant|admin), timestamps

dossiers
  - id, user_id, status (concept|offerte|akkoord|in_uitvoering|afgerond|geannuleerd)
  - kenteken, merk, model, datum_eerste_toelating, brandstof, co2
  - rdw_data_json (volledige RDW-respons opgeslagen)
  - bpm_indicatie_eur, pakket (basis|compleet|compleet_plus)
  - service_fee_eur, started_at, completed_at, timestamps

documents
  - id, dossier_id, type (paspoort|nie|kentekenbewijs|coc|...), filename, path
  - status (aangevraagd|geupload|goedgekeurd|afgekeurd), reviewed_by, reviewed_at
  - timestamps

dossier_events
  - id, dossier_id, type (status_change|note|email_sent|document_uploaded), payload_json
  - actor_id (user die het deed), created_at

leads
  - id, name, email, phone, kenteken, woonplaats_spanje, source (organic|ads|referral)
  - utm_campaign, utm_source, utm_medium, status (nieuw|gecontacteerd|offerte|gewonnen|verloren)
  - timestamps

bpm_rates
  - id, jaar, brandstof, co2_grens, tarief_per_gram, vaste_voet
```

## Pagina-structuur

### Publiek

- `/` — Homepage met hero, kenteken-lookup direct in de hero, USP's, hoe-werkt-het, social proof
- `/diensten` — Overzicht
  - `/diensten/auto-op-spaans-kenteken`
  - `/diensten/bpm-teruggave`
  - `/diensten/auto-export-nederland`
  - `/diensten/itv-begeleiding`
- `/tarieven` — Drie pakketten transparant
- `/bpm-calculator` — Interactieve calculator (kenteken → CO2 → BPM-indicatie)
- `/over-ons`
- `/reviews`
- `/veelgestelde-vragen`
- `/blog` + `/blog/{slug}`
- `/contact` + offerteformulier
- `/inloggen`, `/registreren`, `/wachtwoord-vergeten`

### Klantenportaal (achter login)

- `/portaal` — Dashboard
- `/portaal/dossier` — Eigen dossier met fasestatus
- `/portaal/documenten` — Upload en overzicht
- `/portaal/berichten` — Communicatie met de uitvoerder
- `/portaal/profiel`

### Admin (achter login + role admin)

- `/admin/dossiers` — Lijst en zoeken
- `/admin/dossiers/{id}` — Detail, statusupdates, notities
- `/admin/leads` — Lead-pipeline
- `/admin/klanten`
- `/admin/content` — Blog beheren

## Implementatieplan in fasen

Verdeeld in zes fasen. Elke fase is een aparte Claude Code-sessie waard.

### Fase 1 — Project setup en huisstijl

1. Laravel 11 installeren met Breeze + Inertia + Vue
2. Tailwind v4 configureren met custom theme: kleuren uit het palet, Fraunces en Inter via Google Fonts, custom font-families
3. Layoutcomponenten maken: `AppLayout.vue`, `GuestLayout.vue`, `PortalLayout.vue`
4. Designsysteem-componenten: `Button.vue`, `Card.vue`, `Input.vue`, `Badge.vue`, `Alert.vue` — allemaal in `resources/js/Components/UI/`
5. Een styleguide-pagina op `/styleguide` (alleen in dev-omgeving) waar alle componenten te zien zijn

### Fase 2 — Publieke site en kenteken-lookup

1. Homepage met hero en kenteken-lookup als hero-component
2. RdwService implementeren met cache, errorhandling, validatie
3. BpmCalculator service met config-driven tarieven
4. Endpoint `/api/lookup/{kenteken}` voor de lookup (Inertia of XHR vanuit Vue-component)
5. UI-flow: kenteken invoeren → loading state → voertuigkaart met merk, model, jaar, BPM-indicatie → CTA naar offerteformulier
6. Statische pagina's: diensten, tarieven, over ons, FAQ
7. Blog met markdown-files of CPT in database
8. Contactformulier met Laravel form request validatie en mail naar partner

### Fase 3 — Auth en klantenportaal MVP

1. Breeze auth uitbreiden met telefoonveld bij registratie
2. Dossier-model en migratie
3. Eerste dossier wordt automatisch aangemaakt na succesvolle offerte-acceptatie
4. Portaal-dashboard met fasestatus (timeline-component)
5. Documenten-upload met validatie op type en grootte, opslag buiten webroot, signed URLs voor toegang
6. Status van documenten zichtbaar (aangeleverd / goedgekeurd / afgekeurd)

### Fase 4 — Admin paneel

1. Filament of een custom Inertia-admin (Filament aanrader voor snelheid)
2. Dossier-overzicht met filtering op status
3. Detailpagina met inline statusupdate
4. Documentbeoordeling: goedkeuren / afkeuren met reden
5. Lead-pipeline (kanban of lijst)
6. Berichtenfunctie tussen admin en klant

### Fase 5 — Polishing

1. SEO: meta tags per pagina via Inertia, sitemap.xml, robots.txt, Schema.org markup voor LocalBusiness
2. Performance: lazy loading, image optimization (Spatie Laravel ImageOptimizer), caching
3. Analytics: Plausible of Google Analytics + GTM
4. Cookies-banner (privacy-first, default decline)
5. Privacyverklaring en algemene voorwaarden
6. E-mailflows: welkomstmail, documenten-herinnering, statusupdate

### Fase 6 — Productie

1. CI/CD via GitHub Actions naar je hostingomgeving
2. SSL en domein
3. Backup-strategie (database + uploads dagelijks)
4. Monitoring (Sentry voor errors, UptimeRobot voor uptime)
5. RDW-rate-limit monitoring

## Hoe Claude Code te gebruiken

### Initieel commando om de eerste sessie op gang te brengen

```
Lees PLAN.md. Begin met Fase 1: zet een nieuw Laravel 11 project op
met Breeze (Vue + Inertia variant), installeer en configureer
Tailwind v4, en implementeer het kleurenpalet en de typografie zoals
beschreven in de huisstijl-sectie. Maak de basislayout-componenten
en een /styleguide pagina waar alle UI-componenten zichtbaar zijn.
Werk in kleine commits per logische stap.
```

### Werkwijze per fase

Houd elke fase apart. Begin elke sessie met:

```
Lees PLAN.md, lees de huidige codebase, en geef me een plan voor
Fase X. Werk dat plan uit als checklist. Na mijn akkoord begin je
met implementeren, één onderdeel per keer.
```

Laat Claude Code niet in één klap een hele fase implementeren — laat hem stappen voorstellen, accordeer per stap, en controleer tussendoor. Dat voorkomt dat je drift krijgt op architectuurkeuzes.

### Aanbevolen MCP-servers voor Claude Code

- **Filesystem MCP** — voor projectbestanden
- **GitHub MCP** — voor commit-flow en issues
- **Database MCP (PostgreSQL/MySQL)** — om migraties en queries te kunnen testen

### Beslismomenten waar je actief moet sturen

- **BPM-berekeningstabellen** — Claude Code kan niet weten wat de huidige tarieven zijn. Bevestig met een fiscalist of importeer ze uit een betrouwbare bron, en plaats ze in `config/bpm.php`.
- **Documentopslag** — Lokaal voor MVP is prima, maar bij eerste echte klanten direct naar S3 of vergelijkbare opslag met versleuteling. Dit is gevoelige data.
- **Inertia versus separate API** — Inertia is mijn aanbeveling, maar als je de site wilt kunnen ombouwen naar een mobile app later, koppel dan vanaf het begin via een interne API.

## Wat dit plan expliciet niet bevat

Geen e-commerce, geen meertaligheid in fase 1 (Spaans en Engels komen pas in fase 2 van het bedrijf), geen native mobile app, geen geavanceerde rapportages. Houd de scope strak — eerst een werkende site met klantenportaal, dan optimaliseren.

## Verwachte tijdsindicatie

Met Claude Code als co-developer en jouw stack-ervaring:

- Fase 1: 1-2 dagen
- Fase 2: 4-6 dagen (RDW + BPM zijn het meeste werk)
- Fase 3: 3-4 dagen
- Fase 4: 3-5 dagen (afhankelijk of je Filament gebruikt)
- Fase 5: 2-3 dagen
- Fase 6: 1-2 dagen

Totaal: ~3-4 weken aaneengesloten werk, of 6-8 weken parttime naast je andere werk.
