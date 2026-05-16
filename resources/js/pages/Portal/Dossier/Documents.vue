<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, Clock, Download, Trash2, Upload, XCircle } from 'lucide-vue-next';
import DossierTabs from '@/components/DossierTabs.vue';

interface DocumentRow {
    id: number;
    type: string;
    type_label: string;
    filename: string;
    size_bytes: number;
    status: string;
    review_note: string | null;
    created_at: string | null;
    reviewed_at: string | null;
    download_url: string;
}

interface Dossier {
    id: number;
    kenteken: string;
    merk: string | null;
    model: string | null;
    status: string;
    unread_admin_messages_count: number | null;
    urls: { show: string; documents: string; messages: string };
}

const props = defineProps<{
    dossier: Dossier;
    documents: DocumentRow[];
    documentTypes: Record<string, string>;
}>();

const page = usePage<{ flash?: { success?: string } }>();

const form = useForm<{ type: string; file: File | null }>({
    type: Object.keys(props.documentTypes)[0] ?? 'overig',
    file: null,
});

const fileInput = ref<HTMLInputElement | null>(null);

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    form.file = target.files?.[0] ?? null;
}

function submit() {
    form.post(props.dossier.urls.documents, {
        forceFormData: true,
        onSuccess: () => {
            form.reset('file');
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

function destroy(doc: DocumentRow) {
    if (!confirm(`Document "${doc.filename}" verwijderen?`)) return;
    useForm({}).delete(`${props.dossier.urls.documents}/${doc.id}`);
}

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} kB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function statusInfo(status: string) {
    return ({
        aangevraagd: { label: 'Aangevraagd', color: 'text-muted-foreground', icon: Clock },
        geupload: { label: 'In review', color: 'text-warning', icon: Clock },
        goedgekeurd: { label: 'Goedgekeurd', color: 'text-success', icon: CheckCircle2 },
        afgekeurd: { label: 'Afgekeurd', color: 'text-destructive', icon: XCircle },
    } as const)[status as 'aangevraagd' | 'geupload' | 'goedgekeurd' | 'afgekeurd']
        ?? { label: status, color: 'text-muted-foreground', icon: Clock };
}
</script>

<template>
    <Head :title="`Documenten — ${dossier.kenteken}`" />

    <div class="space-y-6 p-6">
        <DossierTabs :dossier="dossier" active="documents" />

        <div
            v-if="page.props.flash?.success"
            class="flex items-center gap-2 rounded-xl border border-success/30 bg-success/10 p-4 text-sm text-success"
        >
            <CheckCircle2 class="size-5" />
            {{ page.props.flash.success }}
        </div>

        <section class="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <h2 class="font-display text-lg font-semibold text-foreground">Nieuw document uploaden</h2>
            <form
                class="mt-4 grid gap-4 md:grid-cols-[1fr_2fr_auto] md:items-end"
                @submit.prevent="submit"
            >
                <div>
                    <label for="doc-type" class="text-sm font-medium text-foreground">Type</label>
                    <select
                        id="doc-type"
                        v-model="form.type"
                        class="mt-1 h-10 w-full rounded-md border border-input bg-card px-3 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                        required
                    >
                        <option v-for="(label, key) in documentTypes" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label for="doc-file" class="text-sm font-medium text-foreground">Bestand</label>
                    <input
                        id="doc-file"
                        ref="fileInput"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="mt-1 block w-full rounded-md border border-input bg-card px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1 file:text-sm file:font-medium"
                        required
                        @change="onFileChange"
                    />
                    <p class="mt-1 text-xs text-muted-foreground">PDF, JPG of PNG — maximaal 10 MB.</p>
                </div>
                <button
                    type="submit"
                    :disabled="form.processing || !form.file"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-accent px-5 text-sm font-semibold text-accent-foreground hover:bg-accent/90 disabled:opacity-50"
                >
                    <Upload class="size-4" />
                    {{ form.processing ? 'Uploaden…' : 'Upload' }}
                </button>
            </form>
            <div v-if="form.errors.file" class="mt-3 flex items-center gap-2 text-sm text-destructive">
                <AlertCircle class="size-4" />
                {{ form.errors.file }}
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-card shadow-sm">
            <div class="border-b border-border p-6">
                <h2 class="font-display text-lg font-semibold text-foreground">Geüploade documenten</h2>
            </div>

            <div v-if="documents.length === 0" class="p-8 text-center text-sm text-muted-foreground">
                Nog geen documenten geüpload.
            </div>

            <ul v-else class="divide-y divide-border">
                <li v-for="doc in documents" :key="doc.id" class="flex flex-wrap items-center gap-4 p-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                {{ doc.type_label }}
                            </span>
                            <span :class="['inline-flex items-center gap-1 text-xs font-medium', statusInfo(doc.status).color]">
                                <component :is="statusInfo(doc.status).icon" class="size-3.5" />
                                {{ statusInfo(doc.status).label }}
                            </span>
                        </div>
                        <div class="mt-1 truncate text-sm font-medium text-foreground">{{ doc.filename }}</div>
                        <div class="mt-1 text-xs text-muted-foreground">{{ formatBytes(doc.size_bytes) }}</div>
                        <div
                            v-if="doc.status === 'afgekeurd' && doc.review_note"
                            class="mt-2 rounded-md border border-destructive/30 bg-destructive/5 p-2 text-xs text-destructive"
                        >
                            <strong>Reden:</strong> {{ doc.review_note }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="doc.download_url" class="inline-flex h-8 items-center gap-1 rounded-md border border-border px-3 text-xs font-medium hover:bg-muted">
                            <Download class="size-3.5" />
                            Download
                        </a>
                        <button
                            v-if="doc.status === 'geupload'"
                            type="button"
                            class="inline-flex h-8 items-center gap-1 rounded-md border border-destructive/40 px-3 text-xs font-medium text-destructive hover:bg-destructive/5"
                            @click="destroy(doc)"
                        >
                            <Trash2 class="size-3.5" />
                            Verwijder
                        </button>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</template>
