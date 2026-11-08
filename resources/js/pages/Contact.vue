<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    CheckCircle,
    Clock,
    Loader2,
    Mail,
    MessageCircle,
    Phone,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AccentHeading from '@/components/AccentHeading.vue';
import WerkgebiedKaart from '@/components/WerkgebiedKaart.vue';
import { usePageSeo } from '@/composables/usePageSeo';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { trackEvent } from '@/lib/analytics';
import {
    EMAIL,
    OPENING_HOURS,
    PHONE,
    PHONE_DISPLAY,
    WHATSAPP_URL,
} from '@/lib/contact';
import { store as storeContact } from '@/routes/contact';

interface Plaats {
    naam: string;
    lat: number;
    lng: number;
}

interface WerkgebiedData {
    accent: string;
    tiles: string;
    attribution: string;
    bounds: [[number, number], [number, number]];
    gebied: [number, number][];
    plaatsen: Plaats[];
    intro: string;
}

defineProps<{
    werkgebied: WerkgebiedData;
}>();

const seo = usePageSeo();

const team = [
    { name: 'Maikel', photo: '/images/maikel.jpeg' },
    { name: 'Sebastiaan', photo: '/images/sebastiaan.jpeg' },
];

const inputClass =
    'block min-h-11 w-full rounded-xl border border-border bg-background px-4 py-2.5 text-base text-foreground placeholder:text-muted-foreground focus-visible:border-primary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none aria-invalid:border-destructive aria-invalid:ring-destructive/20';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const submitted = ref(false);

/** Banner text for failures that aren't validation errors (429, 419, 500, offline). */
const generalError = ref<string | null>(null);

function messageForStatus(status: number): string {
    if (status === 429) {
        return 'U heeft net al een paar berichten gestuurd. Wacht een minuut en probeer het opnieuw.';
    }

    if (status === 419) {
        return 'Uw sessie is verlopen. Ververs de pagina en verstuur uw bericht opnieuw.';
    }

    return `Er is iets misgegaan bij het versturen. Probeer het opnieuw, of mail ons via ${EMAIL}.`;
}

function submit(): void {
    generalError.value = null;

    form.submit(storeContact(), {
        preserveScroll: true,
        onSuccess: () => {
            submitted.value = true;
            form.reset();
            trackEvent('generate_lead', { method: 'contact_form' });
        },
        // Returning false suppresses Inertia's default error modal; we show a banner instead.
        onHttpException: (response) => {
            generalError.value = messageForStatus(response.status);

            return false;
        },
        onNetworkError: () => {
            generalError.value = `Geen verbinding. Controleer uw internet en probeer het opnieuw, of mail ons via ${EMAIL}.`;

            return false;
        },
    });
}
</script>

