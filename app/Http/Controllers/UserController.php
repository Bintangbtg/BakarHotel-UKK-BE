<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'nama_user' => 'required|string|max:100',
                'foto' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,resepsionis',
            ]);

            // Create the user
            $user = User::create([
                'nama_user' => $validated['nama_user'],
                'foto' => $validated['foto'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            // Return success response
            return response()->json([
                'message' => 'User created successfully.',
                'user' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['errors' => $e->errors()], 422);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);

        } catch (\Exception $e) {
            // Log the exception and return a generic error message
            Log::error('Error creating user: ' . $e->getMessage());

            return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

    public function show($id_user)
    {
        $user = User::findOrFail($id_user);
        return response()->json($user);
    }

    public function update(Request $request, $id_user)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'nama_user' => 'sometimes|string|max:100',
                'foto' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $id_user . ',id_user',
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|in:admin,resepsionis',
            ]);

            // Find the user by ID
            $user = User::findOrFail($id_user);

            // Update user fields
            $user->update($request->except('password'));

            // Hash the password only if it's provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->save(); // Save the user after updating the password
            }

            // Return success response
            return response()->json([
                'message' => 'User updated successfully.',
                'user' => $user
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation exceptions
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where user is not found
            return response()->json([
                'error' => 'User not found'
            ], 404);

        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id_user)
    {
        $user = User::findOrFail($id_user);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}