<script setup lang="ts">
import { ref } from 'vue';
import { Loader2, Search, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { postJson, type ApiError } from '@/lib/api';

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

function reset() {
    errorMessage.value = null;
    result.value = null;
}

async function submit() {
    const trimmed = kenteken.value.trim();
    if (!trimmed || loading.value) return;

    loading.value = true;
    reset();

    try {
        result.value = await postJson<VehicleResponse>('/api/lookup', { kenteken: trimmed });
    } catch (raw) {
        const e = raw as ApiError<ValidationErrorBody & NotFoundBody>;
        if (e.status === 422) {
            errorMessage.value = e.data?.errors?.kenteken?.[0] ?? 'Ongeldig kenteken-formaat.';
        } else if (e.status === 404) {
            errorMessage.value = e.data?.message ?? 'Geen voertuig gevonden bij dit kenteken.';
        } else {
            errorMessage.value = 'Er ging iets mis bij het ophalen van de gegevens. Probeer het opnieuw.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="w-full">
        <form
            class="flex items-stretch overflow-hidden rounded-2xl border border-border bg-card shadow-sm focus-within:ring-2 focus-within:ring-ring/40"
            @submit.prevent="submit"
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
                :disabled="loading"
            />
            <Button
                type="submit"
                size="lg"
                class="m-2 rounded-xl bg-accent text-accent-foreground hover:bg-accent/90"
                :disabled="loading || !kenteken.trim()"
            >
                <Loader2 v-if="loading" class="size-4 animate-spin" />
                <Search v-else class="size-4" />
                {{ loading ? 'Ophalen…' : 'Bekijk indicatie' }}
            </Button>
        </form>

        <p class="mt-3 text-xs text-muted-foreground">
            Gegevens komen rechtstreeks bij de RDW vandaan. Streepjes en spaties mag je weglaten.
        </p>

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
        <div v-if="result?.found" class="mt-6 grid gap-4 lg:grid-cols-2">
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
        </div>
    </div>
</template>
