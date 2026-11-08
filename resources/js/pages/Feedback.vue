<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { store } from '@/actions/App/Http/Controllers/LeadFeedbackController';

const props = defineProps<{
    token: string;
    firstName: string;
    submitted: boolean;
}>();

// Voorbeelden van redenen; helpen de bezoeker op weg zonder antwoorden voor te kauwen.
const tips = [
    'Kwam het door de prijs?',
    'Reageerden wij niet snel genoeg?',
    'Was er iets onduidelijk, bijvoorbeeld in de offerte of de kosten?',
    'Heeft u het zelf geregeld of een andere partij gekozen?',
    'Gaat u de auto toch niet invoeren?',
];

const form = useForm({ message: '' });
const isSent = ref(props.submitted);

function submit(): void {
    form.submit(store(props.token), {
        preserveScroll: true,
        onSuccess: () => {
            isSent.value = true;
            form.reset();
        },
    });
}
</script>

<template>
    <main class="min-h-svh bg-secondary/30">
        <div class="mx-auto max-w-xl px-4 pt-10 pb-16">
            <p
                class="text-center font-display text-lg font-semibold tracking-tight text-primary"
            >
                autoinvoeren<span class="text-accent">spanje</span>.nl
            </p>

            <div
                class="mt-6 rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8"
            >
                <div v-if="isSent" class="text-center" role="status">
                    <CheckCircle2
                        class="mx-auto size-10 text-success"
                        aria-hidden="true"
                    />
                    <h1
                        class="mt-4 font-display text-2xl font-semibold text-foreground"
                    >
                        Hartelijk dank
                    </h1>
                    <p class="mt-2 text-base text-muted-foreground">
                        Wij hebben uw bericht ontvangen. Daar gaan wij echt iets
                        mee doen.
                    </p>
                    <p class="mt-6 text-base text-foreground">
                        Sebastiaan Been
                    </p>
                </div>

                <template v-else>
                    <h1
                        class="font-display text-2xl leading-tight font-semibold text-balance text-foreground"
                    >
                        Waarom koos u niet voor ons?
                    </h1>
                    <p class="mt-3 text-base text-pretty text-muted-foreground">
                        Beste {{ props.firstName }}, wij willen onze service
                        graag verbeteren. Wilt u in een paar woorden vertellen
                        waarom u niet voor ons heeft gekozen? Elk antwoord
                        helpt.
                    </p>

                    <form class="mt-6 space-y-4" @submit.prevent="submit">
                        <div>
                            <label
                                for="feedback-message"
                                class="text-base font-medium text-foreground"
                            >
                                Uw bericht
                            </label>
                            <ul
                                id="feedback-tips"
                                class="mt-2 space-y-1 text-base text-muted-foreground"
                            >
                                <li
                                    v-for="tip in tips"
                                    :key="tip"
                                    class="flex gap-2"
                                >
                                    <span aria-hidden="true">·</span>
                                    {{ tip }}
                                </li>
                            </ul>
                            <textarea
                                id="feedback-message"
                                v-model="form.message"
                                rows="6"
                                required
                                maxlength="5000"
                                class="mt-3 block w-full resize-y rounded-xl border border-border bg-background px-4 py-3 text-base text-foreground placeholder:text-muted-foreground focus-visible:border-primary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none aria-invalid:border-destructive"
                                :aria-invalid="!!form.errors.message"
                                :aria-describedby="
                                    form.errors.message
                                        ? 'feedback-tips feedback-error'
                                        : 'feedback-tips'
                                "
                            />
                            <p
                                v-if="form.errors.message"
                                id="feedback-error"
                                class="mt-2 text-sm text-destructive"
                            >
                                {{ form.errors.message }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-accent py-3 text-base font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:opacity-60"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="size-4 animate-spin"
                                aria-hidden="true"
                            />
                            {{ form.processing ? 'Versturen...' : 'Verstuur' }}
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </main>
</template>
