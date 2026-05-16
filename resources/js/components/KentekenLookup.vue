<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loader2, AlertCircle, Info, AlertTriangle, HelpCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { postJson, type ApiError } from '@/lib/api';
import ImportCostsCard from '@/components/ImportCostsCard.vue';
import NetEffectBlock from '@/components/NetEffectBlock.vue';
import ResidencyExemptionHighlight from '@/components/ResidencyExemptionHighlight.vue';
import PackageSelector, { type ServicePackage } from '@/components/PackageSelector.vue';
import TotalSummary from '@/components/TotalSummary.vue';
import QuoteRequestForm from '@/components/QuoteRequestForm.vue';

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

const kenteken = ref('');
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const result = ref<VehicleResponse | null>(null);

const residencyChoice = ref<ResidencyChoice>('');
const ownershipConfirmed = ref(false);
const autonomia = ref(DEFAULT_AUTONOMIA);

const residencyChange = computed(
    () => residencyChoice.value === 'permanent' && ownershipConfirmed.value,
);

const co2 = computed<number | null>(() => result.value?.fuel?.co2_gecombineerd ?? null);

// Vragen alleen tonen wanneer er daadwerkelijk IEDMT wordt geheven —
// anders heeft de vrijstelling geen effect en is de vraag verwarrend.
// CO2 = 120 zit precies in de 0%-schijf, dus 'co2 >= 120' was te ruim.
const showResidencyControls = computed(() => {
    const rate = result.value?.import_costs?.iedmt_rate_pct;
    return typeof rate === 'number' && rate > 0;
});

const iedmtSavings = computed<number>(() => {
    const ic = result.value?.import_costs;
    if (!ic) return 0;
    return Math.max(0, ic.iedmt_without_exemption_eur - ic.iedmt_eur);
});

const showExemptionHighlight = computed(
    () => result.value?.import_costs?.iedmt_exempt === true && iedmtSavings.value > 0,
);

const page = usePage<{ packages: ServicePackage[] }>();
const packages = computed<ServicePackage[]>(() => page.props.packages ?? []);
const selectedPackage = ref<string | null>(null);

const selectedPackageData = computed<ServicePackage | null>(() => {
    if (!selectedPackage.value) return null;
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
    if (!iso) return '—';
    const date = new Date(iso);
    return Number.isNaN(date.getTime()) ? '—' : dateFormatter.format(date);
}

// Lokale check zodat we de RDW niet bestoken tijdens typen — spiegelt
// `KentekenNormalizer::isValidFormat` op de backend.
function isLikelyValidKenteken(input: string): boolean {
    const normalized = input.replace(/[\s-]+/g, '').toUpperCase();
    return /^[A-Z0-9]{5,8}$/.test(normalized)
        && /[A-Z]/.test(normalized)
        && /[0-9]/.test(normalized);
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
        loading.value = false;
        return;
    }

    loading.value = true;
    errorMessage.value = null;
    const seq = ++requestSeq;

    try {
        const data = await postJson<VehicleResponse>('/api/lookup', {
            kenteken: trimmed,
            residency_change: residencyChange.value,
            autonomia: autonomia.value,
        });
        if (seq !== requestSeq) return;
        result.value = data;
    } catch (raw) {
        if (seq !== requestSeq) return;
        const e = raw as ApiError<ValidationErrorBody & NotFoundBody>;
        if (e.status === 422) {
            errorMessage.value = e.data?.errors?.kenteken?.[0] ?? 'Ongeldig kenteken-formaat.';
        } else if (e.status === 404) {
            errorMessage.value = e.data?.message ?? 'Geen voertuig gevonden bij dit kenteken.';
            result.value = null;
        } else {
            errorMessage.value = 'Er ging iets mis bij het ophalen van de gegevens. Probeer het opnieuw.';
        }
    } finally {
        if (seq === requestSeq) loading.value = false;
    }
}

function scheduleRecalc() {
    if (recalcTimer) clearTimeout(recalcTimer);
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
    performRecalc();
}

watch(
    [kenteken, residencyChoice, ownershipConfirmed, autonomia],
    () => scheduleRecalc(),
);

