<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        // 👇 Fix hier: Geef JSON terug i.p.v. redirect
        abort(response()->json([
            'message' => 'Niet geauthenticeerd.'
        ], 401));
    }
}
