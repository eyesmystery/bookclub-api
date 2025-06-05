<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Article::with(['author', 'division']);

        // Filter by division if provided
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter by published articles only
        if ($request->has('published') && $request->published) {
            $query->whereNotNull('published_at');
        }

        $articles = $query->latest('published_at')->paginate(15);

        return response()->json([
            'articles' => $articles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $data['author_id'] = auth()->id();
        
        // Auto-publish if published_at is provided
        if ($request->has('published_at') && $request->published_at) {
            $data['published_at'] = now();
        }

        $article = Article::create($data);
        $article->load(['author', 'division']);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        $article->load(['author', 'division']);
        
        return response()->json([
            'article' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $data = $request->validated();
        
        // Handle publishing/unpublishing
        if ($request->has('published_at')) {
            $data['published_at'] = $request->published_at ? now() : null;
        }

        $article->update($data);
        $article->load(['author', 'division']);

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $article,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully',
        ]);
    }
}
