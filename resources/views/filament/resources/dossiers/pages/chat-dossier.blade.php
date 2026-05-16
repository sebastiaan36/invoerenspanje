<x-filament-panels::page>
    @php($messages = $this->getThreadMessages())

    <x-filament::section>
        <x-slot name="heading">Berichtenthread</x-slot>
        <x-slot name="description">
            {{ $messages->count() === 0 ? 'Nog geen berichten in dit dossier.' : $messages->count() . ' berichten' }}
        </x-slot>

        @if($messages->isNotEmpty())
            <ol style="display:flex; flex-direction:column; gap:1rem; max-height:60vh; overflow-y:auto; list-style:none; margin:0; padding:0.25rem;">
                @foreach($messages as $msg)
                    @php($isAdmin = $msg->author_role === 'admin')
                    <li style="display:flex; justify-content:{{ $isAdmin ? 'flex-end' : 'flex-start' }};">
                        <div style="max-width:80%;">
                            <div style="display:flex; gap:0.5rem; align-items:center; font-size:0.75rem; color:#6b7280; justify-content:{{ $isAdmin ? 'flex-end' : 'flex-start' }}; margin-bottom:0.25rem;">
                                <span style="font-weight:500;">{{ $msg->author->name ?? 'Onbekend' }}</span>
                                <span aria-hidden="true">·</span>
                                <time>{{ $msg->created_at->format('d-m-Y H:i') }}</time>
                                <span style="
                                    border-radius:9999px;
                                    padding:0 0.375rem;
                                    font-size:0.625rem;
                                    font-weight:600;
                                    text-transform:uppercase;
                                    letter-spacing:0.05em;
                                    background:{{ $isAdmin ? '#e0e7ff' : '#fef3c7' }};
                                    color:{{ $isAdmin ? '#3730a3' : '#92400e' }};
                                ">
                                    {{ $isAdmin ? 'uitvoerder' : 'klant' }}
                                </span>
                            </div>
                            <div style="
                                padding:0.75rem 1rem;
                                border-radius:1rem;
                                font-size:0.875rem;
                                line-height:1.5;
                                white-space:pre-line;
                                background:{{ $isAdmin ? '#4f46e5' : '#f3f4f6' }};
                                color:{{ $isAdmin ? '#ffffff' : '#111827' }};
                            ">{{ $msg->body }}</div>

                            @if($msg->attachments->isNotEmpty())
                                <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.5rem; justify-content:{{ $isAdmin ? 'flex-end' : 'flex-start' }};">
                                    @foreach($msg->attachments as $a)
                                        @if(str_starts_with($a->mime_type, 'image/'))
                                            <a href="{{ route('portaal.messages.attachments.download', $a) }}" target="_blank" rel="noopener" title="{{ $a->filename }}" style="display:block; overflow:hidden; border:1px solid #e5e7eb; border-radius:0.75rem;">
                                                <img src="{{ route('portaal.messages.attachments.download', $a) }}" alt="{{ $a->filename }}" style="display:block; max-height:160px; max-width:240px; object-fit:cover;" loading="lazy">
                                            </a>
                                        @else
                                            <a href="{{ route('portaal.messages.attachments.download', $a) }}" target="_blank" rel="noopener" title="{{ $a->filename }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; border:1px solid #e5e7eb; border-radius:0.75rem; font-size:0.75rem; color:#374151; text-decoration:none; background:#ffffff;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                <span style="font-weight:500; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $a->filename }}</span>
                                                <span style="color:#9ca3af;">{{ $a->size_bytes < 1024*1024 ? round($a->size_bytes/1024, 1).' kB' : round($a->size_bytes/(1024*1024), 1).' MB' }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            @if($isAdmin)
                                <div style="margin-top:0.25rem; font-size:0.625rem; color:#9ca3af; text-align:right;">
                                    @if($msg->read_at)
                                        Gelezen door klant op {{ $msg->read_at->format('d-m-Y H:i') }}
                                    @else
                                        Nog niet gelezen
                                    @endif
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Nieuw bericht</x-slot>

        <form wire:submit="send">
            {{ $this->form }}

            <div style="display:flex; justify-content:flex-end; margin-top:1rem;">
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    Verstuur
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
