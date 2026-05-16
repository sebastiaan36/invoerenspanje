<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { Send, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { postJson, isApiError, isNetworkError } from '@/lib/api';
import type { ServicePackage } from '@/components/PackageSelector.vue';

const props = defineProps<{
    kenteken: string;
    selectedPackage: ServicePackage;
    importTotalEur: number;
    bpmEligible: boolean;
    bpmRestEur: number;
    residencyChange: boolean;
    autonomia: string;
}>();

const form = reactive({
    name: '',
    email: '',
    phone: '',
    regio: '',
    expected_move_date: '',
    comment: '',
    // Honeypot — moet leeg blijven; bots vullen 'em vaak vrolijk in.
    website: '',
});

const utm = reactive({ source: '', medium: '', campaign: '' });

onMounted(() => {
    if (typeof window === 'undefined') return;
    const params = new URLSearchParams(window.location.search);
    utm.source = params.get('utm_source') ?? '';
    utm.medium = params.get('utm_medium') ?? '';
    utm.campaign = params.get('utm_campaign') ?? '';
});

const totaalprijs = computed(() => props.selectedPackage.price_eur + props.importTotalEur);
const bpmIndicatie = computed(() => (props.bpmEligible ? props.bpmRestEur : 0));

interface LeadResponse {
    ok: true;
    lead_id: number;
    reference: string;
}

interface ValidationErrorBody {
    errors?: Record<string, string[]>;
    message?: string;
}

const isSubmitting = ref(false);
const submitError = ref<string | null>(null);
const successReference = ref<string | null>(null);

function buildPayload() {
    return {
        // Form-velden
        name: form.name,
        email: form.email,
        phone: form.phone,
        regio: form.regio,
        expected_move_date: form.expected_move_date,
        comment: form.comment,
        website: form.website, // honeypot
        // Verborgen velden
        kenteken: props.kenteken,
        package_slug: props.selectedPackage.slug,
        residency_change: props.residencyChange,
        autonomia: props.autonomia,
        bpm_teruggave_indicatie: bpmIndicatie.value,
        import_kosten_indicatie: props.importTotalEur,
        totaalprijs_indicatie: totaalprijs.value,
        utm_source: utm.source,
        utm_medium: utm.medium,
        utm_campaign: utm.campaign,
    };
}

async function submit() {
    if (isSubmitting.value || successReference.value) return;
    isSubmitting.value = true;
    submitError.value = null;

    try {
        const response = await postJson<LeadResponse>('/api/leads', buildPayload());
        successReference.value = response.reference;
    } catch (raw) {
        if (isNetworkError(raw)) {
            submitError.value = 'Geen verbinding met de server. Controleer uw internet en probeer opnieuw.';
        } else if (isApiError<ValidationErrorBody>(raw)) {
            if (raw.status === 422) {
                const firstError = Object.values(raw.data?.errors ?? {})[0]?.[0];
                submitError.value = firstError ?? 'Controleer de ingevulde velden en probeer opnieuw.';
            } else if (raw.status === 429) {
                submitError.value = 'Te veel aanvragen vanaf uw locatie. Wacht een minuut en probeer opnieuw.';
            } else {
                submitError.value = 'Er ging iets mis bij het versturen. Probeer het zo opnieuw.';
            }
        } else {
            submitError.value = 'Er ging iets mis bij het versturen. Probeer het zo opnieuw.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <section
        class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8"
        aria-labelledby="quote-form-title"
    >
        <!-- Success-state na geslaagde submit -->
        <div v-if="successReference" class="text-center" role="status">
            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-success/15 text-success">
                <CheckCircle2 class="size-8" />
            </div>
            <h3 class="mt-4 font-display text-2xl font-semibold text-foreground sm:text-3xl">
                Bedankt — uw aanvraag is binnen
            </h3>
            <p class="mx-auto mt-3 max-w-md text-sm text-muted-foreground">
                We nemen binnen 24 uur contact op via {{ form.email }} of {{ form.phone }}.
                Een bevestiging is naar uw mailbox verstuurd.
            </p>
            <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1 text-xs font-medium text-foreground">
                Referentie:
                <span class="font-display tabular-nums text-primary">{{ successReference }}</span>
            </p>
        </div>

        <template v-else>
        <header>
            <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                Stap 3 van 3 — uw gegevens
            </div>
            <h3
                id="quote-form-title"
                class="mt-1 font-display text-2xl font-semibold text-foreground sm:text-3xl"
            >
                Vraag een vrijblijvende offerte aan
            </h3>
            <p class="mt-2 text-sm text-muted-foreground">
                We nemen binnen 24 uur contact op met een definitieve berekening
                en uitleg van de volgende stappen.
            </p>
        </header>

        <form class="mt-6 space-y-5" @submit.prevent="submit" novalidate>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="space-y-1.5">
                    <Label for="qr-name">Naam <span class="text-destructive">*</span></Label>
                    <Input
                        id="qr-name"
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        required
                        placeholder="Voornaam Achternaam"
                    />
                </div>
                <div class="space-y-1.5">
                    <Label for="qr-email">E-mailadres <span class="text-destructive">*</span></Label>
                    <Input
                        id="qr-email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        placeholder="naam@voorbeeld.nl"
                    />
                </div>
                <div class="space-y-1.5">
                    <Label for="qr-phone">Telefoon <span class="text-destructive">*</span></Label>
                    <Input
                        id="qr-phone"
                        v-model="form.phone"
                        type="tel"
                        autocomplete="tel"
                        required
                        placeholder="+31 6 12345678"
                    />
                </div>
                <div class="space-y-1.5">
                    <Label for="qr-regio">
                        Woonplaats Spanje of beoogde regio
                        <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="qr-regio"
                        v-model="form.regio"
                        type="text"
                        autocomplete="address-level2"
                        required
                        placeholder="bv. Marbella, Costa del Sol"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <Label for="qr-date">Verwachte verhuisdatum (optioneel)</Label>
                <Input
                    id="qr-date"
                    v-model="form.expected_move_date"
                    type="text"
                    placeholder="bv. juni 2026"
                />
            </div>

            <!-- Honeypot — visueel verborgen, off-screen positioned i.p.v. display:none
                 zodat bots het wel zien en invullen, echte gebruikers niet. -->
            <div aria-hidden="true" class="pointer-events-none absolute -left-[9999px] top-0 size-px overflow-hidden">
                <label for="qr-website">Website (laat dit veld leeg)</label>
                <input
                    id="qr-website"
                    v-model="form.website"
                    type="text"
                    name="website"
                    tabindex="-1"
                    autocomplete="off"
                />
            </div>

            <div class="space-y-1.5">
                <Label for="qr-comment">Opmerking (optioneel)</Label>
                <textarea
                    id="qr-comment"
                    v-model="form.comment"
                    rows="4"
                    class="placeholder:text-muted-foreground border-input flex w-full min-w-0 rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                    placeholder="Bijzonderheden over uw situatie, vragen of wensen…"
                ></textarea>
            </div>

            <div
                v-if="submitError"
                class="flex items-start gap-3 rounded-xl border border-destructive/40 bg-destructive/5 p-4 text-sm text-destructive"
                role="alert"
            >
                <AlertCircle class="mt-0.5 size-5 shrink-0" />
                <div>{{ submitError }}</div>
            </div>

            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-muted-foreground sm:max-w-xs">
                    Geen verplichtingen, geen verborgen kosten. Wij nemen binnen
                    24 uur contact met u op.
                </p>
                <Button
                    type="submit"
                    size="lg"
                    class="w-full bg-accent text-accent-foreground hover:bg-accent/90 sm:w-auto"
                    :disabled="isSubmitting"
                >
                    <Send v-if="!isSubmitting" class="size-4" />
                    <span
                        v-if="isSubmitting"
                        class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"
                        aria-hidden="true"
                    />
                    {{ isSubmitting ? 'Versturen…' : 'Vraag offerte aan' }}
                </Button>
            </div>
        </form>
        </template>
    </section>
</template>
