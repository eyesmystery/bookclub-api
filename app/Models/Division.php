<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users in this division.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the events in this division.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the articles in this division.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get the news in this division.
     */
    public function news()
    {
        return $this->hasMany(News::class);
    }
}
