<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLike;
use Illuminate\Http\Request;

class BookLikeController extends Controller
{
    /**
     * Toggle like for a book.
     */
    public function toggle(Request $request, Book $book)
    {
        $user = $request->user();
        
        $existingLike = BookLike::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();

        if ($existingLike) {
            // Unlike the book
            $existingLike->delete();
            $liked = false;
            $message = 'Book unliked successfully';
        } else {
            // Like the book
            BookLike::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
            $liked = true;
            $message = 'Book liked successfully';
        }

        // Get the updated like count
        $likesCount = $book->likes()->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }
}
