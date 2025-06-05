<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'description',
        'cover_image',
        'pdf_file',
        'recommended_by_user_id',
    ];

    /**
     * Get the user who recommended this book.
     */
    public function recommendedBy()
    {
        return $this->belongsTo(User::class, 'recommended_by_user_id');
    }

    /**
     * Get the likes for the book.
     */
    public function likes()
    {
        return $this->hasMany(BookLike::class);
    }

    /**
     * Get the reviews for the book.
     */
    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    /**
     * Check if the current user has liked this book.
     */
    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Check if the current user has reviewed this book.
     */
    public function isReviewedByUser($userId)
    {
        return $this->reviews()->where('user_id', $userId)->exists();
    }
}
