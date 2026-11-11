<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get POS Dashboard statistics and analytics.
     */
    public function summary(Request $request): JsonResponse
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Revenue calculations
        $todayRevenue = (float) Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $yesterdayRevenue = (float) Transaction::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthRevenue = (float) Transaction::whereBetween('created_at', [$startOfMonth, Carbon::now()])
            ->where('status', 'completed')
            ->sum('total_amount');

        // 2. Transaction counts
        $todayTransactionsCount = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $monthTransactionsCount = Transaction::whereBetween('created_at', [$startOfMonth, Carbon::now()])
            ->where('status', 'completed')
            ->count();

        // 3. Products summary
        $totalProductsCount = Product::count();
        $lowStockCount = Product::lowStock()->count();

        // 4. Top Selling Products
        $topSellingProducts = TransactionDetail::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty_sold'),
                DB::raw('SUM(subtotal) as total_sales_amount')
            )
            ->with('product:id,name,sku,barcode,image,sell_price')
            ->groupBy('product_id')
            ->orderByDesc('total_qty_sold')
            ->limit(5)
            ->get();

        // 5. 7-Day Sales Trend Chart Data
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayLabel = $date->translatedFormat('D, d M');

            $daySales = (float) Transaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');

            $dayCount = Transaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->count();

            $salesTrend[] = [
                'date' => $dateStr,
                'label' => $dayLabel,
                'revenue' => $daySales,
                'count' => $dayCount,
            ];
        }

        // 6. Payment method breakdown for today
        $paymentMethods = Transaction::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();

        // 7. Recent Transactions (last 5)
        $recentTransactions = Transaction::with(['user:id,name', 'details.product:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse([
            'metrics' => [
                'today_revenue' => $todayRevenue,
                'yesterday_revenue' => $yesterdayRevenue,
                'month_revenue' => $monthRevenue,
                'today_transactions' => $todayTransactionsCount,
                'month_transactions' => $monthTransactionsCount,
                'total_products' => $totalProductsCount,
                'low_stock_count' => $lowStockCount,
            ],
            'top_products' => $topSellingProducts,
            'sales_trend' => $salesTrend,
            'payment_methods' => $paymentMethods,
            'recent_transactions' => $recentTransactions,
        ], 'Ringkasan dashboard analitik berhasil diambil');
    }
}
