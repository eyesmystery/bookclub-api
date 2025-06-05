<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of all books in the club library.
     */
    public function index(Request $request)
    {
        $query = Book::with(['recommendedBy'])
            ->withCount(['likes', 'reviews']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by author
        if ($request->has('author') && $request->author) {
            $query->where('author', 'like', "%{$request->author}%");
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortDir);
                break;
            case 'author':
                $query->orderBy('author', $sortDir);
                break;
            case 'popular':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'reviewed':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', $sortDir);
                break;
        }

        $books = $query->paginate($request->get('per_page', 15));

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
     * Store a newly created book in the club library.
     */
    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());
        $book->load(['recommendedBy']);
        $book->loadCount(['likes', 'reviews']);

        return response()->json([
            'success' => true,
            'message' => 'Book added to club library successfully',
            'book' => $book,
        ], 201);
    }

    /**
     * Display the specified book from the club library.
     */
    public function show(Request $request, Book $book)
    {
        $book->load(['recommendedBy']);
        $book->loadCount(['likes', 'reviews']);
        
        // Add user-specific information for authenticated users
        if ($request->user()) {
            $book->user_has_liked = $book->isLikedByUser($request->user()->id);
            $book->user_has_reviewed = $book->isReviewedByUser($request->user()->id);
        }
        
        return response()->json([
            'success' => true,
            'book' => $book,
        ]);
    }

    /**
     * Update the specified book in the club library.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());
        $book->load(['recommendedBy']);
        $book->loadCount(['likes', 'reviews']);

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'book' => $book,
        ]);
    }

    /**
     * Remove the specified book from the club library.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book removed from club library successfully',
        ]);
    }

    /**
     * Get popular books (most liked).
     */
    public function popular(Request $request)
    {
        $books = Book::with(['recommendedBy'])
            ->withCount(['likes', 'reviews'])
            ->orderBy('likes_count', 'desc')
            ->limit($request->get('limit', 10))
            ->get();

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
     * Get recently added books.
     */
    public function recent(Request $request)
    {
        $books = Book::with(['recommendedBy'])
            ->withCount(['likes', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 10))
            ->get();

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
}
