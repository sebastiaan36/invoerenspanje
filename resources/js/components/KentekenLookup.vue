<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Loader2,
    AlertCircle,
    Info,
    AlertTriangle,
    HelpCircle,
    Check,
    ChevronDown,
    MessageCircle,
    Phone,
} from 'lucide-vue-next';
import {
    ref,
    computed,
    watch,
    nextTick,
    onMounted,
    onBeforeUnmount,
} from 'vue';
import ImportCostsCard from '@/components/ImportCostsCard.vue';
import PackageSelector from '@/components/PackageSelector.vue';
import type { ServicePackage } from '@/components/PackageSelector.vue';
import QuoteRequestForm from '@/components/QuoteRequestForm.vue';
import ResidencyExemptionHighlight from '@/components/ResidencyExemptionHighlight.vue';
import ResultAnswerCard from '@/components/ResultAnswerCard.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { postJson } from '@/lib/api';
import type { ApiError } from '@/lib/api';
import { PHONE, PHONE_DISPLAY, WHATSAPP_URL } from '@/lib/contact';

type ResidencyChoice = '' | 'permanent' | 'second_home' | 'other';

// TODO: maak hier een dropdown van zodra we Costa Brava (Cataluña) of
// Costa Blanca (Valencia) ondersteunen — die hebben afwijkende IEDMT-tarieven.
const DEFAULT_AUTONOMIA = 'costa_del_sol';

interface BpmPayload {
    is_eligible: boolean;
    ineligible_reason: string | null;
    bruto_bpm_eur: number;
    rest_bpm_eur: number;
    afschrijving_pct: number;
    age_months: number;
    method: string;
    notes: string[];
}

interface ImportCostsPayload {
    iedmt_eur: number;
    iedmt_without_exemption_eur: number;
    iedmt_rate_pct: number;
    iedmt_exempt: boolean;
    iedmt_exempt_reason: string | null;
    estimated_market_value_eur: number;
    fixed_costs: { key: string; label: string; amount_eur: number }[];
    fixed_costs_total_eur: number;
    total_eur: number;
    autonomia: string;
    notes: string[];
}

interface VehicleResponse {
    found: true;
    kenteken: string;
    vehicle: {
        merk: string | null;
        handelsbenaming: string | null;
        voertuigsoort: string | null;
        eerste_kleur: string | null;
        datum_eerste_toelating: string | null;
        massa_ledig_voertuig: number | null;
    };
    fuel: {
        brandstof: string | null;
        co2_gecombineerd: number | null;
        co2_gewogen: number | null;
        co2_wltp_gecombineerd: number | null;
        co2_wltp_gewogen: number | null;
    } | null;
    bpm: BpmPayload | null;
    import_costs: ImportCostsPayload | null;
    net_effect_eur: number | null;
}

interface ValidationErrorBody {
    errors?: { kenteken?: string[] };
    message?: string;
}

interface NotFoundBody {
    message?: string;
}

const props = withDefaults(defineProps<{ bpmOnly?: boolean }>(), {
    bpmOnly: false,
});

const kenteken = ref('');
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const notFound = ref(false);
const result = ref<VehicleResponse | null>(null);
const announcement = ref('');

const kentekenInput = ref<HTMLInputElement | null>(null);
const resultSection = ref<HTMLElement | null>(null);
const packagesSection = ref<HTMLElement | null>(null);
const quoteSection = ref<HTMLElement | null>(null);

const residencyChoice = ref<ResidencyChoice>('');
const ownershipConfirmed = ref(false);
const autonomia = ref(DEFAULT_AUTONOMIA);

const residencyChange = computed(
    () => residencyChoice.value === 'permanent' && ownershipConfirmed.value,
);

const co2 = computed<number | null>(
    () =>
        result.value?.fuel?.co2_wltp_gecombineerd ??
        result.value?.fuel?.co2_wltp_gewogen ??
        result.value?.fuel?.co2_gecombineerd ??
        null,
);

