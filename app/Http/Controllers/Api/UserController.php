<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('division');

        // Filter by division if provided
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15);

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Users can only see their own profile unless they're admin
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            return response()->json([
                'message' => 'Access denied.',
            ], 403);
        }

        $user->load(['division', 'recommendedBooks', 'articles']);
        
        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Users can only update their own profile unless they're admin
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            return response()->json([
                'message' => 'Access denied.',
            ], 403);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'division_id' => 'sometimes|required|exists:divisions,id',
        ];

        // Only admins can change roles
        if (auth()->user()->isAdmin()) {
            $rules['role'] = 'sometimes|in:admin,moderator,user';
        }

        $validatedData = $request->validate($rules);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);
        $user->load('division');

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
