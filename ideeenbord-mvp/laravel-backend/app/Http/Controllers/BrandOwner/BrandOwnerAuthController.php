<?php

namespace App\Http\Controllers\BrandOwner;

use App\Models\BrandOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;


class BrandOwnerAuthController extends Controller
{
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

        $owner->load('brand'); // Laad merk erbij voor frontend redirect

        return response()->json([
            'message' => 'Login gelukt!',
            'owner' => $owner,
            'token' => $token,
        ]);
    }
    public function me(Request $request)
    {
        $owner = auth('brand_owner')->user(); // 🔥 belangrijk: auth('brand_owner')
        
        if (!$owner) {
            return response()->json(['message' => 'Niet geauthenticeerd'], 401);
        }
    
        $owner->load('brand');
    
        return response()->json([
            'owner' => $owner,
        ]);
    }
    
public function logout(Request $request)
{
    auth('brand_owner')->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Uitgelogd.']);
}
}
