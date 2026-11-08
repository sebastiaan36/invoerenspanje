<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\Packages\ServicePackages;
use App\Services\Rdw\KentekenNormalizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Closure|object>>
     */
    public function rules(): array
    {
        $packageSlugs = array_map(fn ($p) => $p->slug, ServicePackages::all());

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,strict', 'max:255'],
            'phone' => ['required', 'string', 'max:64'],
            'regio' => [
                'required',
                'string',
                'max:255',
                // Printed in the confirmation mail: no links, only a place name.
                'not_regex:/(https?:|www\.|:\/\/|[a-z0-9-]+\.(?:com|net|org|nl|es|io|ru|info|xyz|top|link|click)\b)/i',
            ],
            'expected_move_date' => ['nullable', 'string', 'max:100'],
            'comment' => ['nullable', 'string', 'max:5000'],

            'kenteken' => [
                'required',
                'string',
                'max:12',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! KentekenNormalizer::isValidFormat($value)) {
                        $fail('Kenteken heeft geen geldig formaat.');
                    }
                },
            ],
            'package_slug' => ['required', 'string', Rule::in($packageSlugs)],
            'residency_change' => ['sometimes', 'boolean'],
            'autonomia' => ['sometimes', 'string', Rule::in(array_keys(config('spain_import.autonomias')))],

            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],

            // Honeypot (blijft leeg bij echte gebruikers — STAP 6 polish, vast plumbing).
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'regio.not_regex' => 'Vul alleen uw woonplaats of regio in, zonder link.',
        ];
    }

    /**
     * Lowercase the e-mail address and strip Markdown/HTML brackets from the
     * region, which is printed in the confirmation mail.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if (is_string($this->input('email'))) {
            $normalized['email'] = mb_strtolower(trim($this->input('email')));
        }

        if (is_string($this->input('regio'))) {
            $normalized['regio'] = trim((string) preg_replace(
                '/\s+/',
                ' ',
                str_replace(['[', ']', '<', '>'], ' ', $this->input('regio')),
            ));
        }

        $this->merge($normalized);
    }

    /**
     * Price indications are deliberately absent: the controller derives them
     * from the server-side quote and ignores whatever the browser sent.
     *
     * @return array<string, mixed>
     */
    public function toLeadAttributes(): array
    {
        $utmSource = $this->input('utm_source');

        return [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'woonplaats_spanje' => $this->input('regio'),
            'expected_move_date' => $this->input('expected_move_date') ?: null,
            'comment' => $this->input('comment') ?: null,

            'kenteken' => KentekenNormalizer::normalize((string) $this->input('kenteken')),
            'package_slug' => $this->input('package_slug'),
            'residency_change' => $this->boolean('residency_change'),
            'autonomia' => $this->input('autonomia') ?: 'default',

            // utm_medium=social marks unpaid social links (the Instagram bio
            // page); any other tagged visit counts as an ad.
            'source' => match (true) {
                ! $utmSource => 'organic',
                $this->input('utm_medium') === 'social' => 'social',
                default => 'ads',
            },
            'utm_source' => $utmSource ?: null,
            'utm_medium' => $this->input('utm_medium') ?: null,
            'utm_campaign' => $this->input('utm_campaign') ?: null,
        ];
    }
}
