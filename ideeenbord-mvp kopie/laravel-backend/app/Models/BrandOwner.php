<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailForBrandOwner;

class BrandOwner extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'brand_id',
        'name',
        'email',
        'phone',
        'url',
        'subscription_plan',
        'password',
        'verified_owner',
    ];
    protected $hidden = [
        'password'
    ];
    public static function boot()
    {
        parent::boot();

        static::creating(function ($owner) {
            $owner->password = Hash::make($owner->password);
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
     public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailForBrandOwner);
    }
}
