<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandReport extends Model
{
    protected $fillable = [
        'brand_id',
        'title',
        'status',
        'provider',
        'model',
        'metrics',
        'ai',
        'error',
        'generated_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'ai' => 'array',
        'generated_at' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