// Vragen alleen tonen wanneer er daadwerkelijk IEDMT wordt geheven —
// anders heeft de vrijstelling geen effect en is de vraag verwarrend.
// CO2 = 120 zit precies in de 0%-schijf, dus 'co2 >= 120' was te ruim.
const showResidencyControls = computed(() => {
    const rate = result.value?.import_costs?.iedmt_rate_pct;

    return typeof rate === 'number' && rate > 0;
});

const iedmtSavings = computed<number>(() => {
    const ic = result.value?.import_costs;

    if (!ic) {
        return 0;
    }

    return Math.max(0, ic.iedmt_without_exemption_eur - ic.iedmt_eur);
});

const showExemptionHighlight = computed(
    () =>
        result.value?.import_costs?.iedmt_exempt === true &&
        iedmtSavings.value > 0,
);

const page = usePage<{ packages: ServicePackage[] }>();
const packages = computed<ServicePackage[]>(() => page.props.packages ?? []);
const selectedPackage = ref<string | null>('compleet');

const selectedPackageData = computed<ServicePackage | null>(() => {
    if (!selectedPackage.value) {
        return null;
    }

    return packages.value.find((p) => p.slug === selectedPackage.value) ?? null;
});

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

const dateFormatter = new Intl.DateTimeFormat('nl-NL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

function formatEuro(value: number): string {
    return euroFormatter.format(value);
}

function formatDate(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    const date = new Date(iso);

    return Number.isNaN(date.getTime()) ? '—' : dateFormatter.format(date);
}

// Lokale check zodat we de RDW niet bestoken tijdens typen. Nederlandse
// kentekens hebben altijd 6 tekens; met minder zou een half getypt kenteken
// al een "niet gevonden"-fout geven.
function isLikelyValidKenteken(input: string): boolean {
    const normalized = input.replace(/[\s-]+/g, '').toUpperCase();

    return (
        /^[A-Z0-9]{6,8}$/.test(normalized) &&
        /[A-Z]/.test(normalized) &&
        /[0-9]/.test(normalized)
    );
}

let recalcTimer: ReturnType<typeof setTimeout> | null = null;
// Monotonic seq beschermt tegen out-of-order responses: alleen het
// resultaat van de laatste fetch wordt toegepast.
let requestSeq = 0;

async function performRecalc() {
    const trimmed = kenteken.value.trim();

    if (!isLikelyValidKenteken(trimmed)) {
        // Onvolledig of ongeldig formaat — stille reset; geen errors tijdens typen.
        result.value = null;
        errorMessage.value = null;
        notFound.value = false;
        loading.value = false;

        return;
    }

    loading.value = true;
    errorMessage.value = null;
    notFound.value = false;
    const seq = ++requestSeq;

    try {
        const data = await postJson<VehicleResponse>('/api/lookup', {
            kenteken: trimmed,
            residency_change: residencyChange.value,
            autonomia: autonomia.value,
        });

        if (seq !== requestSeq) {
            return;
        }

        const isNewVehicle = result.value?.kenteken !== data.kenteken;
        result.value = data;

        if (isNewVehicle) {
            onNewResult(data);
        }
    } catch (raw) {
        if (seq !== requestSeq) {
            return;
        }

        const e = raw as ApiError<ValidationErrorBody & NotFoundBody>;

        if (e.status === 422) {
            errorMessage.value =
                e.data?.errors?.kenteken?.[0] ?? 'Ongeldig kenteken-formaat.';
        } else if (e.status === 404) {
            errorMessage.value =
                e.data?.message ?? 'Geen voertuig gevonden bij dit kenteken.';
            notFound.value = true;
            result.value = null;
        } else {
            errorMessage.value =
                'Er ging iets mis bij het ophalen van de gegevens. Probeer het opnieuw.';
        }
    } finally {
        if (seq === requestSeq) {
            loading.value = false;
        }
    }
}

function scheduleRecalc() {
    if (recalcTimer) {
        clearTimeout(recalcTimer);
    }

    recalcTimer = setTimeout(() => {
        recalcTimer = null;
        performRecalc();
    }, 300);
}

// Enter-toets in de input → meteen uitvoeren in plaats van wachten op debounce.
function flushRecalc() {
    if (recalcTimer) {
        clearTimeout(recalcTimer);
        recalcTimer = null;
    }

    if (!isLikelyValidKenteken(kenteken.value.trim())) {
        showFormatHint();

        return;
    }

    performRecalc();
}

// Alleen na Enter, de knop of het verlaten van het veld: tijdens het typen
// blijft een half kenteken stil.
function showFormatHint() {
    if (kenteken.value.trim() === '') {
        return;
    }

    if (!isLikelyValidKenteken(kenteken.value.trim())) {
        errorMessage.value =
            'Een Nederlands kenteken heeft 6 letters en cijfers, bijvoorbeeld GV-412-X.';
        notFound.value = false;
    }
}

const prefersReducedMotion = () =>
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function scrollToElement(el: HTMLElement | null, focusFirstField = false) {
    if (!el) {
        return;
    }

    el.scrollIntoView({
        behavior: prefersReducedMotion() ? 'auto' : 'smooth',
        block: 'start',
    });

    if (focusFirstField) {
        el.querySelector<HTMLElement>('input, textarea, button')?.focus({
            preventScroll: true,
        });
    }
}

function scrollToQuote() {
    scrollToElement(quoteSection.value, true);
}

function scrollToPackages() {
    scrollToElement(packagesSection.value);
}

// Een bezoeker die via "Offerte" of de BPM-calculator binnenkomt, wil na
// het ophalen meteen bij het formulier uitkomen in plaats van bij de kaart.
let scrollToQuoteOnResult = false;

function onNewResult(data: VehicleResponse) {
    const vehicleName = [data.vehicle.merk, data.vehicle.handelsbenaming]
        .filter(Boolean)
        .join(' ');
    announcement.value = `Berekening klaar voor ${vehicleName || data.kenteken}.`;

    syncKentekenToUrl(data.kenteken);

    nextTick(() => {
        if (scrollToQuoteOnResult && quoteSection.value) {
            scrollToQuoteOnResult = false;
            scrollToQuote();

            return;
        }

        const top = resultSection.value?.getBoundingClientRect().top;
        const cameForResult = window.location.hash === '#resultaat';

        // Op mobiel valt het resultaat onder het toetsenbord; alleen scrollen
        // als de bovenkant niet al ruim in beeld staat.
        if (
            cameForResult ||
            (top !== undefined && top > window.innerHeight * 0.6)
        ) {
            scrollToElement(resultSection.value);
        }
    });
}

// Kenteken in de URL zodat herladen of terugkeren het resultaat behoudt.
// Inertia's history state blijft intact; alleen de query verandert.
function syncKentekenToUrl(value: string) {
    if (typeof window === 'undefined') {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('kenteken', value);
    window.history.replaceState(window.history.state, '', url);
}

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const fromUrl = params.get('kenteken');
    const wantsQuote = window.location.hash === '#offerte';
    const requestedPackage = params.get('pakket');

    // Tarieven links here with ?pakket=<slug> so the chosen package carries over.
    if (
        requestedPackage &&
        packages.value.some((p) => p.slug === requestedPackage)
    ) {
        selectedPackage.value = requestedPackage;
    }

    if (fromUrl) {
        scrollToQuoteOnResult = wantsQuote && !props.bpmOnly;
        kenteken.value = fromUrl;
    } else if (wantsQuote) {
        kentekenInput.value?.focus();
    }
});

