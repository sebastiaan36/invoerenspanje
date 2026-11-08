<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Send,
    CheckCircle2,
    AlertCircle,
    MessageCircle,
    Phone,
} from 'lucide-vue-next';
import { onMounted, reactive, ref } from 'vue';
import type { ServicePackage } from '@/components/PackageSelector.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackEvent } from '@/lib/analytics';
import {
    postJson,
    isApiError,
    isNetworkError,
    sharedApiErrorMessage,
} from '@/lib/api';
import { PHONE, PHONE_DISPLAY, WHATSAPP_URL } from '@/lib/contact';
import { store as storeLead } from '@/routes/api/leads';

const props = withDefaults(
    defineProps<{
        kenteken: string;
        selectedPackage: ServicePackage;
        residencyChange: boolean;
        autonomia: string;
        /** True while the calculation above is outdated or recalculating. */
        disabled?: boolean;
    }>(),
    { disabled: false },
);

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
    if (typeof window === 'undefined') {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    utm.source = params.get('utm_source') ?? '';
    utm.medium = params.get('utm_medium') ?? '';
    utm.campaign = params.get('utm_campaign') ?? '';
});

interface LeadResponse {
    ok: true;
    lead_id: number;
    reference: string;
}

interface ValidationErrorBody {
    errors?: Record<string, string[]>;
    message?: string;
}

const VISIBLE_FIELDS = [
    'name',
    'email',
    'phone',
    'regio',
    'expected_move_date',
    'comment',
] as const;

type FieldName = (typeof VISIBLE_FIELDS)[number];

const isSubmitting = ref(false);
const submitError = ref<string | null>(null);
const successReference = ref<string | null>(null);
// Per-field messages from a 422, keyed by request field name.
const fieldErrors = ref<Record<string, string>>({});

function fieldError(field: FieldName): string | undefined {
    return fieldErrors.value[field];
}

function errorId(field: FieldName): string | undefined {
    return fieldErrors.value[field] ? `qr-${field}-error` : undefined;
}

/**
 * Errors on hidden fields (kenteken, pakket) have no input to sit next to,
 * so the first of those goes in the summary box instead.
 */
function validationSummary(errors: Record<string, string>): string {
    const hiddenFieldError = Object.entries(errors).find(
        ([field]) => !(VISIBLE_FIELDS as readonly string[]).includes(field),
    )?.[1];

    return hiddenFieldError ?? 'Controleer de gemarkeerde velden.';
}

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
        utm_source: utm.source,
        utm_medium: utm.medium,
        utm_campaign: utm.campaign,
    };
}

