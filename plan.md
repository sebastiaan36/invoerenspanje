
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

Wanneer is BPM-teruggave mogelijk
Niet elke geëxporteerde auto komt in aanmerking. De Belastingdienst hanteert vijf voorwaarden:

De auto is op of na 16 oktober 2006 voor het eerst in Nederland geregistreerd
De auto wordt daadwerkelijk en duurzaam geëxporteerd naar een EU- of EER-land
De auto wordt afgemeld bij de RDW met de status "Export"
De auto is niet bestemd voor sloop
Het verzoek tot teruggave wordt binnen 13 weken na de exportdatum ingediend bij de Belastingdienst

In de calculator moet de eerste voorwaarde direct gecheckt worden op basis van de RDW-respons. Als datum_eerste_toelating voor 16 oktober 2006 ligt, toon dan: "Helaas, BPM-teruggave is alleen mogelijk voor auto's geregistreerd na oktober 2006. We helpen u wel met de Spaanse importkant."
De drie afschrijvingsmethoden
De Belastingdienst staat drie methoden toe om de afschrijving te berekenen. De aanvrager mag de gunstigste kiezen, maar de keuze is definitief:

Forfaitaire afschrijvingstabel — Vast percentage op basis van leeftijd. Eenvoudig, geen taxatie nodig. Default voor de meeste exporteurs.
Koerslijst — Werkelijke handelsinkoopwaarde uit een erkende koerslijst (ANWB, AutotelexPro, EuroTaxGlass). Vaak gunstiger voor jonge auto's met weinig kilometers.
Taxatierapport — Alleen toegestaan bij bovengemiddelde schade of voor auto's die niet in koerslijsten voorkomen.

In onze calculator gebruiken we de forfaitaire tabel voor de indicatie. Bij de offerte rekent je partner alle drie de methoden door en kiest de gunstigste. Vaak levert de koerslijstmethode bij auto's tot 4 jaar oud een hogere teruggave op.
De formule
De rest-BPM bij export wordt berekend in drie stappen:
Rest-BPM = Bruto BPM × (100 − afschrijvingspercentage) / 100
Waarbij:

Bruto BPM = het BPM-bedrag dat oorspronkelijk bij eerste toelating betaald is, herberekend volgens het tarief dat in dat jaar gold
Afschrijvingspercentage = uit de forfaitaire tabel op basis van leeftijd (datum eerste toelating tot exportdatum)

