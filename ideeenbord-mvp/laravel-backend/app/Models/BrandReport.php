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
        'period_type',
        'period_start',
        'period_end',
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
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
