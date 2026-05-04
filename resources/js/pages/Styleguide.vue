<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { CheckCircle2, AlertTriangle, Info, XCircle } from 'lucide-vue-next';

const swatches = [
    { name: 'Primary — Diepblauw', token: 'primary', hex: '#1B2B4A' },
    { name: 'Primary light — Mistblauw', token: 'primary-light', hex: '#4A6FA5' },
    { name: 'Accent — Terracotta', token: 'accent', hex: '#C97B5C' },
    { name: 'Accent light — Zandsteen', token: 'accent-light', hex: '#E8C9B8' },
    { name: 'Background — Off-white', token: 'background', hex: '#F7F4EE', dark: false },
    { name: 'Card — Wit', token: 'card', hex: '#FFFFFF', dark: false },
    { name: 'Foreground — Antraciet', token: 'foreground', hex: '#2A2D34' },
    { name: 'Muted foreground — Grijsblauw', token: 'muted-foreground', hex: '#6B7280' },
    { name: 'Success — Zachtgroen', token: 'success', hex: '#5D8B6E' },
    { name: 'Warning — Oker', token: 'warning', hex: '#D4A24C' },
    { name: 'Destructive — Diep rood', token: 'destructive', hex: '#B84545' },
    { name: 'Border', token: 'border', hex: '—', dark: false },
];

const kentekenInput = ref('');
const errorInput = ref('XX-12-Z');
</script>