Stap 1 — Bruto BPM bepalen
De bruto BPM is afhankelijk van CO2-uitstoot, brandstof en het bouwjaar. De Belastingdienst publiceert per jaar een tarieftabel met schijven. De algemene formule:
Bruto BPM = vaste voet + Σ (CO2_in_schijf × tarief_per_gram_in_schijf)
Voor dieselauto's komt daar een dieseltoeslag bij:
Dieseltoeslag = max(0, CO2 − drempel) × tarief_per_gram_diesel
Voor PHEV's gelden aparte (lagere) tarieven. Voor volledig elektrisch geldt sinds 2025 een vast bedrag van €600.
Voorbeeld tarieftabel 2019 (illustratief — exacte cijfers per jaar uit Staatscourant):
CO2-schijf (g/km)Tarief per gramVaste voet0 – 71€0€36671 – 95€6895 – 139€152139 – 161€236161 – 192€414192+€557
Dieseltoeslag 2019: €87,38 per gram boven 70 g/km.
Deze tarieven moeten per bouwjaar in config/bpm_rates.php worden opgenomen. De RDW geeft je datum_eerste_toelating, en op basis van het jaartal pak je de juiste tabel.
Stap 2 — Afschrijvingspercentage uit de forfaitaire tabel
De forfaitaire tabel bestaat uit een vast deel voor de eerste 9 maanden, gevolgd door een lineaire afschrijving per maand. De huidige structuur (illustratief — exacte percentages uit de Uitvoeringsregeling BPM jaarlijks controleren):
LeeftijdAfschrijving (cumulatief)0 – 1 maand0% (auto geldt als nieuw)1 – 3 maanden12%3 – 5 maanden18%5 – 9 maanden24%9 – 18 maanden28% + 1% per maand boven 918 – 30 maanden37% + 1% per maand boven 1830 – 42 maanden47% + 0,833% per maand boven 3042 – 54 maanden57% + 0,75% per maand boven 4254 – 66 maanden66% + 0,583% per maand boven 5466 – 78 maanden73% + 0,5% per maand boven 6678 – 90 maanden79% + 0,417% per maand boven 7890 – 102 maanden84% + 0,333% per maand boven 90102 – 114 maanden88% + 0,333% per maand boven 102114 maanden – 25 jaar92% interpolerend naar 100%25+ jaar100% (geen rest-BPM)
Belangrijk over de leeftijdsbepaling: een periode die begint op de laatste dag van een maand en eindigt op de laatste dag van een kortere maand telt als een hele maand. Voorbeeld: 31 januari t/m 28 februari = 1 maand, maar 31 januari t/m 1 maart = 2 maanden. In code rond je dus naar boven af aan het einde van de maand.
Voorbeeldberekening
Een Volkswagen Golf 1.5 TSI Comfortline, benzine, datum eerste toelating 15 april 2019, CO2-uitstoot 130 g/km (NEDC), catalogusprijs €31.500. De auto wordt geëxporteerd op 15 april 2026.
Stap 1 — Bruto BPM bepalen (2019-tarief)
CO2 = 130 g/km, valt door drie schijven:
Schijf 1 (0–71):    71 × €0   = €0
Schijf 2 (71–95):   24 × €68  = €1.632
Schijf 3 (95–130):  35 × €152 = €5.320
Vaste voet:                     €366
                                ─────
Bruto BPM:                      €7.318
Geen dieseltoeslag (benzine).
Stap 2 — Afschrijvingspercentage bepalen
Periode 15 april 2019 tot 15 april 2026 = 84 maanden = 7 jaar exact.
Uit de tabel: 78–90 maanden valt onder "79% + 0,417% per maand boven 78".
Maanden boven 78:   84 − 78 = 6
Extra afschrijving: 6 × 0,417% = 2,502%
Totaal afschrijving: 79% + 2,502% = 81,5%
Stap 3 — Rest-BPM berekenen
Rest-BPM = €7.318 × (100 − 81,5) / 100
        = €7.318 × 0,185
        = €1.354
