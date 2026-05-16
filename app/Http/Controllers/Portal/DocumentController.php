<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\Models\Document;
use App\Models\Dossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentController extends Controller
{
    public function store(DocumentUploadRequest $request, Dossier $dossier): RedirectResponse
    {
        abort_unless($request->user()?->id === $dossier->user_id, 403);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $stored = $file->storeAs(
            "dossiers/{$dossier->id}",
            Str::uuid().'.'.$extension,
            'local',
        );

        Document::create([
            'dossier_id' => $dossier->id,
            'type' => $request->string('type'),
            'filename' => $file->getClientOriginalName(),
            'path' => $stored,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $file->getSize() ?: 0,
            'status' => Document::STATUS_GEUPLOAD,
        ]);

        return back()->with('success', 'Document geüpload.');
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->isAdmin() || $document->dossier->user_id === $user->id),
            403,
        );

        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download($document->path, $document->filename);
    }

    public function destroy(Request $request, Dossier $dossier, Document $document): RedirectResponse
    {
        abort_unless(
            $request->user() && $document->dossier_id === $dossier->id && $dossier->user_id === $request->user()->id,
            403,
        );

        abort_unless($document->status === Document::STATUS_GEUPLOAD, 403);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document verwijderd.');
    }
}
