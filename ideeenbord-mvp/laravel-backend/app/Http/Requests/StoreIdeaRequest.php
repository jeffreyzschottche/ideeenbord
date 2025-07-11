<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ProfanityFree;      // ← de rule uit eerdere stap

class StoreIdeaRequest extends FormRequest
{
    /** Alleen ingelogde gebruikers mogen ideeën plaatsen */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Valideer en filter invoer */
    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'title' => ['required', 'string', 'max:255', new ProfanityFree],
            'description' => ['nullable', 'string', 'max:1000', new ProfanityFree],
        ];
    }

    /** (optioneel) custom foutmelding per attribuut  */
    public function messages(): array
    {
        return [
            'brand_id.required' => __('Je moet een merk kiezen.'),
            'title.required' => __('Een titel is verplicht.'),
            'title.max' => __('Titel mag maximaal 255 tekens bevatten.'),
        ];
    }
}
