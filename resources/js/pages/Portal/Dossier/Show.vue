<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DossierTimeline from '@/components/DossierTimeline.vue';
import DossierTabs from '@/components/DossierTabs.vue';

interface Dossier {
    id: number;
    status: string;
    kenteken: string;
    merk: string | null;
    model: string | null;
    pakket: string;
    bpm_indicatie_eur: number | null;
    service_fee_eur: number | null;
    started_at: string | null;
    completed_at: string | null;
    unread_admin_messages_count: number | null;
    urls: { show: string; documents: string; messages: string };
}

defineProps<{
    dossier: Dossier;
}>();

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

function formatEuro(v: number | null): string {
    return v == null ? '—' : euroFormatter.format(v);
}
function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? '—' : dateFormatter.format(d);
}
</script>

<template>
    <Head :title="`Dossier ${dossier.kenteken}`" />

    <div class="space-y-6 p-6">
        <DossierTabs :dossier="dossier" active="show" />

        <section class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8">
            <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                Huidige fase
            </div>
            <div class="mt-5">
                <DossierTimeline :status="dossier.status as any" />
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    Pakket en bedragen
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Pakket</dt>
                        <dd class="font-medium capitalize">{{ dossier.pakket.replace('_', ' ') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">BPM-teruggave (indicatie)</dt>
                        <dd class="font-medium tabular-nums">{{ formatEuro(dossier.bpm_indicatie_eur) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Servicebedrag</dt>
                        <dd class="font-medium tabular-nums">{{ formatEuro(dossier.service_fee_eur) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    Tijdlijn
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Gestart op</dt>
                        <dd class="font-medium">{{ formatDate(dossier.started_at) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Afgerond op</dt>
                        <dd class="font-medium">{{ formatDate(dossier.completed_at) }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </div>
</template>
