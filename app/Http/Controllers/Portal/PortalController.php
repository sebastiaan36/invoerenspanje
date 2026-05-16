<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Dossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PortalController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user = $request->user();

        $dossiers = $user
            ? $user->dossiers()
                ->withCount([
                    'messages as unread_admin_messages_count' => fn ($q) => $q
                        ->where('author_role', 'admin')
                        ->whereNull('read_at'),
                ])
                ->latest()
                ->get()
                ->map(fn (Dossier $d) => $this->summarizeDossier($d))
                ->all()
            : [];

        return Inertia::render('Portal/Dashboard', [
            'dossiers' => $dossiers,
        ]);
    }

    public function showDossier(Request $request, Dossier $dossier): Response
    {
        $this->authorizeDossier($request, $dossier);

        return Inertia::render('Portal/Dossier/Show', [
            'dossier' => $this->fullDossier($dossier),
        ]);
    }

    public function documents(Request $request, Dossier $dossier): Response
    {
        $this->authorizeDossier($request, $dossier);

        $documents = $dossier->documents()->latest()->get()->map(fn (Document $d) => [
            'id' => $d->id,
            'type' => $d->type,
            'type_label' => Document::TYPES[$d->type] ?? $d->type,
            'filename' => $d->filename,
            'size_bytes' => $d->size_bytes,
            'status' => $d->status,
            'review_note' => $d->review_note,
            'created_at' => $d->created_at?->toIso8601String(),
            'reviewed_at' => $d->reviewed_at?->toIso8601String(),
            'download_url' => route('portaal.documents.download', $d),
        ])->all();

        return Inertia::render('Portal/Dossier/Documents', [
            'dossier' => $this->summarizeDossier($dossier),
            'documents' => $documents,
            'documentTypes' => Document::TYPES,
        ]);
    }

    public function messages(Request $request, Dossier $dossier): Response
    {
        $this->authorizeDossier($request, $dossier);

        $messages = $dossier->messages()
            ->with(['author:id,name', 'attachments'])
            ->oldest()
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'author_name' => $m->author?->name ?? 'Onbekend',
                'author_role' => $m->author_role,
                'created_at' => $m->created_at?->toIso8601String(),
                'attachments' => $m->attachments->map(fn ($a) => [
                    'id' => $a->id,
                    'filename' => $a->filename,
                    'mime_type' => $a->mime_type,
                    'size_bytes' => $a->size_bytes,
                    'is_image' => $a->isImage(),
                    'is_pdf' => $a->isPdf(),
                    'url' => route('portaal.messages.attachments.download', $a),
                ])->values()->all(),
            ])
            ->all();

        $dossier->messages()
            ->where('author_role', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Portal/Dossier/Messages', [
            'dossier' => $this->summarizeDossier($dossier),
            'messages' => $messages,
        ]);
    }

    public function redirectToFirstDossier(Request $request): RedirectResponse
    {
        $first = $request->user()?->dossiers()->latest()->first();

        return $first
            ? redirect()->route('portaal.dossiers.show', $first)
            : redirect()->route('portaal.dashboard');
    }

    private function authorizeDossier(Request $request, Dossier $dossier): void
    {
        abort_unless(
            $request->user() && $dossier->user_id === $request->user()->id,
            403,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeDossier(Dossier $d): array
    {
        return [
            'id' => $d->id,
            'status' => $d->status,
            'kenteken' => $d->kenteken,
            'merk' => $d->merk,
            'model' => $d->model,
            'pakket' => $d->pakket,
            'unread_admin_messages_count' => $d->unread_admin_messages_count ?? null,
            'created_at' => $d->created_at?->toIso8601String(),
            'urls' => [
                'show' => route('portaal.dossiers.show', $d),
                'documents' => route('portaal.dossiers.documents', $d),
                'messages' => route('portaal.dossiers.messages', $d),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fullDossier(Dossier $d): array
    {
        return array_merge($this->summarizeDossier($d), [
            'bpm_indicatie_eur' => $d->bpm_indicatie_eur,
            'service_fee_eur' => $d->service_fee_eur,
            'started_at' => $d->started_at?->toIso8601String(),
            'completed_at' => $d->completed_at?->toIso8601String(),
        ]);
    }
}
