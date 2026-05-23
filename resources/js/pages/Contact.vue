<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Phone, Mail, MessageCircle, Clock, CheckCircle, AlertCircle, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const PHONE = '+31618969732';
const PHONE_DISPLAY = '+31 6 18 96 97 32';
const EMAIL = 'info@autoinvoerenspanje.nl';
const WHATSAPP_URL = `https://wa.me/${PHONE.replace('+', '')}`;

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const submitted = ref(false);

function submit() {
    form.post('/contact', {
        onSuccess: () => {
            submitted.value = true;
            form.reset();
        },
    });
}
</script>

<template>
    <PublicLayout
        title="Contact — autoinvoerenspanje.nl"
        description="Neem contact op met ons team. Bel, WhatsApp of stuur een bericht via het contactformulier."
    >
        <!-- Hero -->
        <section class="bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-20">
                <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground">
                    Nederlandstalige begeleiding
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Neem contact op
                </h1>
                <p class="mx-auto mt-4 max-w-xl text-lg text-muted-foreground">
                    Heeft u een vraag of wilt u een vrijblijvende offerte? Wij zijn bereikbaar via telefoon, WhatsApp of e-mail.
                </p>
            </div>
        </section>

        <!-- Contact info + formulier -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="grid gap-10 lg:grid-cols-2 lg:items-start">

                    <!-- Links: contactgegevens -->
                    <div class="space-y-8">
                        <div>
                            <h2 class="font-display text-2xl font-semibold text-foreground">
                                Directe contactopties
                            </h2>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Liever direct contact? Bel of app ons — wij spreken Nederlands.
                            </p>
                        </div>

                        <!-- Bel knop -->
                        <a
                            :href="`tel:${PHONE}`"
                            class="flex items-center gap-4 rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary hover:shadow-md"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <Phone class="size-6" />
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Bellen</div>
                                <div class="mt-0.5 font-display text-xl font-semibold text-foreground">{{ PHONE_DISPLAY }}</div>
                            </div>
                        </a>

                        <!-- WhatsApp knop -->
                        <a
                            :href="WHATSAPP_URL"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-4 rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-[#25D366] hover:shadow-md"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-[#25D366]/10 text-[#25D366]">
                                <MessageCircle class="size-6" />
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">WhatsApp</div>
                                <div class="mt-0.5 font-display text-xl font-semibold text-foreground">{{ PHONE_DISPLAY }}</div>
                            </div>
                        </a>

                        <!-- E-mail -->
                        <a
                            :href="`mailto:${EMAIL}`"
                            class="flex items-center gap-4 rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-accent hover:shadow-md"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-accent">
                                <Mail class="size-6" />
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">E-mail</div>
                                <div class="mt-0.5 font-display text-lg font-semibold text-foreground">{{ EMAIL }}</div>
                            </div>
                        </a>

                        <!-- Bereikbaarheid -->
                        <div class="rounded-2xl border border-border bg-card p-5">
                            <div class="flex items-center gap-2 text-sm font-semibold text-foreground">
                                <Clock class="size-4 text-muted-foreground" />
                                Bereikbaarheid
                            </div>
                            <ul class="mt-3 space-y-1.5 text-sm text-muted-foreground">
                                <li>Maandag – vrijdag: 09:00 – 18:00</li>
                                <li>Zaterdag: 10:00 – 14:00</li>
                                <li>Zondag: gesloten</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Rechts: formulier -->
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-sm">
                        <!-- Succes -->
                        <div
                            v-if="submitted"
                            class="flex flex-col items-center gap-4 py-8 text-center"
                        >
                            <CheckCircle class="size-12 text-success" />
                            <h3 class="font-display text-xl font-semibold text-foreground">Bericht ontvangen!</h3>
                            <p class="text-sm text-muted-foreground">
                                Bedankt voor uw bericht. Wij nemen binnen één werkdag contact met u op.
                            </p>
                            <button
                                type="button"
                                class="mt-2 text-sm font-medium text-accent hover:underline"
                                @click="submitted = false"
                            >
                                Nieuw bericht sturen
                            </button>
                        </div>

                        <!-- Formulier -->
                        <form v-else @submit.prevent="submit" novalidate class="space-y-5">
                            <div>
                                <h2 class="font-display text-xl font-semibold text-foreground">Stuur ons een bericht</h2>
                                <p class="mt-1 text-sm text-muted-foreground">Wij reageren binnen één werkdag.</p>
                            </div>

                            <!-- Naam -->
                            <div class="space-y-1.5">
                                <label for="name" class="text-sm font-medium text-foreground">Naam <span class="text-destructive">*</span></label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    autocomplete="name"
                                    class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                    :class="{ 'border-destructive': form.errors.name }"
                                    placeholder="Uw volledige naam"
                                />
                                <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                            </div>

                            <!-- E-mail -->
                            <div class="space-y-1.5">
                                <label for="email" class="text-sm font-medium text-foreground">E-mailadres <span class="text-destructive">*</span></label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                    :class="{ 'border-destructive': form.errors.email }"
                                    placeholder="uw@email.nl"
                                />
                                <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                            </div>

                            <!-- Telefoon -->
                            <div class="space-y-1.5">
                                <label for="phone" class="text-sm font-medium text-foreground">Telefoonnummer <span class="text-xs font-normal text-muted-foreground">(optioneel)</span></label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                    placeholder="+31 6 ..."
                                />
                            </div>

                            <!-- Onderwerp -->
                            <div class="space-y-1.5">
                                <label for="subject" class="text-sm font-medium text-foreground">Onderwerp <span class="text-xs font-normal text-muted-foreground">(optioneel)</span></label>
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                    placeholder="Waar gaat uw vraag over?"
                                />
                            </div>

                            <!-- Bericht -->
                            <div class="space-y-1.5">
                                <label for="message" class="text-sm font-medium text-foreground">Bericht <span class="text-destructive">*</span></label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="5"
                                    class="w-full resize-none rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                    :class="{ 'border-destructive': form.errors.message }"
                                    placeholder="Uw vraag of opmerking..."
                                />
                                <p v-if="form.errors.message" class="text-xs text-destructive">{{ form.errors.message }}</p>
                            </div>

                            <!-- Algemeen serverfout -->
                            <div
                                v-if="form.hasErrors && !form.errors.name && !form.errors.email && !form.errors.message"
                                class="flex items-start gap-2 rounded-xl border border-destructive/30 bg-destructive/5 p-3 text-xs text-destructive"
                            >
                                <AlertCircle class="mt-0.5 size-4 shrink-0" />
                                Er is iets misgegaan. Probeer het opnieuw of stuur een e-mail naar {{ EMAIL }}.
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-accent py-3 text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90 disabled:opacity-60"
                            >
                                <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                                {{ form.processing ? 'Versturen...' : 'Bericht versturen' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
