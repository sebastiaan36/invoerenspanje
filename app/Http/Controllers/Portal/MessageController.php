<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\DossierMessage;
use App\Models\DossierMessageAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MessageController extends Controller
{
    public function store(Request $request, Dossier $dossier): RedirectResponse
    {
        abort_unless($request->user()?->id === $dossier->user_id, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif,pdf', 'max:10240'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($dossier, $user, $data, $request): void {
            $message = DossierMessage::create([
                'dossier_id' => $dossier->id,
                'author_id' => $user->id,
                'author_role' => $user->role,
                'body' => $data['body'],
            ]);

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->storeAs(
                    "dossier-message-attachments/{$dossier->id}",
                    Str::uuid().'.'.$file->getClientOriginalExtension(),
                    'local',
                );

                $message->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size_bytes' => $file->getSize() ?: 0,
                ]);
            }
        });

        $dossier->messages()
            ->where('author_role', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Bericht verstuurd.');
    }

    public function downloadAttachment(Request $request, DossierMessageAttachment $attachment): StreamedResponse
    {
        $user = $request->user();
        $dossier = $attachment->message->dossier;

        abort_unless(
            $user && ($user->isAdmin() || $dossier->user_id === $user->id),
            403,
        );

        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->response(
            $attachment->path,
            $attachment->filename,
            ['Content-Disposition' => 'inline; filename="'.$attachment->filename.'"'],
        );
    }
}
