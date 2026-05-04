<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\Rdw\KentekenNormalizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class LookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Closure>>
     */
    public function rules(): array
    {
        return [
            'kenteken' => [
                'required',
                'string',
                'min:5',
                'max:12',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! KentekenNormalizer::isValidFormat($value)) {
                        $fail('Het opgegeven kenteken heeft geen geldig formaat.');
                    }
                },
            ],
            'residency_change' => ['sometimes', 'boolean'],
            'autonomia' => ['sometimes', 'string', 'max:32'],
        ];
    }

    public function normalizedKenteken(): string
    {
        return KentekenNormalizer::normalize((string) $this->input('kenteken'));
    }

    public function residencyChange(): bool
    {
        return $this->boolean('residency_change');
    }

    public function autonomia(): string
    {
        $value = $this->input('autonomia');

        return is_string($value) && $value !== '' ? $value : 'default';
    }
}
