<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FolderPlus, MessageSquare } from 'lucide-vue-next';
import DossierTimeline from '@/components/DossierTimeline.vue';

interface DossierSummary {
    id: number;
    status: string;
    kenteken: string;
    merk: string | null;
    model: string | null;
    pakket: string;
    unread_admin_messages_count: number | null;
    created_at: string | null;
    urls: { show: string; documents: string; messages: string };
}

defineProps<{
    dossiers: DossierSummary[];
}>();

const dateFormatter = new Intl.DateTimeFormat('nl-NL', { day: 'numeric', month: 'long', year: 'numeric' });
function formatDate(iso: string | null): string {
    if (!iso) {
return '';
}

    const d = new Date(iso);

    return Number.isNaN(d.getTime()) ? '' : dateFormatter.format(d);
}
</script>

<template>
    <Head title="Mijn dossiers" />

    <div class="space-y-6 p-6">
        <header>
            <h1 class="font-display text-3xl font-semibold text-foreground">Mijn dossiers</h1>
            <p class="mt-1 text-muted-foreground">
                Overzicht van alle voertuigen die u via ons importeert.
                {{ dossiers.length === 1 ? 'U heeft 1 lopend dossier.' : `U heeft ${dossiers.length} dossiers.` }}
            </p>
        </header>

        <section
            v-if="dossiers.length === 0"
            class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-border bg-card p-10 text-center"
        >
            <FolderPlus class="size-10 text-muted-foreground" />
            <p class="text-muted-foreground">
                Er is nog geen dossier aan uw account gekoppeld. Zodra wij uw aanvraag
                bevestigen, verschijnt deze hier.
            </p>
        </section>

        <section v-else class="grid gap-4 lg:grid-cols-2">
            <Link
                v-for="d in dossiers"
                :key="d.id"
                :href="d.urls.show"
                class="group relative flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                            Dossier #{{ d.id }}
                        </div>
                        <h2 class="mt-1 font-display text-xl font-semibold text-foreground group-hover:text-accent">
                            {{ d.merk ?? 'Voertuig' }} {{ d.model ?? '' }}
                        </h2>
                        <div class="mt-1 font-mono text-sm text-muted-foreground">{{ d.kenteken }}</div>
                    </div>
                    <span
                        v-if="(d.unread_admin_messages_count ?? 0) > 0"
                        class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-1 text-xs font-semibold text-accent-foreground"
                        :title="`${d.unread_admin_messages_count} ongelezen berichten`"
                    >
                        <MessageSquare class="size-3" />
                        {{ d.unread_admin_messages_count }}
                    </span>
                </div>

                <div class="mt-5">
                    <DossierTimeline :status="d.status as any" />
                </div>

                <div class="mt-5 flex items-center justify-between text-xs text-muted-foreground">
                    <span>Pakket: <strong class="text-foreground">{{ d.pakket }}</strong></span>
                    <span v-if="d.created_at">aangemaakt {{ formatDate(d.created_at) }}</span>
                </div>
            </Link>
        </section>
    </div>
</template>
