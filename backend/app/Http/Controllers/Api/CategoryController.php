<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('name', 'asc')
            ->get();

        return $this->successResponse($categories, 'Daftar kategori berhasil diambil');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return $this->successResponse($category, 'Kategori berhasil ditambahkan', 201);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->errorResponse('Kategori tidak ditemukan', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return $this->successResponse($category, 'Kategori berhasil diperbarui');
    }

    /**
     * Remove the specified category
     */
    public function destroy($id): JsonResponse
    {
        $category = Category::withCount('products')->find($id);

        if (!$category) {
            return $this->errorResponse('Kategori tidak ditemukan', null, 404);
        }

        if ($category->products_count > 0) {
            return $this->errorResponse('Tidak dapat menghapus kategori yang masih memiliki produk', null, 422);
        }

        $category->delete();

        return $this->successResponse(null, 'Kategori berhasil dihapus');
    }
}
