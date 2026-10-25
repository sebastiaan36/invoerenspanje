# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary: Dutch people living in, or moving to, the Costa del Sol / Málaga region (emigrants, overwinteraars, retirees) who want to keep their familiar Dutch car and need it registered on a Spanish plate. They are not car or bureaucracy experts, often unsure about Spanish procedures (DGT, ITV, IEDMT, matriculación) and about what it will cost or what BPM they get back. Their job: get the car legally on Spanish plates with as little hassle and uncertainty as possible, in their own language.

Secondary: existing clients using the client portal to follow their dossier; internal admins (Maikel, Sebastiaan) handling leads and dossiers.

## Product Purpose

autoinvoerenspanje.nl takes care of the full trajectory of putting a Dutch car on a Spanish license plate: BPM refund and export on the Dutch side, matriculación and ITV on the Spanish side. Success means a visitor understands what they'll get back and what it costs, trusts the team, and requests a quote or opens a dossier.

## Positioning

- **Everything from one hand, NL → ES**: BPM refund and export in the Netherlands plus matriculación/ITV in Spain, handled by one team instead of combining a Dutch exporter and a Spanish gestoría.
- **Dutch-speaking, personal contact with fixed prices**: transparent packages instead of open-ended quotes.

## Operating Context

- Visitor enters a Dutch kenteken; the site looks up RDW open data and shows vehicle data plus an indication of BPM refund and Spanish import costs (BPM calculator).
- Quote request / lead → dossier; clients log in to the portal for status, document upload and messages.
- Contact via phone, WhatsApp and contact form.

## Capabilities and Constraints

- Public marketing site: home, diensten, tarieven, BPM-calculator, FAQ, over ons, contact, blog.
- Client portal behind login (dossier, documents); admin via Filament.
- Packages (config/packages.php): Basis €395, Compleet €895, Compleet Plus €1.495.
- BPM figures are an indication; RDW `bruto_bpm` is primary data, tariff tables are fallback.
- Language: Dutch only. Stack: Laravel 13 + Inertia v3 + Vue 3 + Tailwind v4; no SSR (public pages render client-side).

## Brand Commitments

- Name: autoinvoerenspanje.nl. Team: Maikel (IVA-certified automotive specialist, Maikel Mobility, works with recognised RDW export partners, knows the DGT) and Sebastiaan (online marketing, client contact).
- Voice: Dutch, personal and reassuring; existing copy addresses visitors with "u" on most pages.

## Evidence on Hand

- Real client reviews exist (not yet in the repo; must be supplied by the user before use).
- A real count of completed cars/dossiers exists (number must come from the user; never estimate).
- Real team/work photos: `public/images/maikel.jpeg`, `public/images/sebastiaan.jpeg`; more work photos to be supplied by the user.
- Never fabricate testimonials, numbers, partner logos or certifications beyond the above.

## Product Principles

1. Remove uncertainty first: show what the car is, what comes back and what it costs before asking for anything.
2. One team, whole trajectory: always communicate that NL and ES sides are handled together.
3. Personal and Dutch-speaking: real people with real faces, reachable by phone/WhatsApp.
4. Honest numbers: fixed prices, and BPM shown clearly as an indication.
