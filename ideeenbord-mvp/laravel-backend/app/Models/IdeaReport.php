<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaReport extends Model
{
    protected $fillable = ['idea_id', 'user_id', 'reason'];

    public function idea()
    {
        return $this->belongsTo(Idea::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
