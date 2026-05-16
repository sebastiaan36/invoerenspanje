<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string|object>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(Document::TYPES))],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10 MB
        ];
    }
}
