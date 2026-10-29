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
            'Telefoon: '.$this->internationalPhone($lead->phone),
            'Lead: '.LeadResource::getUrl('view', ['record' => $lead]),
        ]);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }

    /**
     * Schrijf het nummer als +landcode zonder spaties, zodat WhatsApp het als
     * aantikbaar telefoonnummer herkent. Zonder landcode gaan we uit van een
     * Nederlands (06…) of Spaans (9 cijfers) nummer; al het andere blijft
     * zoals de klant het invulde.
     */
    public function internationalPhone(string $phone): string
    {
        $phone = trim($phone);
        $digits = preg_replace('/\D+/', '', $phone);

        return match (true) {
            str_starts_with($phone, '+') => '+'.$digits,
            str_starts_with($digits, '00') => '+'.substr($digits, 2),
            strlen($digits) === 10 && str_starts_with($digits, '0') => '+31'.substr($digits, 1),
            strlen($digits) === 9 && in_array($digits[0], ['6', '7', '8', '9'], true) => '+34'.$digits,
            default => $phone,
        };
    }

    private function message(Lead $lead): string
    {
        return implode("\n", [
            sprintf('<b>Nieuwe lead #%05d</b>', $lead->id),
            '',
            'Naam: '.e($lead->name),
            'Locatie: '.e($lead->woonplaats_spanje),
            'Telefoon: '.e($this->internationalPhone($lead->phone)),
            'Kenteken: '.e($lead->kenteken),
            'Pakket: '.e($lead->package_slug),
        ]);
    }
}
