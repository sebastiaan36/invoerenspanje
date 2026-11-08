<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Public contact details
|--------------------------------------------------------------------------
|
| Single source for the phone number, e-mail address and opening hours. Used
| by the JSON-LD schema and the crawler fallback HTML, and shared with Vue as
| the `contact` page prop (read by resources/js/lib/contact.ts).
|
*/

$phone = '+34650015942';

return [
    // E.164, for tel: links and schema.org.
    'phone' => $phone,

    'phone_display' => '+34 650 015 942',

    'email' => 'info@autoinvoerenspanje.nl',

    'whatsapp_url' => 'https://wa.me/'.ltrim($phone, '+'),

    'location' => 'Málaga, Spanje',

    // Spanish time.
    'opening_hours' => [
        ['days' => 'Maandag – vrijdag', 'hours' => '09:00 – 18:00'],
        ['days' => 'Zaterdag', 'hours' => '10:00 – 14:00'],
        ['days' => 'Zondag', 'hours' => 'gesloten'],
    ],
];
