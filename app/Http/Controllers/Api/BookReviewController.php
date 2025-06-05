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
        // Check if user already reviewed this book
        $existingReview = BookReview::where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this book',
            ], 422);
        }

        $review = BookReview::create([
            'user_id' => $request->user()->id,
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load('user:id,name,email');

        return response()->json([
            'success' => true,
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
            'success' => true,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Get books that have reviews.
     */
    public function reviewedBooks(Request $request)
    {
        $query = Book::with(['recommendedBy'])
            ->withCount(['reviews', 'likes'])
            ->whereHas('reviews');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->latest()->paginate(15);

        // Add user-specific information for authenticated users
        if ($request->user()) {
            foreach ($books as $book) {
                $book->user_has_liked = $book->isLikedByUser($request->user()->id);
                $book->user_has_reviewed = $book->isReviewedByUser($request->user()->id);
            }
        }

        return response()->json([
            'success' => true,
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
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
