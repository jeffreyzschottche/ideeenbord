<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class brandOwner extends Model
{
    use HasFactory;

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
