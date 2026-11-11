<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * User Login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Email atau password tidak sesuai', null, 401);
        }

        $token = $user->createToken('pos-token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 'Login berhasil');
    }

    /**
     * User Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil');
    }

    /**
     * Get Current Authenticated User
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'role' => $request->user()->role,
        ], 'Data user berhasil diambil');
    }

    /**
     * List all users
     */
    public function indexUsers(Request $request): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('id', 'desc')
            ->get();

        return $this->successResponse($users, 'Daftar pengguna berhasil diambil');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,admin,kasir',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return $this->successResponse($user, 'Pengguna berhasil ditambahkan', 201);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Pengguna tidak ditemukan', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:owner,admin,kasir',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return $this->successResponse($user, 'Data pengguna berhasil diperbarui');
    }

    /**
     * Delete user
     */
    public function destroyUser($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Pengguna tidak ditemukan', null, 404);
        }

        $user->delete();

        return $this->successResponse(null, 'Pengguna berhasil dihapus');
    }
}
