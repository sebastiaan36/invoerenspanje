---
name: autoinvoerenspanje.nl
description: Your Dutch car on a Spanish plate, handled as one calm, orderly dossier in warm southern light.
colors:
  diepblauw: "hsl(220 47% 20%)"
  mistblauw: "hsl(216 38% 47%)"
  terracotta: "hsl(17 55% 42%)"
  zandsteen: "hsl(21 51% 82%)"
  eu-blue: "#003399"
  warm-off-white: "hsl(40 36% 95%)"
  linen-muted: "hsl(40 28% 92%)"
  wit: "hsl(0 0% 100%)"
  antraciet: "hsl(222 11% 18%)"
  grijsblauw: "hsl(220 9% 40%)"
  sand-border: "hsl(30 22% 86%)"
  input-stroke: "hsl(30 15% 52%)"
  plate: "hsl(40 36% 97%)"
  dark-night: "hsl(30 12% 9%)"
  dark-card: "hsl(30 10% 13%)"
  dark-brand: "hsl(30 14% 6%)"
  zachtgroen: "hsl(142 25% 36%)"
  oker: "hsl(38 61% 57%)"
  diep-rood: "hsl(0 46% 50%)"
typography:
  display:
    fontFamily: "Fraunces, ui-serif, Georgia, Cambria, 'Times New Roman', serif"
    fontSize: "clamp(2.25rem, 5vw, 3.75rem)"
    fontWeight: 600
    lineHeight: 1.25
  headline:
    fontFamily: "Fraunces, ui-serif, Georgia, serif"
    fontSize: "clamp(1.875rem, 3.5vw, 2.25rem)"
    fontWeight: 600
    lineHeight: 1.2
  title:
    fontFamily: "Fraunces, ui-serif, Georgia, serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.33
  body:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
  body-lead:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.55
  label:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.33
    letterSpacing: "0.05em"
  plate:
    fontFamily: "Fraunces, ui-serif, Georgia, serif"
    fontSize: "1.5rem"
    fontWeight: 400
    letterSpacing: "0.05em"
rounded:
  sm: "8px"
  md: "10px"
  lg: "12px"
  xl: "16px"
  2xl: "24px"
  full: "9999px"
spacing:
  gutter: "16px"
  card: "24px"
  section: "64px"
  section-lg: "96px"
components:
  button-cta:
    backgroundColor: "{colors.terracotta}"
    textColor: "{colors.wit}"
    rounded: "{rounded.xl}"
    padding: "8px 16px"
    typography: "{typography.label}"
  button-primary:
    backgroundColor: "{colors.diepblauw}"
    textColor: "{colors.warm-off-white}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
    height: "36px"
  button-outline:
    backgroundColor: "{colors.warm-off-white}"
    textColor: "{colors.antraciet}"
    rounded: "{rounded.xl}"
    padding: "10px 16px"
  card:
    backgroundColor: "{colors.wit}"
    textColor: "{colors.antraciet}"
    rounded: "{rounded.2xl}"
    padding: "24px"
  input:
    backgroundColor: "{colors.wit}"
    textColor: "{colors.antraciet}"
    rounded: "{rounded.md}"
    padding: "4px 12px"
    height: "36px"
  plate-input:
    backgroundColor: "{colors.wit}"
    textColor: "{colors.antraciet}"
    rounded: "{rounded.2xl}"
    typography: "{typography.plate}"
    padding: "16px"
  pill-badge:
    backgroundColor: "{colors.zandsteen}"
    textColor: "{colors.antraciet}"
    rounded: "{rounded.full}"
    padding: "4px 12px"
  choice-tile:
    backgroundColor: "{colors.warm-off-white}"
    textColor: "{colors.antraciet}"
    rounded: "{rounded.xl}"
    padding: "16px"
---

# Design System: autoinvoerenspanje.nl

## Overview

**Creative North Star: "Het Zonnige Dossier"**

