<?php
namespace App\Http\Controllers\Api;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class UserController extends Controller
{
    /**
     * Get all users
     */
    public function index(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $query = User::query();

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            return response()->json($query->orderBy('created_at', 'desc')->get());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single user
     */
    public function show(int $id)
    {
        try {
            // Check authorization
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new user
     */
    public function store(UserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => $request->validated('role'),
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing user
     */
    public function update(UserRequest $request, int $id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $updateData = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'role' => $request->validated('role'),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = $request->validated('password');
            }

            $user->update($updateData);

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy(int $id)
    {
        try {
            // Check authorization
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Prevent deleting the current user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'message' => 'Cannot delete your own account'
                ], 422);
            }

            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

