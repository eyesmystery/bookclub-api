<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'author_id',
        'published_at',
        'division_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the author of the article.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the division that owns the article.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
