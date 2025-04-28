<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class BrandOwner extends Authenticatable
{
    use HasFactory, HasApiTokens;

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
}
