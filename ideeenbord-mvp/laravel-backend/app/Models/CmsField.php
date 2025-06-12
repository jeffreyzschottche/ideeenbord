<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CmsField extends Model
{
    use HasFactory;

    protected $fillable = ['page_id', 'label', 'key', 'type', 'value'];

    public function page()
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }
}
