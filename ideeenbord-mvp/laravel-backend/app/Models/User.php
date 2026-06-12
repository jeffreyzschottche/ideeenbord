<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- Import toevoegen
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailForUser;

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
    'political_preference',
    'household_role',
    'purchase_decision',
    'order_frequency',
    'tech_spend',
    'grocery_spend',
    'household_size',
    'liked_posts',
    'ratings_given',
    'disliked_posts',
    'created_posts',
    'quiz_submissions',
    // 'role' en 'is_bot' bewust NIET fillable — voorkomt privilege escalation
    // via mass assignment. Worden alleen expliciet via forceFill gezet.
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
            'notifications' => 'array',
            'is_bot' => 'boolean',
        ];
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailForUser);
    }
}