Bij export naar Spanje krijgt deze klant dus circa €1.354 terug van de Nederlandse Belastingdienst.
Voor jongere auto's of auto's met hogere bruto BPM (diesels, SUV's met hoge CO2) loopt dit bedrag fors op. Een 4-jarige BMW X5 diesel met €25.000 bruto BPM en 57% afschrijving levert bijvoorbeeld €10.750 op.
Implementatie
Service-class
phpnamespace App\Services;

use App\Data\VehicleData;
use App\Data\BpmResult;
use Carbon\Carbon;

class BpmCalculator
{
    public function calculateRestBpm(VehicleData $vehicle, Carbon $exportDate): BpmResult
    {
        // Voorwaarde: alleen voor auto's vanaf 16 oktober 2006
        if ($vehicle->datumEersteToelating->lt(Carbon::create(2006, 10, 16))) {
            return BpmResult::notEligible('Datum eerste toelating voor 16 oktober 2006');
        }

        $brutoBpm = $this->calculateBrutoBpm($vehicle);
        $months = $this->calculateAgeInMonths($vehicle->datumEersteToelating, $exportDate);
        $afschrijving = $this->getDepreciationPercentage($months);
        
        $restBpm = $brutoBpm * (100 - $afschrijving) / 100;

        return new BpmResult(
            brutoBpm: $brutoBpm,
            afschrijvingPercentage: $afschrijving,
            ageMonths: $months,
            restBpm: round($restBpm, 2),
            method: 'forfaitair',
        );
    }

    private function calculateBrutoBpm(VehicleData $vehicle): float
    {
        $year = $vehicle->datumEersteToelating->year;
        $rates = config("bpm_rates.{$year}");
        
        if (!$rates) {
            throw new \RuntimeException("Geen BPM-tarieven voor bouwjaar {$year}");
        }

        // Elektrisch: vast tarief
        if ($vehicle->brandstof === 'Elektriciteit') {
            return $rates['ev_fixed'] ?? 0;
        }

        $bpm = $this->calculateCo2Component($vehicle->co2, $rates['brackets']);
        $bpm += $rates['fixed_base'] ?? 0;

        if ($vehicle->brandstof === 'Diesel') {
            $bpm += $this->calculateDieselToeslag($vehicle->co2, $rates['diesel']);
        }

        return $bpm;
    }

    private function calculateCo2Component(float $co2, array $brackets): float
    {
        $bpm = 0;
        $previousLimit = 0;

        foreach ($brackets as $bracket) {
            $limit = $bracket['max'] ?? PHP_FLOAT_MAX;
            $effectiveCo2 = min($co2, $limit);
            $gramsInBracket = max(0, $effectiveCo2 - $previousLimit);
            $bpm += $gramsInBracket * $bracket['rate'];
            
            if ($co2 <= $limit) {
                break;
            }
            $previousLimit = $limit;
        }

        return $bpm;
    }

    private function calculateDieselToeslag(float $co2, array $dieselConfig): float
    {
        $excess = max(0, $co2 - $dieselConfig['threshold']);
        return $excess * $dieselConfig['rate_per_gram'];
    }

    private function calculateAgeInMonths(Carbon $start, Carbon $end): int
    {
        // Belastingdienst-regel: einde-maand-tot-einde-maand telt als hele maand
        return $start->diffInMonths($end);
    }

    private function getDepreciationPercentage(int $months): float
    {
        $table = config('bpm_rates.depreciation_table');
        
        foreach ($table as $tier) {
            if ($months <= $tier['max_months']) {
                $extraMonths = max(0, $months - $tier['base_months']);
                return $tier['base_percentage'] + ($extraMonths * $tier['per_month']);
            }
        }

        return 100; // 25+ jaar
    }
}
Configuratiebestand
php// config/bpm_rates.php
return [
    'depreciation_table' => [
        ['max_months' => 1,   'base_months' => 0,   'base_percentage' => 0,    'per_month' => 0],
        ['max_months' => 3,   'base_months' => 1,   'base_percentage' => 0,    'per_month' => 6],
        ['max_months' => 5,   'base_months' => 3,   'base_percentage' => 12,   'per_month' => 3],
        ['max_months' => 9,   'base_months' => 5,   'base_percentage' => 18,   'per_month' => 1.5],
        ['max_months' => 18,  'base_months' => 9,   'base_percentage' => 28,   'per_month' => 1.0],
        ['max_months' => 30,  'base_months' => 18,  'base_percentage' => 37,   'per_month' => 0.833],
        ['max_months' => 42,  'base_months' => 30,  'base_percentage' => 47,   'per_month' => 0.833],
        ['max_months' => 54,  'base_months' => 42,  'base_percentage' => 57,   'per_month' => 0.75],
        ['max_months' => 66,  'base_months' => 54,  'base_percentage' => 66,   'per_month' => 0.583],
        ['max_months' => 78,  'base_months' => 66,  'base_percentage' => 73,   'per_month' => 0.5],
        ['max_months' => 90,  'base_months' => 78,  'base_percentage' => 79,   'per_month' => 0.417],
        ['max_months' => 102, 'base_months' => 90,  'base_percentage' => 84,   'per_month' => 0.333],
        ['max_months' => 114, 'base_months' => 102, 'base_percentage' => 88,   'per_month' => 0.333],
        ['max_months' => 300, 'base_months' => 114, 'base_percentage' => 92,   'per_month' => 0.043],
    ],
    
    2019 => [
        'fixed_base' => 366,
        'brackets' => [
            ['max' => 71,  'rate' => 0],
            ['max' => 95,  'rate' => 68],
            ['max' => 139, 'rate' => 152],
            ['max' => 161, 'rate' => 236],
            ['max' => 192, 'rate' => 414],
            ['max' => null, 'rate' => 557],
        ],
        'diesel' => [
            'threshold' => 70,
            'rate_per_gram' => 87.38,
        ],
        'ev_fixed' => 0,
    ],
    
    2020 => [ /* tarieven 2020 */ ],
    2021 => [ /* tarieven 2021 */ ],
    2022 => [ /* tarieven 2022 */ ],
    2023 => [ /* tarieven 2023 */ ],
    2024 => [ /* tarieven 2024 */ ],
    2025 => [ /* tarieven 2025 */ ],
    2026 => [ /* tarieven 2026 */ ],
];
Test-cases om mee te beginnen
Bouw direct deze tests bij de service:
php// tests/Unit/BpmCalculatorTest.php

test('berekent rest-bpm voor benzine personenauto 7 jaar oud', function () {
    $vehicle = new VehicleData(
        kenteken: 'XX-123-Y',
        datumEersteToelating: Carbon::create(2019, 4, 15),
        co2: 130,
        brandstof: 'Benzine',
    );

    $result = (new BpmCalculator())->calculateRestBpm(
        $vehicle, 
        Carbon::create(2026, 4, 15)
    );

    expect($result->brutoBpm)->toEqualWithDelta(7318, 5);
    expect($result->afschrijvingPercentage)->toEqualWithDelta(81.5, 0.1);
    expect($result->restBpm)->toEqualWithDelta(1354, 5);
});

test('weigert teruggave voor auto van voor 16 oktober 2006', function () {
    $vehicle = new VehicleData(
        datumEersteToelating: Carbon::create(2006, 10, 15),
        co2: 130,
        brandstof: 'Benzine',
    );

    $result = (new BpmCalculator())->calculateRestBpm($vehicle, now());

    expect($result->isEligible)->toBeFalse();
});
Onderhoud en updates
De BPM-tarieven worden jaarlijks per 1 januari aangepast in de Staatscourant. Soms gelden zelfs binnenjaarse wijzigingen. De afschrijvingstabel ligt vast in de Uitvoeringsregeling BPM 1992 en wordt zelden gewijzigd, maar controleer dit jaarlijks in december voor het komende jaar.
Plan in december elk jaar een update-taak:

Download de meest recente BPM-tarieven van belastingdienst.nl en Staatscourant
Update config/bpm_rates.php met de nieuwe schijven en bedragen voor het komende jaar
Run de testsuite om te verifiëren dat oude cases nog kloppen
Update ook de Spaanse config/spain_import.php (zie addendum 1)
Test de calculator end-to-end met een handvol bekende voertuigen

Bij twijfel over een specifieke berekening: laat je partner de exacte bruto BPM opvragen bij de Belastingdienst Douane via de Belasting Telefoon Auto (0800-0749). Dat is gratis en geeft uitsluitsel.
Belangrijke disclaimers in de UI
Op de calculator-pagina en in elke offerte vermelden:

Indicatieve berekening volgens forfaitaire afschrijvingstabel
Het werkelijke bedrag kan afwijken. In sommige gevallen levert berekening met een koerslijst of taxatierapport een hogere teruggave op — wij rekenen alle drie de methoden door bij uw offerte en kiezen de gunstigste. Aan deze indicatie kunnen geen rechten worden ontleend.

Dit beschermt je juridisch én geeft de klant een concrete reden om de offerte aan te vragen ("misschien krijg ik wel meer terug").

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
