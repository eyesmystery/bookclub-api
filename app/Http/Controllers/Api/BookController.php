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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with(['division', 'recommendedBy'])
            ->withCount(['likes', 'reviews']);

        // Filter by division if provided
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        $books = $query->latest()->paginate(15);

        return response()->json([
            'books' => $books,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());
        $book->load(['division', 'recommendedBy']);
        $book->loadCount(['likes', 'reviews']);

        return response()->json([
            'message' => 'Book created successfully',
            'book' => $book,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Book $book)
    {
        $book->load(['division', 'recommendedBy']);
        $book->loadCount(['likes', 'reviews']);
        
        // Add information about whether the current user has liked this book
        if ($request->user()) {
            $book->is_liked_by_user = $book->isLikedByUser($request->user()->id);
        }
        
        return response()->json([
            'book' => $book,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());
        $book->load(['division', 'recommendedBy']);
        $book->loadCount(['likes', 'reviews']);

        return response()->json([
            'message' => 'Book updated successfully',
            'book' => $book,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
