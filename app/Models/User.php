<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'division_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the division that owns the user.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the books recommended by this user.
     */
    public function recommendedBooks()
    {
        return $this->hasMany(Book::class, 'recommended_by_user_id');
    }

    /**
     * Get the articles authored by this user.
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * Get the book likes by this user.
     */
    public function bookLikes()
    {
        return $this->hasMany(BookLike::class);
    }

    /**
     * Get the book reviews by this user.
     */
    public function bookReviews()
    {
        return $this->hasMany(BookReview::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a moderator.
     */
    public function isModerator()
    {
        return $this->role === 'moderator';
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if the user is a member (for backward compatibility).
     */
    public function isMember()
    {
        return in_array($this->role, ['member', 'user']);
    }
}