The system treats a stressful bureaucratic job (BPM, DGT, ITV, matriculación) as one well-kept dossier: ordered, legible, nothing missing. Paper precision supplies the order: a real kenteken plate, labelled data rows, clear amounts, numbered steps. Warm southern light supplies the reassurance: a sun-bleached off-white page instead of clinical white, terracotta for the moments that matter, sandstone for gentle highlights. Deep blue anchors trust in the header text, footer and primary actions.

Density is calm. Public pages breathe with generous section padding, a single centred column for heroes and reading, and white cards floating lightly on the warm page. The serif display face gives headings gravitas without stiffness; Inter keeps every number, label and form field hyper-legible. Motion is quiet: colour transitions and fades, no bounce.

Everything should feel soft and reassuring to the touch: rounded corners, subtle shadows, warm surfaces, and states that confirm rather than alarm.

**Key Characteristics:**
- Warm off-white page (never pure white) with white cards on top.
- Fraunces serif for every heading; Inter for everything you read or type.
- Terracotta reserved for calls to action, highlights and money that comes back.
- The Dutch kenteken plate (EU blue strip) as the signature input.
- Soft, generously rounded containers (12–24px) with a whisper of shadow.

## Colors

A trustworthy deep blue and a sun-warmed terracotta over sandy neutrals: Dutch clarity in Spanish light.

### Primary
- **Diepblauw** (deep navy): logo wordmark, active nav, footer background, app primary buttons, focus ring. The colour of trust and of the paperwork being in order.
- **Mistblauw** (soft steel blue): hover and secondary blue elements, chart series; never a large fill on public pages.

### Secondary
- **Terracotta** (deep warm clay): the public call to action ("Offerte aanvragen", "Bereken"), the highlighted phrase in the hero headline and the selected state of choice tiles. Tuned to 42% lightness so it passes AA as text on the page and under white button labels. On deep-blue surfaces use Zandsteen instead; terracotta is too dark there.
- **Zandsteen** (pale sandstone): pill badges, soft highlight blocks, secondary surfaces.

