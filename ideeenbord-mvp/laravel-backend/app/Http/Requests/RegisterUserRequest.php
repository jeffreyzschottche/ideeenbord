<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ProfanityFree;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;               // iedereen mag registreren
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new ProfanityFree],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username', new ProfanityFree],
            'password' => ['required', 'string', 'min:6'],
            // alle overige velden blijven zoals je ze had
            'gender' => ['nullable', 'string'],
            'birthdate' => ['nullable', 'date'],
            'education_level' => ['nullable', 'string'],
            'education' => ['nullable', 'string'],
            'job' => ['nullable', 'string', new ProfanityFree],
            'sector' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'birth_city' => ['nullable', 'string'],
            'relationship_status' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Naam is verplicht.'),
            'username.required' => __('Gebruikersnaam is verplicht.'),
            'profanity' => __('Ongepaste taal gedetecteerd.'),
        ];
    }
}
