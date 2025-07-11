<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ProfanityFree;

class StoreBrandRequest extends FormRequest
{
    /** Only authenticated users may request a brand */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Validate brand request fields */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', new ProfanityFree],
            'category' => ['required', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', new ProfanityFree],
            'intro' => ['nullable', 'string', new ProfanityFree],
            'intro_short' => ['nullable', 'string', 'max:160', new ProfanityFree],
            'email' => ['required', 'email', 'unique:brands,email', new ProfanityFree],
            'logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'socials' => ['nullable', 'json'],
        ];
    }

    /** Custom error messages */
    public function messages(): array
    {
        return [
            'title.required' => __('Merknaam is verplicht.'),
            'category.required' => __('Categorie is verplicht.'),
            'email.required' => __('Email is verplicht.'),
            'profanity' => __('Ongepaste taal gedetecteerd.'),
        ];
    }
}
