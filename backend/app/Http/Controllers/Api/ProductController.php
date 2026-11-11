<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of products with filtering, searching, and low-stock detection.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Search by Name, SKU, or Barcode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by Category
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Low Stock Alert
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Ordering
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        if ($request->boolean('all')) {
            $products = $query->get();
            return $this->successResponse($products, 'Katalog produk berhasil diambil');
        }

        $perPage = (int) $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        return $this->successResponse($products, 'Daftar produk berhasil diambil');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'barcode' => $request->barcode ?: $request->sku,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
            'stock' => $request->stock,
            'min_stock_alert' => $request->input('min_stock_alert', 5),
            'image' => $request->image,
            'description' => $request->description,
        ]);

        $product->load('category');

        return $this->successResponse($product, 'Produk berhasil ditambahkan', 201);
    }

    /**
     * Display the specified product
     */
    public function show($id): JsonResponse
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return $this->errorResponse('Produk tidak ditemukan', null, 404);
        }

        return $this->successResponse($product, 'Detail produk berhasil diambil');
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('Produk tidak ditemukan', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'barcode' => 'nullable|string|max:100',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'barcode' => $request->barcode ?: $product->barcode,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
            'stock' => $request->stock,
            'min_stock_alert' => $request->input('min_stock_alert', $product->min_stock_alert),
            'image' => $request->image,
            'description' => $request->description,
        ]);

        $product->load('category');

        return $this->successResponse($product, 'Produk berhasil diperbarui');
    }

    /**
     * Remove the specified product
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('Produk tidak ditemukan', null, 404);
        }

        $product->delete();

        return $this->successResponse(null, 'Produk berhasil dihapus');
    }

    /**
     * Adjust product stock (Stock Opname)
     */
    public function adjustStock(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('Produk tidak ditemukan', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:set,add,sub',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $oldStock = $product->stock;
        $qty = $request->quantity;

        if ($request->type === 'set') {
            $product->stock = $qty;
        } elseif ($request->type === 'add') {
            $product->stock += $qty;
        } elseif ($request->type === 'sub') {
            if ($product->stock < $qty) {
                return $this->errorResponse('Stok tidak boleh kurang dari nol', null, 422);
            }
            $product->stock -= $qty;
        }

        $product->save();

        return $this->successResponse([
            'product' => $product,
            'old_stock' => $oldStock,
            'new_stock' => $product->stock,
            'notes' => $request->notes,
        ], 'Penyesuaian stok berhasil disimpan');
    }

    /**
     * Get list of low stock products for smart alert
     */
    public function lowStockAlert(): JsonResponse
    {
        $products = Product::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->get();

        return $this->successResponse([
            'count' => $products->count(),
            'products' => $products,
        ], 'Data peringatan stok tipis berhasil diambil');
    }
}
