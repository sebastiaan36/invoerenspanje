---
target: home
total_score: 19
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 4
target_identity: "file:/Users/sebastiaanbeen/Documents/autoinvoerenspanje.nl/resources/js/pages/Welcome.vue"
target_fingerprint: "sha256:2fbe96393337593d261e23dcf9faed84d2eb9a586ecc011975be5fa18cfdd1b0"
target_path: /Users/sebastiaanbeen/Documents/autoinvoerenspanje.nl/resources/js/pages/Welcome.vue
timestamp: 2026-10-24T21-36-48Z
slug: resources-js-pages-welcome-vue
closed: true
---
Method: dual-agent (A: design review · B: detector + browser evidence). Live at 1440/390, real lookup GV-412-X.

## Design Health Score
| # | Heuristic | Score | Key issue |
|---|---|---|---|
| 1 | Visibility of System Status | 2 | Mobile result below fold, no scroll; error fires mid-typing |
| 2 | Match System / Real World | 2 | Jargon (forfaitaire methode, NEDC, Honorarios), "€ -489" |
| 3 | User Control and Freedom | 2 | Offerte CTAs href="/" wipe the result |
| 4 | Consistency and Standards | 1 | je/u mixed; terracotta = refund and cost; BPM refund terracotta vs green |
| 5 | Error Prevention | 2 | Lookup fires at 5 chars; no Bereken button |
| 6 | Recognition Rather Than Recall | 3 | Two different "net" figures to reconcile |
| 7 | Flexibility and Efficiency | 2 | Result not shareable/saveable |
| 8 | Aesthetic and Minimalist Design | 2 | ~8 stacked result blocks, 4 warning boxes |
| 9 | Error Recovery | 1 | "Geen voertuig gevonden" dead end |
| 10 | Help and Documentation | 2 | No human help path |
| **Total** | | **19/40** | **Poor** |

## Design Specificity Verdict
Tool (plate input, Spanish cost lines, BPM vs costs) is product-specific; surrounding page is category template. Team photos, phone/WhatsApp, NL→ES one-team story absent from homepage. Detector: source scan clean (1 advisory: 10px NL); browser detect.js 16 findings desktop / 10 mobile, mainly low contrast: terracotta text on cream 2.6–3.2:1, white on terracotta button 3.2:1, muted 4.4:1. URL-scan footer contrast findings are false positives (wrong bg); cream-palette/overused-font are style noise per DESIGN.md.

## Priority Issues
- [P0] Offerte CTAs (KentekenLookup.vue:661, PublicLayout.vue:93,137,208) href="/" reload and wipe the calculation. Fix: scroll to quote form / plate input. Command: harden
- [P1] Conflicting net figures (NetEffectBlock −489 excl. package vs TotalSummary 1.384 incl.) and reversed colour meaning (terracotta for costs; refund "− € 243" green). Fix: one answer card, + refund / costs rows, collapse details. Command: distill
- [P1] No human trust layer on homepage (faces, phone/WhatsApp, two-lane NL|ES). Command: shape
- [P1] Lookup fires on 5 chars (KentekenLookup.vue:187), no accessible name, result offscreen on mobile. Fix: ≥6 chars, error on Enter/blur, aria-label, Bereken button, scroll/announce. Command: harden
- [P1] Contrast: terracotta text/button and muted-foreground below AA. Fix: darker text terracotta (~hsl(17 50% 42%)), darker muted. Command: colorize

## Persona Red Flags
Jordan: "Stap 3 van 3" without steps 1–2; optional Matrículas Temporales counted; preselected package. Riley: partial-plate error; CTA wipes result; checkbox alone unlocks "Goed nieuws". Casey: result offscreen, form ~5000px down, no sticky CTA/WhatsApp, lost on reload. Retiree: 12px grey uppercase text, alarming "€ -489", Honorarios next to "Vaste prijs", no faces/phone before giving own number.

## Minor Observations
je/u mix (clarify); BPM card empty navy stretch on desktop, ineligible card no CTA; ResidencyExemptionHighlight sparkles + border-2 + shadow-md at rest; "klantenportaal" links to /contact (Welcome.vue:241); footer email not mailto; footer links 17px tall, menu button 36px; heading order (footer H4 after H3, NetEffect H2 early).

## Questions to Consider
- If most older cars net negative, should the first number be "what Spain costs you"?
- Should the lookup end in one human step instead of packages + form?
- What if the NL/ES two lanes became the homepage structure?
