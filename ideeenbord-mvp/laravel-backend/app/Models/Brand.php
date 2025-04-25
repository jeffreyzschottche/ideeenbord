<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'verified',
        'rating',
        'has_paid',
        'subscription',
        'brand_owner_id',
        'likes',
        'dislikes',
        'quizzes',
        'giveaways',
        'main_question',
        'ideas',
        'pinned_ideas',
    ];
    protected $casts = [
        'socials' => 'array',
        'quizzes' => 'array',
        'giveaways' => 'array',
        'ideas' => 'array',
        'pinned_ideas' => 'array',
    ];
    public function owner()
{
    return $this->hasOne(BrandOwner::class);
}
}
