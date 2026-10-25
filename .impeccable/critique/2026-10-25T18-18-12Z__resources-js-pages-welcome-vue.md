---
target: home (light + dark)
total_score: 27
max_score: 36
na_heuristics: 7
p0_count: 1
p1_count: 2
target_identity: "file:/Users/sebastiaanbeen/Documents/autoinvoerenspanje.nl/resources/js/pages/Welcome.vue"
target_fingerprint: "sha256:b5880386a497ede3c972110001a110f0f89af04c2ec3a5827d001795783cb1ee"
target_path: /Users/sebastiaanbeen/Documents/autoinvoerenspanje.nl/resources/js/pages/Welcome.vue
timestamp: 2026-10-25T18-18-12Z
slug: resources-js-pages-welcome-vue
---
Method: dual-agent (A: design review light+dark · B: detector + browser evidence light+dark). Live 1440/390, lookup GV412X + ZZ999Z in both schemes. Correction: footer contrast hits from URL scanner are real dark-mode defects, not false positives.

## Design Health Score
| # | Heuristic | Light | Dark | Key issue |
|---|---|---|---|---|
| 1 | Visibility of System Status | 3 | 3 | Plate frame not red on error |
| 2 | Match System / Real World | 4 | 3 | Plate loses plate look in dark |
| 3 | User Control and Freedom | 3 | 3 | — |
| 4 | Consistency and Standards | 2 | 1 | Selected vs recommended colours; primary flips in dark |
| 5 | Error Prevention | 3 | 3 | — |
| 6 | Recognition Rather Than Recall | 3 | 3 | — |
| 7 | Flexibility and Efficiency | n/a | n/a | Single-task Persuade page |
| 8 | Aesthetic and Minimalist Design | 3 | 2 | Dark: flat slate, sections blur |
| 9 | Error Recovery | 3 | 3 | — |
| 10 | Help and Documentation | 3 | 3 | — |
| Total | | 27/36 | 24/36 | Light Good (75%), dark Acceptable (67%) |

## Priority Issues
- [P0] Dark: footer bg-primary becomes hsl(216 38% 60%) with dark text, 2.1–3.5:1 (PublicLayout.vue:205). Fix: --surface-brand token. colorize
- [P1] Dark: cold slate neutrals, primary flips, input borders 1.4:1, required asterisk 3.6:1, plate becomes dark slab. Fix warm dark tokens / or force light on public pages. colorize
- [P1] Light: PackageSelector selected (navy) vs recommended (terracotta) conflict; 12px uppercase AANBEVOLEN; "Kies dit pakket" not button-like. polish
- [P2] Light: input borders ~1.3:1 (WCAG 1.4.11), prose links 4.47:1 on sand band, full-width white band. colorize
- [P2] Tap targets: quote inputs 36px, submit 40px, contact links 32px, header links 20px. adapt

## Persona Red Flags
Jordan: jargon explained late. Casey: ~12,000px after lookup. Riley: unformatted not-found plate, no error frame, two highlighted cards. Retiree: 12px badge, faint fields. Dark-mode user: footer fails, fields vanish, off-brand.

## Minor Observations
peer-checked check icon never shows (KentekenLookup.vue:621); mobile placeholder truncated; html bg pure white (app.blade.php:44); dark error red borderline.

## Questions to Consider
- Force light mode on public pages?
- No face or real number above the fold?
- Should the answer card say honestly whether it pays off?
