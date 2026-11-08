<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    AlertCircle,
    Clock,
    Download,
    Trash2,
    Upload,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { destroy as destroyDocument } from '@/actions/App/Http/Controllers/Portal/DocumentController';
import DossierTabs from '@/components/DossierTabs.vue';
import type { DossierSummary } from '@/types/dossier';

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

const props = defineProps<{
    dossier: DossierSummary;
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

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

/** Id of the document being deleted; doubles as a guard against double clicks. */
const deletingId = ref<number | null>(null);
const deleteError = ref<string | null>(null);

function destroy(doc: DocumentRow) {
    if (deletingId.value !== null) {
        return;
    }

    if (!confirm(`Document "${doc.filename}" verwijderen?`)) {
        return;
    }

    deletingId.value = doc.id;
    deleteError.value = null;

    router.delete(
        destroyDocument({ dossier: props.dossier.id, document: doc.id }),
        {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
            // Returning false suppresses Inertia's default error modal.
            onHttpException: () => {
                deleteError.value = `"${doc.filename}" kon niet worden verwijderd. Mogelijk is het al beoordeeld; ververs de pagina of stuur ons een bericht.`;

                return false;
            },
            onNetworkError: () => {
                deleteError.value =
                    'Geen verbinding. Controleer uw internet en probeer het opnieuw.';

                return false;
            },
        },
    );
}

function formatBytes(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} kB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function statusInfo(status: string) {
    return (
        (
            {
                aangevraagd: {
                    label: 'Aangevraagd',
                    color: 'text-muted-foreground',
                    icon: Clock,
                },
                geupload: {
                    label: 'In review',
                    color: 'text-warning',
                    icon: Clock,
                },
                goedgekeurd: {
                    label: 'Goedgekeurd',
                    color: 'text-success',
                    icon: CheckCircle2,
                },
                afgekeurd: {
                    label: 'Afgekeurd',
                    color: 'text-destructive',
                    icon: XCircle,
                },
            } as const
        )[
            status as 'aangevraagd' | 'geupload' | 'goedgekeurd' | 'afgekeurd'
        ] ?? { label: status, color: 'text-muted-foreground', icon: Clock }
    );
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
            <h2 class="font-display text-lg font-semibold text-foreground">
                Nieuw document uploaden
            </h2>
            <form
                class="mt-4 grid gap-4 md:grid-cols-[1fr_2fr_auto] md:items-end"
                @submit.prevent="submit"
            >
                <div>
                    <label
                        for="doc-type"
                        class="text-sm font-medium text-foreground"
                        >Type</label
                    >
                    <select
                        id="doc-type"
                        v-model="form.type"
                        class="mt-1 h-10 w-full rounded-md border border-input bg-card px-3 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        required
                    >
                        <option
                            v-for="(label, key) in documentTypes"
                            :key="key"
                            :value="key"
                        >
                            {{ label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label
                        for="doc-file"
                        class="text-sm font-medium text-foreground"
                        >Bestand</label
                    >
                    <input
                        id="doc-file"
                        ref="fileInput"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="mt-1 block w-full rounded-md border border-input bg-card px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1 file:text-sm file:font-medium"
                        required
                        @change="onFileChange"
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        PDF, JPG of PNG — maximaal 10 MB.
                    </p>
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
            <div
                v-if="form.errors.type"
                role="alert"
                class="mt-3 flex items-center gap-2 text-sm text-destructive"
            >
                <AlertCircle class="size-4" />
                {{ form.errors.type }}
            </div>
            <div
                v-if="form.errors.file"
                role="alert"
                class="mt-3 flex items-center gap-2 text-sm text-destructive"
            >
                <AlertCircle class="size-4" />
                {{ form.errors.file }}
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-card shadow-sm">
            <div class="border-b border-border p-6">
                <h2 class="font-display text-lg font-semibold text-foreground">
                    Geüploade documenten
                </h2>
            </div>

            <div
                v-if="deleteError"
                role="alert"
                class="flex items-center gap-2 border-b border-border bg-destructive/5 p-4 text-sm text-destructive"
            >
                <AlertCircle class="size-4 shrink-0" />
                {{ deleteError }}
            </div>

            <div
                v-if="documents.length === 0"
                class="p-8 text-center text-sm text-muted-foreground"
            >
                Nog geen documenten geüpload.
            </div>

            <ul v-else class="divide-y divide-border">
                <li
                    v-for="doc in documents"
                    :key="doc.id"
                    class="flex flex-wrap items-center gap-4 p-5"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                {{ doc.type_label }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center gap-1 text-xs font-medium',
                                    statusInfo(doc.status).color,
                                ]"
                            >
                                <component
                                    :is="statusInfo(doc.status).icon"
                                    class="size-3.5"
                                />
                                {{ statusInfo(doc.status).label }}
                            </span>
                        </div>
                        <div
                            class="mt-1 truncate text-sm font-medium text-foreground"
                        >
                            {{ doc.filename }}
                        </div>
                        <div class="mt-1 text-xs text-muted-foreground">
                            {{ formatBytes(doc.size_bytes) }}
                        </div>
                        <div
                            v-if="doc.status === 'afgekeurd' && doc.review_note"
                            class="mt-2 rounded-md border border-destructive/30 bg-destructive/5 p-2 text-xs text-destructive"
                        >
                            <strong>Reden:</strong> {{ doc.review_note }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            :href="doc.download_url"
                            class="inline-flex h-8 items-center gap-1 rounded-md border border-border px-3 text-xs font-medium hover:bg-muted"
                        >
                            <Download class="size-3.5" />
                            Download
                        </a>
                        <button
                            v-if="doc.status === 'geupload'"
                            type="button"
                            :disabled="deletingId !== null"
                            :aria-busy="deletingId === doc.id"
                            class="inline-flex h-8 items-center gap-1 rounded-md border border-destructive/40 px-3 text-xs font-medium text-destructive hover:bg-destructive/5 disabled:opacity-50"
                            @click="destroy(doc)"
                        >
                            <Trash2 class="size-3.5" />
                            {{
                                deletingId === doc.id
                                    ? 'Verwijderen…'
                                    : 'Verwijder'
                            }}
                        </button>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</template>
