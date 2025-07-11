<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
/**
 * Class AuthController
 *
 * This controller handles user authentication, registration, profile updates,
 * and notification access. It is responsible for managing user credentials,
 * access tokens, and public profile information.
 */

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * Validates incoming user data, creates a user, hashes the password,
     * initializes JSON fields, sends a verification email, and returns an access token.
     *
     * @param Request $request The HTTP request with registration details.
     * @return \Illuminate\Http\JsonResponse The access token and created user.
     *
     * @throws \Illuminate\Validation\ValidationException If input validation fails.
     */
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        $data['liked_posts'] = [];
        $data['disliked_posts'] = [];
        $data['created_posts'] = [];
        $data['quiz_submissions'] = [];

        $user = User::create($data);


        $token = $user->createToken('auth_token')->plainTextToken;
        $user->sendEmailVerificationNotification();


        return response()->json(['access_token' => $token, 'user' => $user], 201);
    }
    /**
     * Authenticate a user and return an access token.
     *
     * @param Request $request The HTTP request containing credentials.
     * @return \Illuminate\Http\JsonResponse The access token and user info.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
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
    /**
     * Update user profile data.
     *
     * Only the authenticated user can update their own profile.
     *
     * @param Request $request The HTTP request containing profile data.
     * @param string $username The username of the profile being updated.
     * @return \Illuminate\Http\JsonResponse A message and updated user object.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request, $username)
    {
        $user = $request->user();

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
    /**
     * Show limited public data for a user by username.
     *
     * @param string $username The username to look up.
     * @return \Illuminate\Http\JsonResponse Public profile data.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user not found.
     */
    public function showByUsername($username)
    {
        $user = \App\Models\User::where('username', $username)->firstOrFail();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'created_posts' => $user->created_posts ?? [],
        ]);
    }

    /**
     * Return authenticated user's notifications if username matches.
     *
     * @param string $username The username of the authenticated user.
     * @return \Illuminate\Http\JsonResponse List of notifications.
     */
    public function notifications($username)
    {
        $auth = auth()->user();
        if (!$auth || $auth->username !== $username) {
            abort(403);
        }

        return response()->json([
            'notifications' => $auth->notifications ?? [],
        ]);
    }
}
