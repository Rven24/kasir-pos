<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of transactions with filtering by date, cashier, or status.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['user:id,name,role', 'details.product:id,name,sku,barcode,image'])
            ->orderBy('created_at', 'desc');

        // Search by Invoice Number or Customer Name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by Date Range
        if ($request->filled('date_range')) {
            $range = $request->date_range;
            if ($range === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($range === 'yesterday') {
                $query->whereDate('created_at', Carbon::yesterday());
            } elseif ($range === 'week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($range === 'month') {
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        // Filter by Payment Method
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $perPage = (int) $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return $this->successResponse($transactions, 'Riwayat transaksi berhasil diambil');
    }

    /**
     * Display transaction detail by ID or Invoice Number.
     */
    public function show($identifier): JsonResponse
    {
        $transaction = Transaction::with(['user:id,name,role', 'details.product:id,name,sku,barcode,image'])
            ->where('id', $identifier)
            ->orWhere('invoice_number', $identifier)
            ->first();

        if (!$transaction) {
            return $this->errorResponse('Transaksi tidak ditemukan', null, 404);
        }

        return $this->successResponse($transaction, 'Detail transaksi berhasil diambil');
    }

    /**
     * Process POS Checkout using DB::transaction()
     */
    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris,transfer',
            'pay_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Data transaksi tidak valid', $validator->errors(), 422);
        }

        try {
            $transaction = DB::transaction(function () use ($request) {
                $items = $request->items;
                $subtotal = 0;
                $detailsToInsert = [];

                // 1. Verify stocks and calculate subtotal
                foreach ($items as $item) {
                    // Lock product row for update to prevent race conditions during high checkout load
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (!$product) {
                        throw new Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new Exception("Stok produk '{$product->name}' tidak mencukupi (Tersisa: {$product->stock}, Diminta: {$item['quantity']}).");
                    }

                    $itemSubtotal = $product->sell_price * $item['quantity'];
                    $subtotal += $itemSubtotal;

                    // Deduct stock automatically
                    $product->decrement('stock', $item['quantity']);

                    $detailsToInsert[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->sell_price,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                // 2. Calculate tax and totals
                $discount = (float) $request->input('discount', 0);
                $taxableAmount = max(0, $subtotal - $discount);
                $taxRate = 0.11; // 11% PPN
                $tax = round($taxableAmount * $taxRate);
                $totalAmount = $taxableAmount + $tax;

                $payAmount = (float) $request->pay_amount;

                // QRIS / Transfer auto-match if pay_amount is exact total
                if ($request->payment_method !== 'cash' && $payAmount < $totalAmount) {
                    $payAmount = $totalAmount;
                }

                if ($payAmount < $totalAmount) {
                    throw new Exception("Jumlah bayar (Rp " . number_format($payAmount, 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($totalAmount, 0, ',', '.') . ").");
                }

                $changeAmount = $payAmount - $totalAmount;

                // 3. Generate unique invoice number: INV-YYYYMMDD-XXXX
                $todayDate = Carbon::now()->format('Ymd');
                $dailyCount = Transaction::whereDate('created_at', Carbon::today())->count() + 1;
                $invoiceNumber = 'INV-' . $todayDate . '-' . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);

                // Double check uniqueness
                while (Transaction::where('invoice_number', $invoiceNumber)->exists()) {
                    $dailyCount++;
                    $invoiceNumber = 'INV-' . $todayDate . '-' . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);
                }

                // 4. Create Transaction record
                $userId = $request->user() ? $request->user()->id : null;

                $trx = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => $userId,
                    'customer_name' => $request->customer_name ?: 'Pelanggan Umum',
                    'total_amount' => $totalAmount,
                    'discount' => $discount,
                    'tax' => $tax,
                    'pay_amount' => $payAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $request->payment_method,
                    'status' => 'completed',
                    'notes' => $request->notes,
                ]);

                // 5. Create Transaction Details
                foreach ($detailsToInsert as $detail) {
                    $detail['transaction_id'] = $trx->id;
                    $detail['created_at'] = Carbon::now();
                    $detail['updated_at'] = Carbon::now();
                    TransactionDetail::create($detail);
                }

                return $trx;
            });

            // Reload transaction with relations for instant receipt printing
            $transaction->load(['user:id,name,role', 'details.product:id,name,sku,barcode,image']);

            return $this->successResponse($transaction, 'Transaksi berhasil diselesaikan', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
