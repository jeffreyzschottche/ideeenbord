<?php

namespace App\Http\Controllers\BrandOwner;

use App\Models\BrandOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

/**
 * Class BrandOwnerAuthController
 *
 * This controller handles authentication processes for brand owners, including
 * login, logout, and retrieving the currently authenticated brand owner.
 */
class BrandOwnerAuthController extends Controller
{
    /**
     * Authenticate a brand owner and return an access token.
     *
     * Validates credentials, checks email and admin verification, and returns
     * a personal access token upon successful login.
     *
     * @param Request $request The HTTP request containing login credentials.
     * @return \Illuminate\Http\JsonResponse A JSON response with the owner and token or error message.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $owner = BrandOwner::where('email', $request->email)->first();

        if (!$owner || !Hash::check($request->password, $owner->password)) {
            return response()->json(['message' => 'Ongeldige inloggegevens.'], 401);
        }

        if (!$owner->verified_owner) {
            return response()->json(['message' => 'Je account is nog niet goedgekeurd door een administrator.'], 403);
        }

        if (!$owner->hasVerifiedEmail()) {
            return response()->json(['message' => 'Je e-mailadres is nog niet geverifieerd.'], 403);
        }

        $token = $owner->createToken('brand-owner-token')->plainTextToken;

        // Load associated brand for frontend use
        $owner->load('brand');

        return response()->json([
            'message' => 'Login gelukt!',
            'owner' => $owner,
            'token' => $token,
        ]);
    }

    /**
     * Get the currently authenticated brand owner.
     *
     * Loads associated brand and returns the authenticated user's data.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse A JSON response with the brand owner or error message.
     */
    public function me(Request $request)
    {
        $owner = auth('brand_owner')->user();

        if (!$owner) {
            return response()->json(['message' => 'Niet geauthenticeerd'], 401);
        }

        $owner->load('brand');

        return response()->json([
            'owner' => $owner,
        ]);
    }

    /**
     * Logout the currently authenticated brand owner.
     *
     * Deletes the current access token to invalidate the session.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse A JSON response confirming logout.
     */
    public function logout(Request $request)
    {
        auth('brand_owner')->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Uitgelogd.']);
    }
}
