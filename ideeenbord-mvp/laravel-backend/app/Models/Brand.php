<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'category',
        'website_url',
        'intro',
        'intro_short',
        'email',
        'logo_path',
        'socials',
        'accepted',
        'verified',
        'rating',
        'has_paid',
        'subscription',
        'brand_owner_id',
        'likes',
        'dislikes',
        'quizzes',
        'giveaways',
        'main_question_id',
        'ideas',
        'pinned_ideas',
    ];
    protected $casts = [
        'socials' => 'array',
        'quizzes' => 'array',
        'giveaways' => 'array',
        'ideas' => 'array',
        'pinned_ideas' => 'array',
        'accepted' => 'boolean',
        'verified' => 'boolean',
    ];
    public function owner()
    {
        return $this->hasOne(BrandOwner::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            $brand->slug = \Str::slug($brand->title);
        });

        static::updating(function ($brand) {
            $brand->slug = \Str::slug($brand->title);
        });
    }
    public function mainQuestion()
    {
        return $this->belongsTo(MainQuestion::class);
    }


}
