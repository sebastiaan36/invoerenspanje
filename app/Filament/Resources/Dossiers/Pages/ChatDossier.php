<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dossiers\Pages;

use App\Filament\Resources\Dossiers\DossierResource;
use App\Models\DossierMessage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ChatDossier extends Page implements HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithSchemas;

    protected static string $resource = DossierResource::class;

    protected string $view = 'filament.resources.dossiers.pages.chat-dossier';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $title = 'Berichten';

    /** @var array<string, mixed> */
    public ?array $data = ['reply' => '', 'attachments' => []];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Markeer alle ongelezen klant-berichten als gelezen zodra admin de chat opent.
        $this->getRecord()->messages()
            ->where('author_role', 'klant')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->form->fill(['reply' => '', 'attachments' => []]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Textarea::make('reply')
                    ->label('Antwoord aan '.($this->getRecord()->user?->name ?? 'de klant'))
                    ->placeholder('Uw bericht…')
                    ->required()
                    ->rows(5)
                    ->maxLength(5000)
                    ->helperText('De klant ontvangt een mailnotificatie (max 1 per uur per dossier).'),

                FileUpload::make('attachments')
                    ->label('Bijlagen')
                    ->multiple()
                    ->maxFiles(5)
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'])
                    ->storeFiles(false) // we slaan ze zelf op in send() — anders raakt de original filename verloren
                    ->helperText('Optioneel — JPG, PNG, WEBP, GIF of PDF (max 5 bestanden, 10 MB elk).'),
            ]);
    }

    public function getThreadMessages(): Collection
    {
        return $this->getRecord()->messages()->with(['author:id,name', 'attachments'])->oldest()->get();
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $dossier = $this->getRecord();

        DB::transaction(function () use ($data, $dossier): void {
            $message = DossierMessage::create([
                'dossier_id' => $dossier->id,
                'author_id' => Auth::id(),
                'author_role' => 'admin',
                'body' => $data['reply'],
            ]);

            foreach (($data['attachments'] ?? []) as $tempFile) {
                if (! $tempFile instanceof TemporaryUploadedFile) {
                    continue;
                }

                $path = $tempFile->storeAs(
                    "dossier-message-attachments/{$dossier->id}",
                    Str::uuid().'.'.$tempFile->getClientOriginalExtension(),
                    'local',
                );

                $message->attachments()->create([
                    'filename' => $tempFile->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $tempFile->getMimeType() ?? 'application/octet-stream',
                    'size_bytes' => $tempFile->getSize() ?: 0,
                ]);
            }
        });

        $this->form->fill(['reply' => '', 'attachments' => []]);

        Notification::make()
            ->title('Bericht verstuurd')
            ->success()
            ->send();
    }

    public function getBreadcrumb(): string
    {
        return 'Berichten';
    }
}
