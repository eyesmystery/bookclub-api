<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'published_at',
        'division_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the division that owns the news.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
