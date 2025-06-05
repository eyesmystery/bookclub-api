<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookReviewRequest;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Http\Request;

class BookReviewController extends Controller
{
    /**
     * Store a review for a book.
     */
    public function store(StoreBookReviewRequest $request, Book $book)
    {
        $review = BookReview::create([
            'user_id' => $request->user()->id,
            'book_id' => $book->id,
            'content' => $request->content,
        ]);

        $review->load('user:id,name,email');

        return response()->json([
            'message' => 'Review added successfully',
            'review' => $review,
        ], 201);
    }

    /**
     * Get reviews for a specific book.
     */
    public function index(Request $request, Book $book)
    {
        $reviews = $book->reviews()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(15);

        return response()->json([
            'reviews' => $reviews,
        ]);
    }

    /**
     * Get books that have reviews.
     */
    public function reviewedBooks(Request $request)
    {
        $books = Book::with(['division', 'recommendedBy'])
            ->withCount(['reviews', 'likes'])
            ->whereHas('reviews')
            ->latest()
            ->paginate(15);

        return response()->json([
            'books' => $books,
        ]);
    }

    /**
     * Delete a review (Admin only).
     */
    public function destroy(BookReview $review)
    {
        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }
}
