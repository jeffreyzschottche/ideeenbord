<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProfanityFree implements Rule
{
    protected array $banned = [
        'fuck',
        'shit',
        'bitch',
        'kut',
    ];

    public function passes($attribute, $value)
    {
        if (! is_string($value)) {
            return true;
        }

        $lower = strtolower($value);
        foreach ($this->banned as $word) {
            if (str_contains($lower, $word)) {
                return false;
            }
        }
        return true;
    }

    public function message()
    {
        return 'profanity-detected';
    }
}