onBeforeUnmount(() => {
    if (recalcTimer) clearTimeout(recalcTimer);
});
</script>

<template>
    <div class="w-full">
        <form
            class="flex items-stretch overflow-hidden rounded-2xl border border-border bg-card shadow-sm focus-within:ring-2 focus-within:ring-ring/40"
            @submit.prevent="flushRecalc"
        >
            <div
                class="flex items-center bg-[#003399] px-3 text-[10px] font-bold uppercase tracking-widest text-white"
            >
                NL
            </div>
            <input
                v-model="kenteken"
                type="text"
                inputmode="text"
                autocapitalize="characters"
                spellcheck="false"
                placeholder="Voer je kenteken in (bv. 12-ABC-3)"
                class="flex-1 bg-card px-4 py-4 font-display text-2xl tracking-wider text-foreground placeholder:text-base placeholder:font-sans placeholder:font-normal placeholder:tracking-normal placeholder:text-muted-foreground focus:outline-none"
            />
            <div
                class="flex w-12 items-center justify-center pr-2 text-muted-foreground"
                aria-live="polite"
            >
                <Loader2 v-if="loading" class="size-5 animate-spin text-accent" aria-label="Bezig met ophalen" />
            </div>
        </form>

        <p class="mt-3 text-xs text-muted-foreground">
            Gegevens komen rechtstreeks bij de RDW vandaan. De berekening werkt
            mee zodra je typt — streepjes en spaties mag je weglaten.
        </p>

        <!-- Residency vragen — alleen tonen na een geslaagde lookup en als CO2 >= 120 g/km. -->
        <section
            v-if="showResidencyControls"
            class="mt-6 rounded-2xl border border-border bg-card p-6 shadow-sm"
        >
            <header>
                <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                    Voor een nauwkeuriger indicatie
                </div>
                <h3 class="mt-1 font-display text-xl font-semibold text-foreground">
                    Hoe ziet uw situatie in Spanje eruit?
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    Bij verhuizing van uw vaste verblijfplaats naar Spanje is
                    IEDMT-vrijstelling vaak mogelijk — wij passen de berekening
                    direct aan zodra u kiest.
                </p>
            </header>

            <fieldset class="mt-5 space-y-3">
                <legend class="sr-only">Uw situatie in Spanje</legend>

                <label
                    v-for="option in [
                        { value: 'permanent', label: 'Ik verhuis permanent naar Spanje' },
                        { value: 'second_home', label: 'Ik heb een tweede woning in Spanje, hoofdverblijf blijft Nederland' },
                        { value: 'other', label: 'Anders of weet ik nog niet' },
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
                        class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 border-border transition-colors group-hover:border-muted-foreground peer-checked:border-accent"
                        aria-hidden="true"
                    >
                        <span
                            class="size-2.5 rounded-full bg-accent opacity-0 transition-opacity peer-checked:opacity-100"
                        />
                    </span>
                    <span class="text-sm font-medium text-foreground">{{ option.label }}</span>
                </label>
            </fieldset>

            <div
                v-if="residencyChoice === 'permanent'"
                class="mt-4 flex items-start gap-3 rounded-xl border border-accent/30 bg-accent/5 p-4"
            >
                <Checkbox
                    id="ownership-confirmed"
                    :model-value="ownershipConfirmed"
                    @update:model-value="(v) => (ownershipConfirmed = v === true)"
                    class="mt-0.5"
                />
                <div class="flex-1">
                    <Label for="ownership-confirmed" class="flex items-center gap-1.5 font-medium text-foreground">
                        De auto staat al meer dan 6 maanden op mijn naam
                        <TooltipProvider :delay-duration="150">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <button
                                        type="button"
                                        class="-m-1 inline-flex size-8 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:bg-muted focus-visible:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/50"
                                        aria-label="Meer informatie over de 6-maanden eigenaarsplicht"
                                    >
                                        <HelpCircle class="size-4" />
                                    </button>
                                </TooltipTrigger>
                                <TooltipContent class="max-w-xs">
                                    Dit is een wettelijke voorwaarde voor de IEDMT-vrijstelling.
                                    Bewijs (RDW-uittreksel) wordt later in het traject opgevraagd.
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </Label>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Zelfdeclaratie — kan door ons later geverifieerd worden bij de
                        offerte-aanvraag.
                    </p>
                </div>
            </div>

            <!-- Hidden region field. TODO: omzetten naar dropdown bij uitbreiding
                 naar Costa Brava (Cataluña) of Costa Blanca (Valencia). -->
            <input type="hidden" name="autonomia" :value="autonomia" />
        </section>

        <!-- ERROR -->
        <div
            v-if="errorMessage"
            class="mt-6 flex items-start gap-3 rounded-xl border border-destructive/40 bg-card p-4 text-sm text-destructive"
            role="alert"
        >
            <AlertCircle class="mt-0.5 size-5 shrink-0" />
            <div>{{ errorMessage }}</div>
        </div>

        <!-- RESULT -->
        <div v-if="result?.found" class="mt-6 space-y-6 md:mt-8 md:space-y-8">
            <ResidencyExemptionHighlight
                v-if="showExemptionHighlight"
                :iedmt-savings-eur="iedmtSavings"
            />

            <!-- Net effect summary on top -->
            <NetEffectBlock
                v-if="result.import_costs && result.net_effect_eur !== null"
                :net-effect-eur="result.net_effect_eur"
                :bpm-rest-eur="result.bpm?.is_eligible ? result.bpm.rest_bpm_eur : 0"
                :bpm-eligible="result.bpm?.is_eligible ?? false"
                :import-total-eur="result.import_costs.total_eur"
            />

            <div class="grid gap-4 lg:grid-cols-3">
            <article class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                    Voertuiggegevens
                </div>
                <h3 class="mt-1 font-display text-2xl font-semibold text-foreground">
                    {{ result.vehicle.merk }}
                    <span class="font-normal text-muted-foreground">
                        {{ result.vehicle.handelsbenaming }}
                    </span>
                </h3>

                <dl class="mt-5 grid grid-cols-2 gap-y-3 text-sm">
                    <dt class="text-muted-foreground">Soort</dt>
                    <dd class="text-right font-medium">{{ result.vehicle.voertuigsoort ?? '—' }}</dd>

                    <dt class="text-muted-foreground">Brandstof</dt>
                    <dd class="text-right font-medium">{{ result.fuel?.brandstof ?? '—' }}</dd>

                    <dt class="text-muted-foreground">CO₂ uitstoot</dt>
                    <dd class="text-right font-medium">
                        <span v-if="result.fuel?.co2_gecombineerd != null">
                            {{ result.fuel.co2_gecombineerd }} g/km
                        </span>
                        <span v-else>—</span>
                    </dd>

                    <dt class="text-muted-foreground">Eerste toelating</dt>
                    <dd class="text-right font-medium">
                        {{ formatDate(result.vehicle.datum_eerste_toelating) }}
                    </dd>

                    <dt class="text-muted-foreground">Kleur</dt>
                    <dd class="text-right font-medium capitalize">
                        {{ result.vehicle.eerste_kleur?.toLowerCase() ?? '—' }}
                    </dd>

                    <dt class="text-muted-foreground">Leeggewicht</dt>
                    <dd class="text-right font-medium">
                        <span v-if="result.vehicle.massa_ledig_voertuig != null">
                            {{ result.vehicle.massa_ledig_voertuig }} kg
                        </span>
                        <span v-else>—</span>
                    </dd>
                </dl>
            </article>

            <!-- BPM card — three states: ineligible / no data / eligible -->
            <article
                v-if="!result.bpm"
                class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm"
            >
                <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                    BPM-teruggave
                </div>
                <div class="mt-2 font-display text-2xl text-muted-foreground">
                    Onvoldoende data om te berekenen
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    De RDW gaf voor dit voertuig geen brandstof- of CO₂-gegevens terug.
                    Vraag een persoonlijke offerte aan voor een handmatige berekening.
                </p>
            </article>

            <article
                v-else-if="!result.bpm.is_eligible"
                class="flex flex-col rounded-2xl border border-warning/40 bg-card p-6 shadow-sm"
            >
                <div class="flex items-start gap-3">
                    <AlertTriangle class="mt-1 size-5 shrink-0 text-warning" />
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Geen BPM-teruggave mogelijk
                        </div>
                        <div class="mt-1 font-display text-xl font-semibold text-foreground">
                            {{ result.bpm.ineligible_reason }}
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-sm text-muted-foreground">
                    We helpen je nog steeds graag met de Spaanse importkant: ITV-keuring,
                    permiso de circulación en alle papierwerk.
                </p>
                <div class="mt-auto pt-5">
                    <Button
                        type="button"
                        size="lg"
                        class="w-full bg-primary text-primary-foreground hover:bg-primary/90"
                    >
                        Bekijk Spaanse import-pakket
                    </Button>
                </div>
            </article>

            <article
                v-else
                class="flex flex-col rounded-2xl bg-primary p-6 text-primary-foreground shadow-lg"
            >
                <div class="text-xs font-medium uppercase tracking-wider text-primary-foreground/70">
                    Indicatie BPM-teruggave (forfaitaire methode)
                </div>
                <div class="mt-1 font-display text-5xl font-semibold text-accent">
                    {{ formatEuro(result.bpm.rest_bpm_eur) }}
                </div>

                <p class="mt-3 text-sm text-primary-foreground/80">
                    Geschatte teruggave bij export naar Spanje. Bruto BPM:
                    <strong>{{ formatEuro(result.bpm.bruto_bpm_eur) }}</strong>
                    · afschrijving
                    <strong>{{ result.bpm.afschrijving_pct.toFixed(1) }}%</strong>
                    over {{ result.bpm.age_months }} maanden.
                </p>

                <div
                    v-for="note in result.bpm.notes"
                    :key="note"
                    class="mt-3 flex items-start gap-2 rounded-xl bg-warning/15 p-3 text-xs text-primary-foreground"
                >
                    <AlertTriangle class="mt-0.5 size-4 shrink-0 text-warning" />
                    <p>{{ note }}</p>
                </div>

                <div
                    class="mt-4 flex items-start gap-2 rounded-xl bg-primary-foreground/10 p-3 text-xs text-primary-foreground/80"
                >
                    <Info class="mt-0.5 size-4 shrink-0" />
                    <p>
                        Indicatieve berekening volgens de forfaitaire afschrijvingstabel.
                        In sommige gevallen levert de <strong>koerslijst</strong>- of
                        <strong>taxatierapport</strong>-methode meer op. Bij een offerte
                        rekent onze partner alle drie de methoden door en kiest de gunstigste.
                        Aan deze indicatie kunnen geen rechten worden ontleend.
                    </p>
                </div>

                <div class="mt-auto pt-5">
                    <Button
                        type="button"
                        size="lg"
                        class="w-full bg-accent text-accent-foreground hover:bg-accent/90"
                    >
                        Vraag persoonlijke offerte aan
                    </Button>
                </div>
            </article>

            <ImportCostsCard
                v-if="result.import_costs"
                :import-costs="result.import_costs"
                :co2="co2"
            />
            </div>

            <PackageSelector
                v-if="packages.length > 0"
                :packages="packages"
                v-model:selected="selectedPackage"
            />

            <TotalSummary
                v-if="selectedPackageData && result.import_costs"
                :selected-package="selectedPackageData"
                :import-total-eur="result.import_costs.total_eur"
                :bpm-eligible="result.bpm?.is_eligible ?? false"
                :bpm-rest-eur="result.bpm?.rest_bpm_eur ?? 0"
            />

            <QuoteRequestForm
                v-if="selectedPackageData && result.import_costs"
                :kenteken="result.kenteken"
                :selected-package="selectedPackageData"
                :import-total-eur="result.import_costs.total_eur"
                :bpm-eligible="result.bpm?.is_eligible ?? false"
                :bpm-rest-eur="result.bpm?.rest_bpm_eur ?? 0"
                :residency-change="residencyChange"
                :autonomia="autonomia"
            />
        </div>
    </div>
</template>
