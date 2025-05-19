<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'gender' => 'nullable|string',
            'birthdate' => 'nullable|date',
            'education_level' => 'nullable|string',
            'education' => 'nullable|string',
            'job' => 'nullable|string',
            'sector' => 'nullable|string',
            'city' => 'nullable|string',
            'birth_city' => 'nullable|string',
            'relationship_status' => 'nullable|string',
            'postal_code' => 'nullable|string',
        ]);

        $data['password'] = Hash::make($data['password']);

        // JSON-velden initieel als lege arrays
        $data['liked_posts'] = [];
        $data['disliked_posts'] = [];
        $data['created_posts'] = [];
        $data['quiz_submissions'] = [];

        $user = User::create($data);


        $token = $user->createToken('auth_token')->plainTextToken;
        $user->sendEmailVerificationNotification();


        return response()->json(['access_token' => $token, 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'user' => $user]);
    }
    public function update(Request $request, $username)
{
    $user = $request->user();

    // Alleen eigen profiel mag aangepast worden
    if ($user->username !== $username) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|string|min:6',
        'username' => 'sometimes|string|unique:users,username,' . $user->id,
        'gender' => 'nullable|string',
        'education_level' => 'nullable|string',
        'education' => 'nullable|string',
        'job' => 'nullable|string',
        'sector' => 'nullable|string',
        'city' => 'nullable|string',
        'relationship_status' => 'nullable|string',
        'postal_code' => 'nullable|string',
    ]);

    if (isset($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    }

    $user->update($validated);

    return response()->json(['message' => 'Gegevens bijgewerkt', 'user' => $user]);
}

}