<template>
    <Head title="Styleguide" />

    <div class="min-h-screen bg-background text-foreground">
        <header class="border-b border-border bg-card">
            <div class="container mx-auto px-6 py-10">
                <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                    Designsysteem
                </div>
                <h1 class="mt-2 font-display text-4xl font-semibold text-primary">
                    autoinvoeren<span class="text-accent">spanje</span>.nl — styleguide
                </h1>
                <p class="mt-3 max-w-2xl text-muted-foreground">
                    Tokens, typografie en componenten die op de site gebruikt worden. Alleen
                    zichtbaar in <code class="rounded bg-muted px-1.5 py-0.5 text-xs">local</code>
                    en <code class="rounded bg-muted px-1.5 py-0.5 text-xs">testing</code>.
                </p>
            </div>
        </header>

        <div class="container mx-auto space-y-16 px-6 py-12">
            <!-- COLORS -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Kleurenpalet</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Tokens worden via CSS variabelen geleverd; gebruik Tailwind utilities zoals
                    <code class="rounded bg-muted px-1 text-xs">bg-primary</code> en
                    <code class="rounded bg-muted px-1 text-xs">text-accent</code>.
                </p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="s in swatches"
                        :key="s.token"
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="h-20"
                            :class="`bg-${s.token}`"
                            :style="{ backgroundColor: `var(--${s.token})` }"
                        />
                        <div class="p-3 text-sm">
                            <div class="font-medium">{{ s.name }}</div>
                            <div class="mt-0.5 text-xs text-muted-foreground">
                                <code>--{{ s.token }}</code> · {{ s.hex }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <Separator />

            <!-- TYPOGRAPHY -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Typografie</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Display: Fraunces (variable serif). Body / UI: Inter (variable sans). Beide via Bunny Fonts.
                </p>
                <div class="mt-6 space-y-6 rounded-2xl border border-border bg-card p-8">
                    <div>
                        <h1 class="font-display text-6xl font-semibold text-foreground">H1 — Fraunces 600</h1>
                        <p class="mt-1 text-xs text-muted-foreground">48–64px · gebruikt voor hero-koppen</p>
                    </div>
                    <div>
                        <h2 class="font-display text-4xl font-semibold text-foreground">H2 — Fraunces 600</h2>
                        <p class="mt-1 text-xs text-muted-foreground">32–40px · sectiekoppen</p>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl font-medium text-foreground">H3 — Fraunces 500</h3>
                        <p class="mt-1 text-xs text-muted-foreground">24–28px · subkoppen</p>
                    </div>
                    <div>
                        <p class="text-base text-foreground">
                            Body — Inter 400, 16px. De RDW levert geen BPM-bedrag — dat berekenen we zelf
                            op basis van CO₂, brandstof en datum eerste toelating, met afschrijving naar
                            leeftijd. Een indicatie, geen bindende offerte.
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">16–18px · standaard body tekst</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Klein — Inter 400, 14px. Captions en metadata.
                        </p>
                    </div>
                    <div>
                        <Button>Knop — Inter 600, 14px</Button>
                    </div>
                </div>
            </section>

            <Separator />

            <!-- BUTTONS -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Knoppen</h2>
                <div class="mt-6 grid gap-6 rounded-2xl border border-border bg-card p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <Button>Primair</Button>
                        <Button variant="secondary">Secundair</Button>
                        <Button class="bg-accent text-accent-foreground hover:bg-accent/90">
                            Accent
                        </Button>
                        <Button variant="outline">Outline</Button>
                        <Button variant="ghost">Ghost</Button>
                        <Button variant="link">Link</Button>
                        <Button variant="destructive">Verwijder</Button>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button size="sm">Klein</Button>
                        <Button>Standaard</Button>
                        <Button size="lg">Groot</Button>
                        <Button disabled>Disabled</Button>
                    </div>
                </div>
            </section>

            <Separator />

            <!-- BADGES -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Badges</h2>
                <div class="mt-6 flex flex-wrap items-center gap-2 rounded-2xl border border-border bg-card p-8">
                    <Badge>Primair</Badge>
                    <Badge variant="secondary">Secundair</Badge>
                    <Badge variant="outline">Outline</Badge>
                    <Badge variant="destructive">Fout</Badge>
                    <Badge class="bg-accent text-accent-foreground">Accent</Badge>
                    <Badge class="bg-success text-success-foreground">Succes</Badge>
                    <Badge class="bg-warning text-warning-foreground">Waarschuwing</Badge>
                </div>
            </section>

            <Separator />

            <!-- INPUTS -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Formulier-elementen</h2>
                <div class="mt-6 grid gap-6 rounded-2xl border border-border bg-card p-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="kenteken">Kenteken</Label>
                        <Input id="kenteken" v-model="kentekenInput" placeholder="bv. 12-ABC-3" />
                        <p class="text-xs text-muted-foreground">Hint: streepjes zijn optioneel.</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">E-mailadres</Label>
                        <Input id="email" type="email" placeholder="naam@voorbeeld.nl" />
                    </div>
                    <div class="space-y-2">
                        <Label for="error" class="text-destructive">Kenteken — met fout</Label>
                        <Input
                            id="error"
                            v-model="errorInput"
                            aria-invalid="true"
                            class="border-destructive focus-visible:ring-destructive/50"
                        />
                        <p class="text-xs text-destructive">Dit kenteken is niet geldig.</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="disabled">Disabled</Label>
                        <Input id="disabled" disabled value="Niet bewerkbaar" />
                    </div>
                </div>
            </section>

            <Separator />

            <!-- ALERTS -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Meldingen</h2>
                <div class="mt-6 space-y-4">
                    <Alert>
                        <Info />
                        <AlertTitle>Info</AlertTitle>
                        <AlertDescription>
                            BPM-bedragen zijn een indicatie en kunnen afwijken van het uiteindelijke
                            besluit van de Belastingdienst.
                        </AlertDescription>
                    </Alert>
                    <Alert class="border-success/30 text-success [&>svg]:text-success">
                        <CheckCircle2 />
                        <AlertTitle>Succes</AlertTitle>
                        <AlertDescription class="text-success/90">
                            Je dossier is aangemaakt. We sturen je binnen 24 uur een bevestiging.
                        </AlertDescription>
                    </Alert>
                    <Alert class="border-warning/40 text-warning-foreground [&>svg]:text-warning">
                        <AlertTriangle />
                        <AlertTitle>Let op</AlertTitle>
                        <AlertDescription>
                            We missen nog je NIE-nummer — upload deze om verder te kunnen.
                        </AlertDescription>
                    </Alert>
                    <Alert variant="destructive">
                        <XCircle />
                        <AlertTitle>Fout</AlertTitle>
                        <AlertDescription>
                            Het opgegeven kenteken kon niet worden gevonden bij de RDW.
                        </AlertDescription>
                    </Alert>
                </div>
            </section>

            <Separator />

            <!-- CARDS -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Cards</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Voertuiggegevens</CardTitle>
                            <CardDescription>Opgehaald via de RDW open-data API</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Merk</span>
                                <span class="font-medium">Volkswagen</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Model</span>
                                <span class="font-medium">Golf 1.5 TSI</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Eerste toelating</span>
                                <span class="font-medium">2019</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">CO₂ uitstoot</span>
                                <span class="font-medium">112 g/km</span>
                            </div>
                        </CardContent>
                        <CardFooter class="flex justify-between">
                            <Badge variant="secondary">Personenauto</Badge>
                            <Button size="sm">Vraag offerte aan</Button>
                        </CardFooter>
                    </Card>

                    <Card class="bg-primary text-primary-foreground">
                        <CardHeader>
                            <CardTitle class="text-primary-foreground">BPM-indicatie</CardTitle>
                            <CardDescription class="text-primary-foreground/70">
                                Op basis van leeftijd en CO₂
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="font-display text-5xl font-semibold text-accent">
                                € 2.840
                            </div>
                            <p class="mt-2 text-sm text-primary-foreground/70">
                                Geschatte teruggave bij export naar Spanje. Indicatief; geen rechten te ontlenen.
                            </p>
                        </CardContent>
                        <CardFooter>
                            <Button class="bg-accent text-accent-foreground hover:bg-accent/90">
                                Start dossier
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </section>

            <Separator />

            <!-- RADII / SHADOWS -->
            <section>
                <h2 class="font-display text-2xl font-semibold">Radii &amp; schaduwen</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <div v-for="r in ['rounded-md', 'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-full']" :key="r"
                         :class="['flex h-24 items-center justify-center bg-card border border-border text-xs text-muted-foreground shadow-sm', r]">
                        {{ r }}
                    </div>
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="s in ['shadow-xs', 'shadow-sm', 'shadow-md', 'shadow-lg']" :key="s"
                         :class="['flex h-24 items-center justify-center rounded-xl bg-card border border-border text-xs text-muted-foreground', s]">
                        {{ s }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
