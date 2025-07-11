<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckProfanity
{
    public function handle(Request $request, Closure $next)
    {
        $banned = config('profanity.words', []);
        $values = $this->flatten($request->all());

        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }
            foreach ($banned as $bad) {
                if ($bad !== '' && stripos($value, $bad) !== false) {
                    return response()->json(['message' => 'Scheldwoorden zijn verboden.'], 422);
                }
            }
        }

        if ($request->is('api/v1/ideas') && $request->isMethod('post')) {
            $patterns = [
                '/\b\d{10}\b/',
                '/https?:\/\//i',
                '/www\./i',
                '/[€$£]/',
                '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
            ];
            foreach ($values as $value) {
                if (!is_string($value)) {
                    continue;
                }
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return response()->json(['message' => 'Contactgegevens zijn niet toegestaan in ideeën.'], 422);
                    }
                }
            }
        }

        return $next($request);
    }

    private function flatten(array $data): array
    {
        $result = [];
        foreach ($data as $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flatten($value));
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }
}