<template>
    <PublicLayout>
        <!-- Hero -->
        <section class="bg-background">
            <div
                class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-20"
            >
                <AccentHeading
                    :text="seo.h1 ?? ''"
                    :accent="seo.h1_accent"
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                />
                <p
                    class="mx-auto mt-4 max-w-xl text-lg text-pretty text-muted-foreground"
                >
                    {{ seo.intro }}
                </p>
                <Link
                    href="/#offerte"
                    class="mt-4 inline-flex min-h-11 items-center gap-1.5 rounded-md px-1 font-medium text-accent underline-offset-4 hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                >
                    Direct weten wat het kost? Vul uw Nederlandse kenteken in
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </section>

        <!-- Contact info + formulier -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                    <!-- Links: contactgegevens -->
                    <div class="space-y-6">
                        <!-- Bel of WhatsApp -->
                        <div
                            class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                        >
                            <h2
                                class="font-display text-2xl font-semibold text-foreground"
                            >
                                Bel of WhatsApp
                            </h2>
                            <div class="mt-4 flex items-center gap-4">
                                <div class="flex shrink-0 -space-x-3">
                                    <img
                                        v-for="person in team"
                                        :key="person.name"
                                        :src="person.photo"
                                        :alt="person.name"
                                        width="56"
                                        height="56"
                                        loading="lazy"
                                        class="size-14 rounded-full border-2 border-card object-cover"
                                    />
                                </div>
                                <p class="text-muted-foreground">
                                    U spreekt met Maikel of Sebastiaan, gewoon
                                    in het Nederlands.
                                </p>
                            </div>
                            <p
                                class="mt-5 font-display text-xl font-semibold text-foreground"
                            >
                                {{ PHONE_DISPLAY }}
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a
                                    :href="WHATSAPP_URL"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                >
                                    <MessageCircle class="size-4" />
                                    WhatsApp ons
                                </a>
                                <a
                                    :href="`tel:${PHONE}`"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-sm font-medium text-foreground transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                >
                                    <Phone class="size-4" />
                                    Bel ons
                                </a>
                            </div>
                        </div>

                        <!-- E-mail -->
                        <a
                            :href="`mailto:${EMAIL}`"
                            class="flex min-h-11 items-center gap-4 rounded-2xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            <Mail
                                class="size-5 shrink-0 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <div class="min-w-0">
                                <p class="text-sm text-muted-foreground">
                                    E-mail
                                </p>
                                <p
                                    class="font-medium break-words text-foreground"
                                >
                                    {{ EMAIL }}
                                </p>
                            </div>
                        </a>

                        <!-- Bereikbaarheid -->
                        <div
                            class="rounded-2xl border border-border bg-card p-6"
                        >
                            <h2
                                class="flex items-center gap-2 font-semibold text-foreground"
                            >
                                <Clock
                                    class="size-4 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                Bereikbaarheid (Spaanse tijd)
                            </h2>
                            <ul class="mt-3 space-y-1.5 text-muted-foreground">
                                <li
                                    v-for="openingHours in OPENING_HOURS"
                                    :key="openingHours.days"
                                >
                                    {{ openingHours.days }}:
                                    {{ openingHours.hours }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Rechts: formulier -->
                    <div
                        class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8"
                    >
                        <!-- Succes -->
                        <div
                            v-if="submitted"
                            role="status"
                            aria-live="polite"
                            class="flex flex-col items-center gap-4 py-8 text-center"
                        >
                            <CheckCircle
                                class="size-12 text-success"
                                aria-hidden="true"
                            />
                            <h2
                                class="font-display text-2xl font-semibold text-foreground"
                            >
                                Bericht ontvangen
                            </h2>
                            <p class="max-w-sm text-muted-foreground">
                                Bedankt voor uw bericht. Wij reageren binnen één
                                werkdag. Heeft u haast? Bel of WhatsApp ons op
                                {{ PHONE_DISPLAY }}.
                            </p>
                            <button
                                type="button"
                                class="mt-2 inline-flex min-h-11 items-center justify-center rounded-xl border border-border bg-card px-5 text-sm font-medium text-foreground transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                @click="submitted = false"
                            >
                                Nieuw bericht sturen
                            </button>
                        </div>

                        <!-- Formulier -->
                        <form
                            v-else
                            novalidate
                            class="space-y-5"
                            @submit.prevent="submit"
                        >
                            <div>
                                <h2
                                    class="font-display text-2xl font-semibold text-foreground"
                                >
                                    Stuur ons een bericht
                                </h2>
                                <p class="mt-1 text-muted-foreground">
                                    Wij reageren binnen één werkdag.
                                </p>
                            </div>

                            <!-- Naam -->
                            <div class="space-y-1.5">
                                <label
                                    for="name"
                                    class="text-sm font-medium text-foreground"
                                    >Naam
                                    <span class="text-destructive"
                                        >*</span
                                    ></label
                                >
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    :class="inputClass"
                                    :aria-invalid="!!form.errors.name"
                                    :aria-describedby="
                                        form.errors.name
                                            ? 'name-error'
                                            : undefined
                                    "
                                    placeholder="Uw volledige naam"
                                />
                                <p
                                    v-if="form.errors.name"
                                    id="name-error"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- E-mail -->
                            <div class="space-y-1.5">
                                <label
                                    for="email"
                                    class="text-sm font-medium text-foreground"
                                    >E-mailadres
                                    <span class="text-destructive"
                                        >*</span
                                    ></label
                                >
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    :class="inputClass"
                                    :aria-invalid="!!form.errors.email"
                                    :aria-describedby="
                                        form.errors.email
                                            ? 'email-error'
                                            : undefined
                                    "
                                    placeholder="uw@email.nl"
                                />
                                <p
                                    v-if="form.errors.email"
                                    id="email-error"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Telefoon -->
                            <div class="space-y-1.5">
                                <label
                                    for="phone"
                                    class="text-sm font-medium text-foreground"
                                    >Telefoonnummer
                                    <span
                                        class="text-sm font-normal text-muted-foreground"
                                        >(optioneel)</span
                                    ></label
                                >
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    :class="inputClass"
                                    :aria-invalid="!!form.errors.phone"
                                    :aria-describedby="
                                        form.errors.phone
                                            ? 'phone-error'
                                            : undefined
                                    "
                                    placeholder="+31 6 ..."
                                />
                                <p
                                    v-if="form.errors.phone"
                                    id="phone-error"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Onderwerp -->
                            <div class="space-y-1.5">
                                <label
                                    for="subject"
                                    class="text-sm font-medium text-foreground"
                                    >Onderwerp
                                    <span
                                        class="text-sm font-normal text-muted-foreground"
                                        >(optioneel)</span
                                    ></label
                                >
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    :class="inputClass"
                                    :aria-invalid="!!form.errors.subject"
                                    :aria-describedby="
                                        form.errors.subject
                                            ? 'subject-error'
                                            : undefined
                                    "
                                    placeholder="Waar gaat uw vraag over?"
                                />
                                <p
                                    v-if="form.errors.subject"
                                    id="subject-error"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.subject }}
                                </p>
                            </div>

                            <!-- Bericht -->
                            <div class="space-y-1.5">
                                <label
                                    for="message"
                                    class="text-sm font-medium text-foreground"
                                    >Bericht
                                    <span class="text-destructive"
                                        >*</span
                                    ></label
                                >
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="5"
                                    required
                                    :class="[inputClass, 'resize-y']"
                                    :aria-invalid="!!form.errors.message"
                                    :aria-describedby="
                                        form.errors.message
                                            ? 'message-error'
                                            : undefined
                                    "
                                    placeholder="Uw vraag of opmerking..."
                                />
                                <p
                                    v-if="form.errors.message"
                                    id="message-error"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.message }}
                                </p>
                            </div>

                            <!-- Algemene serverfout -->
                            <div
                                v-if="
                                    generalError ||
                                    (form.hasErrors &&
                                        !form.errors.name &&
                                        !form.errors.email &&
                                        !form.errors.phone &&
                                        !form.errors.subject &&
                                        !form.errors.message)
                                "
                                role="alert"
                                class="flex items-start gap-2 rounded-xl border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive"
                            >
                                <AlertCircle
                                    class="mt-0.5 size-4 shrink-0"
                                    aria-hidden="true"
                                />
                                <span v-if="generalError">{{
                                    generalError
                                }}</span>
                                <span v-else>
                                    Er is iets misgegaan bij het versturen.
                                    Probeer het opnieuw, of mail ons via
                                    {{ EMAIL }}.
                                </span>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-accent py-3 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:opacity-60"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="size-4 animate-spin"
                                    aria-hidden="true"
                                />
                                {{
                                    form.processing
                                        ? 'Versturen...'
                                        : 'Bericht versturen'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Werkgebied -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="mb-8 max-w-2xl">
                    <h2
                        class="font-display text-2xl font-semibold text-foreground md:text-3xl"
                    >
                        Ons werkgebied
                    </h2>
                    <p class="mt-3 text-muted-foreground">
                        {{ werkgebied.intro }}
                    </p>
                </div>
                <WerkgebiedKaart :data="werkgebied" />
            </div>
        </section>
    </PublicLayout>
</template>
