<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisions = Division::withCount(['users', 'books', 'events', 'articles', 'news'])->get();
        
        return response()->json([
            'divisions' => $divisions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDivisionRequest $request)
    {
        $division = Division::create($request->validated());

        return response()->json([
            'message' => 'Division created successfully',
            'division' => $division,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        $division->load(['users', 'books', 'events', 'articles', 'news']);
        
        return response()->json([
            'division' => $division,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDivisionRequest $request, Division $division)
    {
        $division->update($request->validated());

        return response()->json([
            'message' => 'Division updated successfully',
            'division' => $division,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $division->delete();

        return response()->json([
            'message' => 'Division deleted successfully',
        ]);
    }
}
