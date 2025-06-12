<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    public function fields()
    {
        return $this->hasMany(\App\Models\CmsField::class, 'page_id');
    }
}
