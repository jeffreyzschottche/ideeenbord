<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Quiz extends Model
{
    use HasFactory;
    protected $fillable = [
        'brand_id', 'title', 'slug', 'description', 'prize', 'status',
        'quiz_questions', 'quiz_answers', 'participants', 'winner_id'
    ];
    protected $casts = [
        'quiz_questions' => 'array',
        'quiz_answers' => 'array',
        'participants' => 'array',
    ];

    public function brand() {
        return $this->belongsTo(Brand::class);
    }
}
