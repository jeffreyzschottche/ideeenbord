<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainQuestion extends Model
{
    use HasFactory;

    // protected $table = 'main_question'; // 🔥 fix naam
    protected $fillable = [
        'text',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];
}
