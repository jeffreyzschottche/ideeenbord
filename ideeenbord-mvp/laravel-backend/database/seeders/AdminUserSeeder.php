<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'jeffreyzschot@gmail.com');
        $password = env('ADMIN_PASSWORD');

        if (!$password) {
            $this->command?->error('ADMIN_PASSWORD is required. Example: ADMIN_PASSWORD="new-password" php artisan db:seed --class=AdminUserSeeder');
            return;
        }

        $user = User::firstOrNew(['email' => $email]);

        $user->fill([
            'name' => env('ADMIN_NAME', 'jeffrey'),
            'username' => env('ADMIN_USERNAME', 'jeffrey'),
            'password' => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'liked_posts' => $user->liked_posts ?? [],
            'disliked_posts' => $user->disliked_posts ?? [],
            'created_posts' => $user->created_posts ?? [],
            'quiz_submissions' => $user->quiz_submissions ?? [],
            'ratings_given' => $user->ratings_given ?? [],
        ]);

        $user->forceFill([
            'role' => 'admin',
            'is_bot' => false,
        ]);

        $user->save();

        $this->command?->info("Admin user ready: {$email}");
    }
}
