<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FolderOpen, Files, MessageSquare } from 'lucide-vue-next';

interface DossierUrls {
    show: string;
    documents: string;
    messages: string;
}

interface Dossier {
    id: number;
    kenteken: string;
    merk: string | null;
    model: string | null;
    status: string;
    unread_admin_messages_count?: number | null;
    urls: DossierUrls;
}

const props = defineProps<{
    dossier: Dossier;
    active: 'show' | 'documents' | 'messages';
}>();

const tabs = computed(() => [
    { key: 'show' as const, label: 'Overzicht', href: props.dossier.urls.show, icon: FolderOpen, badge: 0 },
    { key: 'documents' as const, label: 'Documenten', href: props.dossier.urls.documents, icon: Files, badge: 0 },
    {
        key: 'messages' as const,
        label: 'Berichten',
        href: props.dossier.urls.messages,
        icon: MessageSquare,
        badge: props.dossier.unread_admin_messages_count ?? 0,
    },
]);
</script>

<template>
    <header class="space-y-3 border-b border-border pb-4">
        <div class="flex items-baseline justify-between gap-4">
            <div>
                <Link
                    href="/portaal"
                    class="text-xs font-medium uppercase tracking-wider text-muted-foreground hover:text-primary"
                >
                    ← Mijn dossiers
                </Link>
                <h1 class="mt-1 font-display text-2xl font-semibold text-foreground sm:text-3xl">
                    {{ dossier.merk ?? 'Voertuig' }} {{ dossier.model ?? '' }}
                    · <span class="font-mono text-accent">{{ dossier.kenteken }}</span>
                </h1>
            </div>
        </div>

        <nav class="flex gap-1 overflow-x-auto" aria-label="Dossier tabs">
            <Link
                v-for="tab in tabs"
                :key="tab.key"
                :href="tab.href"
                class="inline-flex items-center gap-2 rounded-t-md border-b-2 px-3 py-2 text-sm font-medium transition-colors"
                :class="active === tab.key
                    ? 'border-accent text-foreground'
                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
            >
                <component :is="tab.icon" class="size-4" />
                {{ tab.label }}
                <span
                    v-if="tab.badge > 0"
                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-xs font-semibold text-accent-foreground"
                >
                    {{ tab.badge }}
                </span>
            </Link>
        </nav>
    </header>
</template>
