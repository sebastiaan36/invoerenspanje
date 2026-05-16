<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Send, MessageSquare, Paperclip, FileText, X } from 'lucide-vue-next';
import DossierTabs from '@/components/DossierTabs.vue';

interface Attachment {
    id: number;
    filename: string;
    mime_type: string;
    size_bytes: number;
    is_image: boolean;
    is_pdf: boolean;
    url: string;
}

interface Message {
    id: number;
    body: string;
    author_name: string;
    author_role: string;
    created_at: string | null;
    attachments: Attachment[];
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
    messages: Message[];
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm<{ body: string; attachments: File[] }>({
    body: '',
    attachments: [],
});

const MAX_FILES = 5;
const MAX_BYTES = 10 * 1024 * 1024;
const ALLOWED = /\.(jpe?g|png|webp|gif|pdf)$/i;

function onPick(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;
    form.attachments = [
        ...form.attachments,
        ...Array.from(input.files).filter((f) => ALLOWED.test(f.name) && f.size <= MAX_BYTES),
    ].slice(0, MAX_FILES);
    input.value = '';
}

function removeFile(idx: number) {
    form.attachments = form.attachments.filter((_, i) => i !== idx);
}

function submit() {
    form.post(props.dossier.urls.messages, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body', 'attachments');
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

const dateFormatter = new Intl.DateTimeFormat('nl-NL', {
    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
});

function formatTime(iso: string | null): string {
    if (!iso) return '';
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? '' : dateFormatter.format(d);
}

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} kB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<template>
    <Head :title="`Berichten — ${dossier.kenteken}`" />

    <div class="space-y-6 p-6">
        <DossierTabs :dossier="dossier" active="messages" />

        <section class="rounded-2xl border border-border bg-card shadow-sm">
            <div
                v-if="messages.length === 0"
                class="flex flex-col items-center gap-3 p-12 text-center text-muted-foreground"
            >
                <MessageSquare class="size-10" />
                <p class="text-sm">Nog geen berichten. Stel hieronder uw vraag.</p>
            </div>

            <ol v-else class="divide-y divide-border">
                <li
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex flex-col gap-2 p-5"
                    :class="msg.author_role === 'klant' ? 'items-end' : 'items-start'"
                >
                    <div
                        v-if="msg.body"
                        class="max-w-[85%] rounded-2xl px-4 py-3"
                        :class="msg.author_role === 'klant'
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-foreground'"
                    >
                        <p class="whitespace-pre-line text-sm leading-relaxed">{{ msg.body }}</p>
                    </div>

                    <div
                        v-if="msg.attachments.length"
                        class="flex max-w-[85%] flex-wrap gap-2"
                        :class="msg.author_role === 'klant' ? 'justify-end' : 'justify-start'"
                    >
                        <template v-for="a in msg.attachments" :key="a.id">
                            <a
                                v-if="a.is_image"
                                :href="a.url"
                                target="_blank"
                                rel="noopener"
                                class="block overflow-hidden rounded-xl border border-border bg-card hover:shadow-md"
                                :title="a.filename"
                            >
                                <img :src="a.url" :alt="a.filename" class="block max-h-48 max-w-[16rem] object-cover" loading="lazy" />
                            </a>
                            <a
                                v-else
                                :href="a.url"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm hover:bg-muted"
                                :title="a.filename"
                            >
                                <FileText class="size-4 text-accent" />
                                <span class="max-w-[14rem] truncate font-medium">{{ a.filename }}</span>
                                <span class="text-xs text-muted-foreground">{{ formatBytes(a.size_bytes) }}</span>
                            </a>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span class="font-medium">{{ msg.author_name }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ formatTime(msg.created_at) }}</span>
                        <span
                            v-if="msg.author_role === 'admin'"
                            class="rounded-full bg-accent/15 px-1.5 text-[10px] font-semibold uppercase tracking-wider text-accent"
                        >
                            Uitvoerder
                        </span>
                    </div>
                </li>
            </ol>
        </section>

        <form
            class="rounded-2xl border border-border bg-card p-5 shadow-sm"
            @submit.prevent="submit"
        >
            <label for="msg-body" class="text-sm font-medium text-foreground">Stuur een bericht</label>
            <textarea
                id="msg-body"
                v-model="form.body"
                rows="3"
                class="mt-2 block w-full rounded-md border border-input bg-card px-3 py-2 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                placeholder="Uw bericht voor de uitvoerder…"
                required
            ></textarea>
            <div v-if="form.errors.body" class="mt-2 text-sm text-destructive">{{ form.errors.body }}</div>

            <div v-if="form.attachments.length" class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="(file, idx) in form.attachments"
                    :key="idx"
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-muted px-3 py-1 text-xs"
                >
                    <FileText class="size-3.5 text-accent" />
                    <span class="max-w-[12rem] truncate">{{ file.name }}</span>
                    <span class="text-muted-foreground">{{ formatBytes(file.size) }}</span>
                    <button
                        type="button"
                        class="rounded-full text-muted-foreground hover:text-destructive"
                        :aria-label="`Verwijder ${file.name}`"
                        @click="removeFile(idx)"
                    >
                        <X class="size-3.5" />
                    </button>
                </span>
            </div>

            <div class="mt-3 flex items-center justify-between gap-3">
                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-border bg-card px-3 py-2 text-xs font-medium text-foreground hover:bg-muted">
                    <Paperclip class="size-3.5" />
                    Bijlage toevoegen
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.pdf"
                        multiple
                        class="hidden"
                        @change="onPick"
                    />
                </label>
                <button
                    type="submit"
                    :disabled="form.processing || !form.body.trim()"
                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-accent px-5 text-sm font-semibold text-accent-foreground hover:bg-accent/90 disabled:opacity-50"
                >
                    <Send class="size-4" />
                    {{ form.processing ? 'Versturen…' : 'Verstuur' }}
                </button>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">
                Max 5 bestanden van 10 MB. Toegestaan: JPG, PNG, WEBP, GIF, PDF.
            </p>
        </form>
    </div>
</template>