const vehicleSummary = computed(() => {
    const r = result.value;

    if (!r) {
        return '';
    }

    // RDW levert merk en type in hoofdletters; toon ze als gewone namen.
    const name = [r.vehicle.merk, r.vehicle.handelsbenaming]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()
        .replace(/(^|[\s-])\p{L}/gu, (match) => match.toUpperCase());
    const year = r.vehicle.datum_eerste_toelating?.slice(0, 4);
    const fuel = r.fuel?.brandstof?.toLowerCase();
    const co2Text = co2.value != null ? `${co2.value} g/km CO₂` : null;

    return [name, fuel, year, co2Text].filter(Boolean).join(' · ');
});

// Vanuit de BPM-calculator naar het totaalantwoord op de homepage, niet
// meteen naar het formulier: de bezoeker vroeg om de Spaanse kosten.
const totalCostsHref = computed(
    () =>
        `/?kenteken=${encodeURIComponent(result.value?.kenteken ?? '')}#resultaat`,
);

watch([kenteken, residencyChoice, ownershipConfirmed, autonomia], () =>
    scheduleRecalc(),
);

onBeforeUnmount(() => {
    if (recalcTimer) {
        clearTimeout(recalcTimer);
    }
});
</script>

<template>
    <div class="w-full">
        <div class="mx-auto max-w-3xl">
            <form
                :id="result?.found && !props.bpmOnly ? undefined : 'offerte'"
                class="flex scroll-mt-24 items-stretch overflow-hidden rounded-2xl border border-input bg-plate shadow-sm focus-within:ring-2 focus-within:ring-ring/40 has-[[aria-invalid=true]]:border-destructive has-[[aria-invalid=true]]:ring-1 has-[[aria-invalid=true]]:ring-destructive"
                role="search"
                @submit.prevent="flushRecalc"
            >
                <div
                    class="flex items-center bg-[#003399] px-3 text-[11px] font-bold tracking-widest text-white uppercase"
                    aria-hidden="true"
                >
                    NL
                </div>
                <input
                    ref="kentekenInput"
                    v-model="kenteken"
                    type="text"
                    inputmode="text"
                    autocapitalize="characters"
                    autocomplete="off"
                    enterkeyhint="search"
                    spellcheck="false"
                    aria-label="Nederlands kenteken"
                    :aria-invalid="errorMessage ? true : undefined"
                    aria-describedby="kenteken-help"
                    placeholder="Bijv. GV-412-X"
                    class="min-w-0 flex-1 bg-plate px-4 py-4 font-display text-2xl tracking-wider text-plate-foreground caret-[#003399] placeholder:font-sans placeholder:text-base placeholder:font-normal placeholder:tracking-normal placeholder:text-plate-muted focus:outline-none"
                    @blur="showFormatHint"
                />
                <div class="flex items-center p-2">
                    <button
                        type="submit"
                        class="inline-flex h-full min-h-11 items-center justify-center gap-2 rounded-xl bg-accent px-4 text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none sm:px-5"
                        :aria-busy="loading"
                    >
                        <Loader2
                            v-if="loading"
                            class="size-4 animate-spin"
                            aria-hidden="true"
                        />
                        Bereken
                    </button>
                </div>
            </form>

            <p id="kenteken-help" class="mt-3 text-sm text-muted-foreground">
                De gegevens komen rechtstreeks van de RDW. Streepjes en spaties
                mag u weglaten.
            </p>

            <p class="sr-only" aria-live="polite">{{ announcement }}</p>

            <!-- ERROR -->
            <div
                v-if="errorMessage"
                class="mt-6 rounded-xl border border-destructive/40 bg-card p-4 text-sm"
                role="alert"
            >
                <div class="flex items-start gap-3 text-destructive">
                    <AlertCircle class="mt-0.5 size-5 shrink-0" />
                    <p class="font-medium">{{ errorMessage }}</p>
                </div>
                <div v-if="notFound" class="mt-3 pl-8 text-foreground">
                    <p class="text-muted-foreground">
                        Controleer of het kenteken klopt. Staat de auto nog niet
                        (of niet meer) in het RDW-register? Dan rekenen we het
                        graag persoonlijk voor u uit.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                        <a
                            :href="WHATSAPP_URL"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex min-h-11 items-center gap-2 font-medium text-primary underline-offset-4 hover:underline"
                        >
                            <MessageCircle class="size-4" />
                            WhatsApp ons
                        </a>
                        <a
                            :href="`tel:${PHONE}`"
                            class="inline-flex min-h-11 items-center gap-2 font-medium text-primary underline-offset-4 hover:underline"
                        >
                            <Phone class="size-4" />
                            {{ PHONE_DISPLAY }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end max-w-3xl -->

        <!-- RESULT -->
        <div
            v-if="result?.found"
            ref="resultSection"
            class="mt-8 scroll-mt-24 space-y-6 md:space-y-8"
        >
            <!-- Volledige tool (homepage): één antwoord bovenaan, details inklapbaar -->
            <template v-if="!props.bpmOnly && result.import_costs">
                <ResultAnswerCard
                    v-if="selectedPackageData"
                    :kenteken="result.kenteken"
                    :vehicle-summary="vehicleSummary"
                    :selected-package="selectedPackageData"
                    :import-total-eur="result.import_costs.total_eur"
                    :bpm-available="result.bpm !== null"
                    :bpm-eligible="result.bpm?.is_eligible ?? false"
                    :bpm-rest-eur="result.bpm?.rest_bpm_eur ?? 0"
                    :bpm-ineligible-reason="
                        result.bpm?.ineligible_reason ?? null
                    "
                    @request-quote="scrollToQuote"
                    @compare-packages="scrollToPackages"
                />

                <!-- Residency vragen — alleen als er IEDMT wordt geheven. -->
                <section
                    v-if="showResidencyControls"
                    class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                    aria-labelledby="residency-title"
                >
                    <h3
                        id="residency-title"
                        class="font-display text-xl font-semibold text-foreground"
                    >
                        Verhuist u permanent naar Spanje?
                    </h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Dan hoeft u vaak geen Spaanse registratiebelasting
                        (IEDMT) te betalen. Kies uw situatie; de berekening past
                        zich direct aan.
                    </p>

                    <fieldset class="mt-5 space-y-3">
                        <legend class="sr-only">Uw situatie in Spanje</legend>

                        <label
                            v-for="option in [
                                {
                                    value: 'permanent',
                                    label: 'Ik verhuis permanent naar Spanje',
                                },
                                {
                                    value: 'second_home',
                                    label: 'Ik heb een tweede woning in Spanje, hoofdverblijf blijft Nederland',
                                },
                                {
                                    value: 'other',
                                    label: 'Anders of weet ik nog niet',
                                },
                            ]"
                            :key="option.value"
                            class="group flex min-h-[3.25rem] cursor-pointer items-center gap-3 rounded-xl border border-border bg-background p-4 transition-colors hover:bg-muted has-[:checked]:border-accent has-[:checked]:bg-accent/5 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent/40"
                        >
                            <input
                                v-model="residencyChoice"
                                type="radio"
                                name="residency_choice"
                                :value="option.value"
                                class="peer sr-only"
                            />
                            <span
                                class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 border-border transition-colors group-hover:border-muted-foreground peer-checked:border-accent peer-checked:bg-accent"
                                aria-hidden="true"
                            >
                                <Check
                                    class="size-3 text-white opacity-0 transition-opacity peer-checked:opacity-100"
                                    :stroke-width="3"
                                />
                            </span>
                            <span class="text-sm font-medium text-foreground">{{
                                option.label
                            }}</span>
                        </label>
                    </fieldset>

                    <div
                        v-if="residencyChoice === 'permanent'"
                        class="mt-4 flex items-start gap-3 rounded-xl border border-border bg-background p-4"
                    >
                        <Checkbox
                            id="ownership-confirmed"
                            :model-value="ownershipConfirmed"
                            @update:model-value="
                                (v) => (ownershipConfirmed = v === true)
                            "
                            class="mt-0.5"
                        />
                        <div class="flex-1">
                            <Label
                                for="ownership-confirmed"
                                class="flex items-center gap-1.5 font-medium text-foreground"
                            >
                                De auto staat al meer dan 6 maanden op mijn naam
                                <TooltipProvider :delay-duration="150">
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <button
                                                type="button"
                                                class="-m-1 inline-flex size-8 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:bg-muted focus-visible:text-foreground focus-visible:ring-2 focus-visible:ring-accent/50 focus-visible:outline-none"
                                                aria-label="Meer informatie over de 6-maanden eigenaarsplicht"
                                            >
                                                <HelpCircle class="size-4" />
                                            </button>
                                        </TooltipTrigger>
                                        <TooltipContent class="max-w-xs">
                                            Dit is een wettelijke voorwaarde
                                            voor de IEDMT-vrijstelling. Bewijs
                                            (RDW-uittreksel) wordt later in het
                                            traject opgevraagd.
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </Label>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Wij controleren dit later bij uw
                                offerte-aanvraag.
                            </p>
                        </div>
                    </div>

                    <!-- Hidden region field. TODO: omzetten naar dropdown bij uitbreiding
                     naar Costa Brava (Cataluña) of Costa Blanca (Valencia). -->
                    <input type="hidden" name="autonomia" :value="autonomia" />
                </section>

                <ResidencyExemptionHighlight
                    v-if="showExemptionHighlight"
                    :iedmt-savings-eur="iedmtSavings"
                />

                <details
                    class="group rounded-2xl border border-border bg-card shadow-sm"
                >
                    <summary
                        class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 rounded-2xl px-6 py-4 font-display text-lg font-semibold text-foreground transition-colors hover:bg-muted/50 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none [&::-webkit-details-marker]:hidden"
                    >
                        Bekijk de volledige berekening
                        <ChevronDown
                            class="size-5 shrink-0 text-muted-foreground transition-transform group-open:rotate-180"
                            aria-hidden="true"
                        />
                    </summary>
                    <div
                        class="grid gap-4 border-t border-border p-4 sm:p-6 lg:grid-cols-3 lg:items-start"
                    >
                        <article class="rounded-xl border border-border p-5">
                            <h3
                                class="font-display text-lg font-semibold text-foreground"
                            >
                                Uw auto
                            </h3>
                            <dl class="mt-4 grid grid-cols-2 gap-y-3 text-sm">
                                <dt class="text-muted-foreground">Soort</dt>
                                <dd class="text-right font-medium">
                                    {{ result.vehicle.voertuigsoort ?? '—' }}
                                </dd>
                                <dt class="text-muted-foreground">Brandstof</dt>
                                <dd class="text-right font-medium">
                                    {{ result.fuel?.brandstof ?? '—' }}
                                </dd>
                                <dt class="text-muted-foreground">
                                    CO₂-uitstoot
                                </dt>
                                <dd class="text-right font-medium">
                                    <span v-if="co2 != null"
                                        >{{ co2 }} g/km</span
                                    >
                                    <span v-else>—</span>
                                </dd>
                                <dt class="text-muted-foreground">
                                    Eerste toelating
                                </dt>
                                <dd class="text-right font-medium">
                                    {{
                                        formatDate(
                                            result.vehicle
                                                .datum_eerste_toelating,
                                        )
                                    }}
                                </dd>
                                <dt class="text-muted-foreground">Kleur</dt>
                                <dd class="text-right font-medium capitalize">
                                    {{
                                        result.vehicle.eerste_kleur?.toLowerCase() ??
                                        '—'
                                    }}
                                </dd>
                                <dt class="text-muted-foreground">
                                    Leeggewicht
                                </dt>
                                <dd class="text-right font-medium">
                                    <span
                                        v-if="
                                            result.vehicle
                                                .massa_ledig_voertuig != null
                                        "
                                    >
                                        {{
                                            result.vehicle.massa_ledig_voertuig
                                        }}
                                        kg
                                    </span>
                                    <span v-else>—</span>
                                </dd>
                            </dl>
                        </article>

                        <article
                            v-if="result.bpm"
                            class="rounded-xl border border-border p-5"
                        >
                            <h3
                                class="font-display text-lg font-semibold text-foreground"
                            >
                                BPM-teruggave uit Nederland
                            </h3>
                            <template v-if="result.bpm.is_eligible">
                                <p
                                    class="mt-2 font-display text-3xl font-semibold text-success tabular-nums"
                                >
                                    + {{ formatEuro(result.bpm.rest_bpm_eur) }}
                                </p>
                                <p class="mt-3 text-sm text-muted-foreground">
                                    Oorspronkelijke BPM
                                    {{ formatEuro(result.bpm.bruto_bpm_eur) }},
                                    daarvan is
                                    {{
                                        result.bpm.afschrijving_pct.toLocaleString(
                                            'nl-NL',
                                            {
                                                maximumFractionDigits: 1,
                                            },
                                        )
                                    }}% afgeschreven in
                                    {{ result.bpm.age_months }} maanden.
                                </p>
                                <p
                                    v-for="note in result.bpm.notes"
                                    :key="note"
                                    class="mt-3 flex items-start gap-2 rounded-lg bg-warning/15 p-3 text-sm text-foreground"
                                >
                                    <AlertTriangle
                                        class="mt-0.5 size-4 shrink-0 text-warning"
                                    />
                                    <span>{{ note }}</span>
                                </p>
                                <p
                                    class="mt-3 flex items-start gap-2 text-sm text-muted-foreground"
                                >
                                    <Info class="mt-0.5 size-4 shrink-0" />
                                    <span>
                                        Berekend met de vaste afschrijvingstabel
                                        van de Belastingdienst. Soms levert een
                                        koerslijst of taxatie meer op; bij de
                                        offerte rekenen we alle drie uit en
                                        kiezen de gunstigste. Aan deze indicatie
                                        kunt u geen rechten ontlenen.
                                    </span>
                                </p>
                            </template>
                            <p
                                v-else
                                class="mt-2 text-sm text-muted-foreground"
                            >
                                {{ result.bpm.ineligible_reason }}. We helpen u
                                nog steeds graag met de Spaanse kant: ITV,
                                kenteken en al het papierwerk.
                            </p>
                        </article>
                        <article
                            v-else
                            class="rounded-xl border border-border p-5"
                        >
                            <h3
                                class="font-display text-lg font-semibold text-foreground"
                            >
                                BPM-teruggave uit Nederland
                            </h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                De RDW gaf voor deze auto geen brandstof- of
                                CO₂-gegevens terug. Wij rekenen de BPM met de
                                hand voor u uit in de offerte.
                            </p>
                        </article>

                        <ImportCostsCard
                            :import-costs="result.import_costs"
                            :co2="co2"
                        />
                    </div>
                </details>

                <div
                    v-if="packages.length > 0"
                    ref="packagesSection"
                    class="scroll-mt-24"
                >
                    <PackageSelector
                        :packages="packages"
                        v-model:selected="selectedPackage"
                    />
                </div>

                <div
                    v-if="selectedPackageData"
                    id="offerte"
                    ref="quoteSection"
                    class="scroll-mt-24"
                >
                    <QuoteRequestForm
                        :kenteken="result.kenteken"
                        :selected-package="selectedPackageData"
                        :import-total-eur="result.import_costs.total_eur"
                        :bpm-eligible="result.bpm?.is_eligible ?? false"
                        :bpm-rest-eur="result.bpm?.rest_bpm_eur ?? 0"
                        :residency-change="residencyChange"
                        :autonomia="autonomia"
                    />
                </div>
            </template>

            <!-- BPM-calculator: één antwoordkaart met de teruggave -->
            <section
                v-else
                class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8"
                aria-labelledby="bpm-answer-title"
            >
                <p class="text-sm text-muted-foreground">
                    {{ vehicleSummary }}
                </p>

                <template v-if="!result.bpm">
                    <h2
                        id="bpm-answer-title"
                        class="mt-3 font-display text-xl font-semibold text-foreground"
                    >
                        We kunnen de BPM niet automatisch berekenen
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        De RDW gaf voor deze auto geen brandstof- of
                        CO₂-gegevens terug. Maikel rekent het graag met de hand
                        voor u uit.
                    </p>
                </template>
                <template v-else-if="!result.bpm.is_eligible">
                    <h2
                        id="bpm-answer-title"
                        class="mt-3 flex items-start gap-2 font-display text-xl font-semibold text-foreground"
                    >
                        <AlertTriangle
                            class="mt-1 size-5 shrink-0 text-warning"
                            aria-hidden="true"
                        />
                        Geen BPM-teruggave voor deze auto
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        {{ result.bpm.ineligible_reason }}. We helpen u nog
                        steeds graag met de Spaanse kant: ITV-keuring, kenteken
                        en al het papierwerk.
                    </p>
                </template>
                <template v-else>
                    <h2
                        id="bpm-answer-title"
                        class="mt-3 font-display text-xl font-semibold text-foreground"
                    >
                        Wat u terugkrijgt uit Nederland
                    </h2>
                    <p
                        class="mt-2 font-display text-5xl font-semibold text-success tabular-nums sm:text-6xl"
                    >
                        + {{ formatEuro(result.bpm.rest_bpm_eur) }}
                    </p>
                    <p class="mt-3 max-w-xl text-muted-foreground">
                        Oorspronkelijke BPM
                        {{ formatEuro(result.bpm.bruto_bpm_eur) }}, daarvan is
                        {{
                            result.bpm.afschrijving_pct.toLocaleString(
                                'nl-NL',
                                {
                                    maximumFractionDigits: 1,
                                },
                            )
                        }}% afgeschreven in {{ result.bpm.age_months }} maanden.
                        Berekend met de vaste afschrijvingstabel van de
                        Belastingdienst; bij de offerte rekenen we ook
                        koerslijst en taxatie door en kiezen de gunstigste. Aan
                        deze indicatie kunt u geen rechten ontlenen.
                    </p>
                    <p
                        v-for="note in result.bpm.notes"
                        :key="note"
                        class="mt-3 flex max-w-xl items-start gap-2 rounded-lg bg-warning/15 p-3 text-sm text-foreground"
                    >
                        <AlertTriangle
                            class="mt-0.5 size-4 shrink-0 text-warning"
                            aria-hidden="true"
                        />
                        <span>{{ note }}</span>
                    </p>
                </template>

                <div
                    class="mt-6 flex flex-col gap-4 border-t border-border pt-6 md:flex-row md:items-center md:justify-between"
                >
                    <Link
                        :href="totalCostsHref"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-accent px-5 py-2 text-center text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bekijk wat het hele traject u netto kost
                    </Link>
                    <div class="flex items-center gap-3 text-sm">
                        <img
                            src="/images/maikel.jpeg"
                            alt="Maikel"
                            width="40"
                            height="40"
                            loading="lazy"
                            class="size-10 rounded-full object-cover"
                        />
                        <div>
                            <p class="text-muted-foreground">
                                Vragen over dit bedrag?
                            </p>
                            <div class="flex flex-wrap gap-x-3">
                                <a
                                    :href="WHATSAPP_URL"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex min-h-11 items-center gap-1.5 font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    <MessageCircle class="size-4" />
                                    WhatsApp Maikel
                                </a>
                                <a
                                    :href="`tel:${PHONE}`"
                                    class="inline-flex min-h-11 items-center gap-1.5 font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    <Phone class="size-4" />
                                    {{ PHONE_DISPLAY }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
