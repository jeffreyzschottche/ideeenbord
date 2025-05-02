<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainQuestionResponse extends Model
{
    protected $fillable = ['user_id', 'brand_id', 'main_question_id', 'answer'];
}
