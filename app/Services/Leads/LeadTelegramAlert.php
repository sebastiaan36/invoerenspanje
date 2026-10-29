<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;

/**
 * Stuurt bij een nieuwe lead een Telegram-bericht met een knop die WhatsApp
 * opent met de klantgegevens (naam, locatie, telefoon) al ingevuld, klaar om
 * door te sturen.
 */
final class LeadTelegramAlert
{
    public function isConfigured(): bool
    {
        return filled(config('services.telegram.bot_token'))
            && filled(config('services.telegram.chat_id'));
    }

    public function send(Lead $lead): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        Http::asJson()
            ->timeout(10)
            ->post(sprintf('https://api.telegram.org/bot%s/sendMessage', config('services.telegram.bot_token')), [
                'chat_id' => config('services.telegram.chat_id'),
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
                'text' => $this->message($lead),
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'Doorsturen via WhatsApp', 'url' => $this->whatsappUrl($lead)]],
                        [['text' => 'Bekijk lead', 'url' => LeadResource::getUrl('view', ['record' => $lead])]],
                    ],
                ],
            ])
            ->throw();
    }

    /**
     * WhatsApp-link met de klantgegevens als vooraf ingevuld bericht.
     */
    public function whatsappUrl(Lead $lead): string
    {
        $number = preg_replace('/\D+/', '', (string) config('services.telegram.lead_forward_whatsapp'));

        $text = implode("\n", [
            'Nieuwe klant:',
            'Naam: '.$lead->name,
            'Locatie: '.$lead->woonplaats_spanje,
            'Telefoon: '.$lead->phone,
        ]);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }

    private function message(Lead $lead): string
    {
        return implode("\n", [
            sprintf('<b>Nieuwe lead #%05d</b>', $lead->id),
            '',
            'Naam: '.e($lead->name),
            'Locatie: '.e($lead->woonplaats_spanje),
            'Telefoon: '.e($lead->phone),
            'Kenteken: '.e($lead->kenteken),
            'Pakket: '.e($lead->package_slug),
        ]);
    }
}
