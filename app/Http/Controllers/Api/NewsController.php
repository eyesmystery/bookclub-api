<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = News::with('division');

        // Filter by division if provided
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter by published news only
        if ($request->has('published') && $request->published) {
            $query->whereNotNull('published_at');
        }

        $news = $query->latest('published_at')->paginate(15);

        return response()->json([
            'news' => $news,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();
        
        // Auto-publish if published_at is provided
        if ($request->has('published_at') && $request->published_at) {
            $data['published_at'] = now();
        }

        $news = News::create($data);
        $news->load('division');

        return response()->json([
            'message' => 'News created successfully',
            'news' => $news,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        $news->load('division');
        
        return response()->json([
            'news' => $news,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsRequest $request, News $news)
    {
        $data = $request->validated();
        
        // Handle publishing/unpublishing
        if ($request->has('published_at')) {
            $data['published_at'] = $request->published_at ? now() : null;
        }

        $news->update($data);
        $news->load('division');

        return response()->json([
            'message' => 'News updated successfully',
            'news' => $news,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        $news->delete();

        return response()->json([
            'message' => 'News deleted successfully',
        ]);
    }
}