### Tertiary
- **EU Blue** (#003399 in frontmatter): only on the left strip of the kenteken plate input, as on a real Dutch plate. Never used elsewhere.

### Neutral
- **Warm Off-White**: the page background on every public surface; also the text colour on deep-blue fills.
- **Linen Muted**: hover fills on list rows and choice tiles; muted panels.
- **Wit**: cards, popovers, the plate input.
- **Antraciet**: body text and headings.
- **Grijsblauw**: secondary text, captions, data labels, placeholder text.
- **Sand Border**: all hairline borders and input strokes.

### Status
- **Zachtgroen**: money coming back (BPM refund, "+ €"), check marks in trust lists, confirmations. Tuned to 36% lightness for AA as text on white.
- **Oker**: attention states.
- **Diep Rood**: errors (outlined, never as a full alarm block).

- **Input Stroke** (input-stroke): the border of every form field. Darker than Sand Border so fields stay visible at 3:1 (WCAG 1.4.11); never used for card borders.

### Dark Mode
"Het Zonnige Dossier" after sunset. The dark theme keeps the warmth: neutrals sit on hue 30 (sun-warmed stone), never the cool slate of a starter kit.
- **Night** (dark-night) is the page, **Dark Card** (dark-card) the sheets on it, **Dark Brand** (dark-brand) the footer, a step darker than the page.
- Diepblauw is lifted to a light steel blue (hsl(214 60% 68%)) so links and the wordmark read on dark; buttons on it use dark text.
- Terracotta brightens to hsl(17 62% 58%) and keeps its job: action and selection.
- The **brand surface** (footer) uses its own tokens (`--brand`, `--brand-foreground`, `--brand-accent`): diepblauw in light, Dark Brand in dark, always with off-white text and Zandsteen accents. Never build the footer from `--primary`, which flips lightness between themes.

### Named Rules
**The Real Plate Rule.** The kenteken plate (input and result chip) is always a light plate (`--plate`) with dark characters, in both themes, like the physical object.

**The Sunlight Rule.** The page is warm off-white, not white. Pure white is reserved for cards and inputs that sit on top of it, so every container reads as a sheet in the dossier.

**The Terracotta Means Action Rule.** Terracotta marks what to do next: CTAs, the hero highlight, selected choices. Money you get back is Zachtgroen with a "+"; costs and net totals stay Antraciet. Never colour a cost terracotta.

**The Contrast Floor Rule.** Every text colour clears 4.5:1 on its surface. Terracotta and Zandsteen swap roles on deep blue.

## Typography

**Display Font:** Fraunces (with ui-serif, Georgia fallback)
**Body Font:** Inter (with ui-sans-serif, system-ui fallback)

**Character:** A warm, slightly old-style variable serif for headings paired with a neutral, highly legible sans for content and data. The serif gives the dossier its voice; the sans does the bookkeeping.

### Hierarchy
- **Display** (600, 36px → 60px, leading-tight): page hero H1 only, centred, with one phrase in terracotta.
- **Headline** (600, 30px → 36px): section H2s.
- **Title** (600, 20–24px): card titles, vehicle name in the lookup result, footer column heads (smaller).
- **Body** (400, 16px, 1.5): running text; long-form content uses the typography plugin at `prose-lg` inside a max-w-3xl column (~65–75ch).
- **Body Lead** (400, 18px): hero subcopy in Grijsblauw, max-w-2xl.
- **Label** (600, 12px, 0.05em tracking, uppercase): legacy small labels in app screens only. New public surfaces don't put a label above a heading; the heading carries itself.
- **Plate** (400, 24px, wide tracking): the typed kenteken, set in Fraunces like an embossed plate.

### Named Rules
**The Serif Heads, Sans Hands Rule.** Every h1–h4 is Fraunces (set globally in the base layer); every number, label, input and button is Inter. The kenteken plate is the only exception.

## Layout

Centred containers with a 16px gutter. Hero and reading sections use a single column (max-w-6xl for the hero frame, max-w-3xl for text and the lookup tool, max-w-2xl for lead copy). Sections stack with 64px vertical padding, 96px on desktop heroes, separated by a hairline top border and an alternating sandstone-tinted band (Zandsteen at 30%) for rhythm. Full-width bands are never pure white; white is for cards. Grids switch to 3 columns at `lg` for result cards and 4 columns in the footer at `md`. Header is sticky, 64px tall, translucent off-white with backdrop blur; navigation collapses to a sheet below `md`.

## Elevation & Depth

Mostly flat with a whisper of lift. Cards and the plate input carry the lightest shadow at rest; stronger shadows appear only as hover feedback or for overlays. Depth comes mainly from the white-on-warm tonal difference and hairline sand borders, not from shadow.

### Shadow Vocabulary
- **Rest** (`shadow-sm`): cards, plate input, CTA button.
- **Hover** (`shadow-md` / `shadow-lg`): interactive cards on hover; dialogs and popovers.
- **Hairline** (`shadow-xs`): outline buttons and inputs.

### Named Rules
**The Lift On Touch Rule.** Surfaces rest almost flat; a stronger shadow is a response to hover or a floating layer, never a default.

## Shapes

Soft and generously rounded, scaled to size: 8–10px for small controls (app buttons, inputs), 12–16px for public buttons and choice tiles, 24px for large blocks (cards, the plate input, result panels), full pills for badges, radio dots and icon buttons. Borders are 1px hairlines in Sand Border; selected states swap the border to terracotta rather than thickening it.

## Components

### Buttons
Soft, warm and unmistakable.
- **Shape:** gently rounded (16px) on public pages; app/portal buttons use the shadcn base (10px, 36px tall).
- **CTA (public):** terracotta fill, white Inter 600 at 14px, 8px × 16px padding, rest shadow; hover darkens to 90% opacity.
- **Primary (app):** diepblauw fill with off-white text; hover 90%.
- **Outline:** hairline sand border on the page colour; hover fills with sandstone.
- **Focus:** 3px ring in diepblauw at 50% (`ring-ring/50`).

### Chips / Badges
- **Pill badge:** sandstone fill, antraciet text, 12px semibold, full pill. Status tags only, never as a kicker above a heading.

### Cards / Containers
- **Corner Style:** 24px on public pages (16px for the shadcn base card in the app).
- **Background:** white on the warm page.
- **Shadow Strategy:** rest shadow; see Elevation.
- **Border:** 1px sand hairline.
- **Internal Padding:** 24px.

### Inputs / Fields
- **Style:** hairline sand stroke, transparent/white fill, 10px radius, 36px tall.
- **Focus:** stroke shifts to diepblauw with a 3px soft ring.
- **Error:** destructive border and faint red ring; inline error text below.

### Result Answer Card
The first thing after a lookup: a white 24px card with a mini plate chip and a one-line vehicle summary, one headline ("Wat het u netto kost" or "Wat u netto overhoudt"), one Fraunces amount at 48–60px, one sentence of context, the terracotta quote CTA and a "Pakketten vergelijken" text link. A muted panel on the right breaks it down as "U betaalt" / "U krijgt terug" / "Netto". All other detail lives in a "Bekijk de volledige berekening" disclosure below.

### Contact Strip (shared)
`ContactStrip.vue`: the human trust layer every public page ends with. A muted 24px panel with overlapping round photos of Maikel and Sebastiaan, a Fraunces name line, one sentence of context, and three actions: a deep-blue "WhatsApp ons" button, an outlined phone button and a text link. Pages pass their own sentence ("Twijfelt u welk pakket bij u past?"); the actions never change.

### Choice Tiles
Radio options rendered as full-width tiles: off-white fill, sand hairline, 16px radius, min 52px tall. Hover tints to Linen Muted; checked swaps the border to terracotta with a 5% terracotta wash and a filled terracotta dot.

### Navigation
Sticky translucent header: Fraunces wordmark "autoinvoeren**spanje**.nl" with "spanje" in terracotta, Inter 14px medium links at 80% antraciet, active link in diepblauw with a 2px terracotta underline at the header's bottom edge. Terracotta CTA on the right. Mobile: hamburger opens a full-width list with hairline separators and two equal buttons (outline + CTA). Footer: deep-blue band, off-white text at 70%, links hover to terracotta.

### Kenteken Plate Input (signature)
A large 24px-rounded white field shaped like a Dutch plate: an EU-blue strip on the left with small white uppercase "NL" lettering, the typed plate in Fraunces 24px with wide tracking, and a terracotta "Bereken" button inside the plate that shows a spinner while looking up. The input is labelled "Nederlands kenteken" for assistive tech, looks up automatically once 6 characters are typed, and keeps the plate in the URL (`?kenteken=`). Focus adds a soft ring around the whole plate. This is the front door of the product; keep it the most prominent interactive element on any page it appears.

## Do's and Don'ts

### Do:
- **Do** use the warm off-white page with white cards on top; keep pure white for containers.
- **Do** set every heading in Fraunces 600 and every label, number and control in Inter.
- **Do** reserve terracotta for the next action and the selected option; show money coming back in Zachtgroen with a "+".
- **Do** lead a result with one answer (one amount, one sentence, one action) and fold the detail into a disclosure.
- **Do** keep corners soft: 24px for big blocks, 16px for public buttons and tiles, full pills for badges.
- **Do** show a selected option with a terracotta border, a 5% terracotta wash and a filled terracotta indicator; a recommendation is a 14px sentence inside the card, never a second coloured border.
- **Do** make every form field and button at least 44px tall on public pages.
- **Do** use Zachtgroen check marks for reassurance lists under the hero.

### Don't:
- **Don't** use EU blue anywhere except the plate strip.
- **Don't** use pure white or cool grey as the page background, in either theme.
- **Don't** add bouncy or attention-seeking motion; transitions are colour and opacity fades.
- **Don't** stack heavy shadows at rest; lift only on hover or for overlays.
- **Don't** set headings in Inter or body copy in Fraunces.
- **Don't** put an eyebrow or kicker label above a heading, or a pill badge above the hero headline.
- **Don't** use body or helper text smaller than 14px on public pages; the audience skews 60+.