async function submit() {
    if (isSubmitting.value || successReference.value || props.disabled) {
        return;
    }

    isSubmitting.value = true;
    submitError.value = null;
    fieldErrors.value = {};

    try {
        const response = await postJson<LeadResponse>(
            storeLead.url(),
            buildPayload(),
        );
        successReference.value = response.reference;

        // GA4: mark a completed quote request as a lead conversion.
        trackEvent('generate_lead', {
            method: 'quote_form',
            package_slug: props.selectedPackage.slug,
        });
    } catch (raw) {
        if (isNetworkError(raw)) {
            submitError.value =
                'Geen verbinding met de server. Controleer uw internet en probeer opnieuw.';
        } else if (isApiError<ValidationErrorBody>(raw)) {
            const sharedMessage = sharedApiErrorMessage(raw);

            if (sharedMessage) {
                submitError.value = sharedMessage;
            } else if (raw.status === 422) {
                fieldErrors.value = Object.fromEntries(
                    Object.entries(raw.data?.errors ?? {}).map(
                        ([field, messages]) => [field, messages[0] ?? ''],
                    ),
                );
                submitError.value = validationSummary(fieldErrors.value);
            } else {
                submitError.value =
                    'Er ging iets mis bij het versturen. Probeer het zo opnieuw.';
            }
        } else {
            submitError.value =
                'Er ging iets mis bij het versturen. Probeer het zo opnieuw.';
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
            <div
                class="mx-auto flex size-14 items-center justify-center rounded-full bg-success/15 text-success"
            >
                <CheckCircle2 class="size-8" />
            </div>
            <h3
                class="mt-4 font-display text-2xl font-semibold text-foreground sm:text-3xl"
            >
                Bedankt — uw aanvraag is binnen
            </h3>
            <p class="mx-auto mt-3 max-w-md text-sm text-muted-foreground">
                We nemen binnen 24 uur contact op via {{ form.email }} of
                {{ form.phone }}. Een bevestiging is naar uw mailbox verstuurd.
            </p>
            <p
                class="mt-4 inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1 text-xs font-medium text-foreground"
            >
                Referentie:
                <span class="font-display text-primary tabular-nums">{{
                    successReference
                }}</span>
            </p>
        </div>

        <template v-else>
            <header
                class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between md:gap-8"
            >
                <div>
                    <h3
                        id="quote-form-title"
                        class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
                    >
                        Vraag een vrijblijvende offerte aan
                    </h3>
                    <p class="mt-2 max-w-lg text-base text-muted-foreground">
                        Maikel belt of mailt u binnen 24 uur met de definitieve
                        berekening voor pakket
                        <span class="font-medium text-foreground">{{
                            selectedPackage.name
                        }}</span>
                        en legt de volgende stappen uit.
                    </p>
                </div>
                <div
                    class="flex shrink-0 items-center gap-3 rounded-xl bg-muted/60 p-3 pr-4"
                >
                    <img
                        src="/images/maikel.jpeg"
                        alt="Maikel"
                        width="48"
                        height="48"
                        loading="lazy"
                        class="size-12 rounded-full object-cover"
                    />
                    <div class="text-sm">
                        <p class="font-medium text-foreground">
                            Liever direct even contact?
                        </p>
                        <div class="mt-0.5 flex flex-wrap gap-x-3">
                            <a
                                :href="WHATSAPP_URL"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex min-h-11 items-center gap-1.5 font-medium text-primary underline-offset-4 hover:underline"
                            >
                                <MessageCircle class="size-4" />
                                WhatsApp
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
            </header>

            <form class="mt-6 space-y-5" @submit.prevent="submit" novalidate>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="qr-name"
                            >Naam <span class="text-destructive">*</span></Label
                        >
                        <Input
                            class="h-11 text-base"
                            id="qr-name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            placeholder="Voornaam Achternaam"
                            :aria-invalid="
                                fieldError('name') ? true : undefined
                            "
                            :aria-describedby="errorId('name')"
                        />
                        <p
                            v-if="fieldError('name')"
                            :id="errorId('name')"
                            class="text-sm text-destructive"
                        >
                            {{ fieldError('name') }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="qr-email"
                            >E-mailadres
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            class="h-11 text-base"
                            id="qr-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="naam@voorbeeld.nl"
                            :aria-invalid="
                                fieldError('email') ? true : undefined
                            "
                            :aria-describedby="errorId('email')"
                        />
                        <p
                            v-if="fieldError('email')"
                            :id="errorId('email')"
                            class="text-sm text-destructive"
                        >
                            {{ fieldError('email') }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="qr-phone"
                            >Telefoon
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            class="h-11 text-base"
                            id="qr-phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                            required
                            placeholder="+31 6 12345678"
                            :aria-invalid="
                                fieldError('phone') ? true : undefined
                            "
                            :aria-describedby="errorId('phone')"
                        />
                        <p
                            v-if="fieldError('phone')"
                            :id="errorId('phone')"
                            class="text-sm text-destructive"
                        >
                            {{ fieldError('phone') }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="qr-regio">
                            Woonplaats Spanje of beoogde regio
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            class="h-11 text-base"
                            id="qr-regio"
                            v-model="form.regio"
                            type="text"
                            autocomplete="address-level2"
                            required
                            placeholder="bv. Marbella, Costa del Sol"
                            :aria-invalid="
                                fieldError('regio') ? true : undefined
                            "
                            :aria-describedby="errorId('regio')"
                        />
                        <p
                            v-if="fieldError('regio')"
                            :id="errorId('regio')"
                            class="text-sm text-destructive"
                        >
                            {{ fieldError('regio') }}
                        </p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label for="qr-date"
                        >Verwachte verhuisdatum (optioneel)</Label
                    >
                    <Input
                        class="h-11 text-base"
                        id="qr-date"
                        v-model="form.expected_move_date"
                        type="text"
                        placeholder="bv. juni 2026"
                        :aria-invalid="
                            fieldError('expected_move_date') ? true : undefined
                        "
                        :aria-describedby="errorId('expected_move_date')"
                    />
                    <p
                        v-if="fieldError('expected_move_date')"
                        :id="errorId('expected_move_date')"
                        class="text-sm text-destructive"
                    >
                        {{ fieldError('expected_move_date') }}
                    </p>
                </div>

                <!-- Honeypot — visueel verborgen, off-screen positioned i.p.v. display:none
                 zodat bots het wel zien en invullen, echte gebruikers niet. -->
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute top-0 -left-[9999px] size-px overflow-hidden"
                >
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
                        :aria-invalid="fieldError('comment') ? true : undefined"
                        :aria-describedby="errorId('comment')"
                        class="flex w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Bijzonderheden over uw situatie, vragen of wensen…"
                    ></textarea>
                    <p
                        v-if="fieldError('comment')"
                        :id="errorId('comment')"
                        class="text-sm text-destructive"
                    >
                        {{ fieldError('comment') }}
                    </p>
                </div>

                <div
                    v-if="submitError"
                    class="flex items-start gap-3 rounded-xl border border-destructive/40 bg-destructive/5 p-4 text-sm text-destructive"
                    role="alert"
                >
                    <AlertCircle class="mt-0.5 size-5 shrink-0" />
                    <div>{{ submitError }}</div>
                </div>

                <p
                    v-if="disabled && !isSubmitting"
                    id="qr-disabled-reason"
                    class="text-sm text-muted-foreground"
                >
                    Wacht tot de berekening hierboven is bijgewerkt voordat u de
                    aanvraag verstuurt.
                </p>

                <div
                    class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="space-y-1 text-sm text-muted-foreground sm:max-w-xs"
                    >
                        <p>
                            Vrijblijvend: u zit nergens aan vast tot u de
                            offerte akkoord geeft.
                        </p>
                        <p>
                            Wij gebruiken uw gegevens alleen om uw offerte op te
                            stellen en contact met u op te nemen. Lees onze
                            <Link
                                href="/privacy"
                                class="font-medium text-primary underline underline-offset-4 hover:text-primary/80"
                                >privacyverklaring</Link
                            >.
                        </p>
                    </div>
                    <Button
                        type="submit"
                        size="lg"
                        class="h-11 w-full bg-accent text-accent-foreground hover:bg-accent/90 sm:w-auto"
                        :disabled="isSubmitting || disabled"
                        :aria-describedby="
                            disabled ? 'qr-disabled-reason' : undefined
                        "
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
