<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProfanityFree implements Rule
{
    protected array $bad;

    public function __construct()
    {
        $this->bad = config('profanity.bad_words');
    }

    public function passes($attribute, $value): bool
    {
        $lower = mb_strtolower($value);
        foreach ($this->bad as $word) {
            if (str_contains($lower, $word)) {
                return false;
            }
        }
        return true;
    }

    public function message(): string
    {
        return 'profanity-detected';
    }
}
