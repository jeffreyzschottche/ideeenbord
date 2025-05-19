<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- Import toevoegen
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'name',
    'email',
    'password',
    'username',
    'gender',
    'birthdate',
    'education_level',
    'education',
    'job',
    'sector',
    'city',
    'birth_city',
    'relationship_status',
    'postal_code',
    'liked_posts',
    'ratings_given',
    'disliked_posts',
    'created_posts',
    'quiz_submissions',
    ];

    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'liked_posts' => 'array',
            'disliked_posts' => 'array',
            'created_posts' => 'array',
            'quiz_submissions' => 'array',
            'ratings_given' => 'array',
            'birthdate' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
