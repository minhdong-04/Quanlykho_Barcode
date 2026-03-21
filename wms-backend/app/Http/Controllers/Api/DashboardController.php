<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        try {
            // Basic stats (guard against missing tables)
            $stats = [];

            $productsCount = Schema::hasTable('products') ? DB::table('products')->count() : 0;
            $inventoriesExist = Schema::hasTable('inventories');
            // Prefer the newer stock_movements table (used by StockInService/StockOutService)
            $stockMovementsExist = Schema::hasTable('stock_movements');
            // Legacy installs may still have stock_logs with type IN/OUT
            $stockLogsExist = Schema::hasTable('stock_logs');

            $lowStockCount = 0;
            $totalStock = 0;
            if ($inventoriesExist) {
                // If reorder_level column missing, guard using try/catch
                try {
                    $lowStockCount = DB::table('inventories')->whereColumn('quantity', '<=', 'reorder_level')->count();
                } catch (\Exception $e) {
                    $lowStockCount = 0;
                }

                try {
                    $totalStock = DB::table('inventories')->sum('quantity');
                } catch (\Exception $e) {
                    $totalStock = 0;
                }
            }

            $todayIn = 0;
            if ($stockMovementsExist) {
                try {
                    $todayIn = DB::table('stock_movements')
                        ->whereDate('created_at', Carbon::today())
                        ->where('type', 'in')
                        ->sum('quantity');
                } catch (\Exception $e) {
                    $todayIn = 0;
                }
            } elseif ($stockLogsExist) {
                try {
                    $todayIn = DB::table('stock_logs')->whereDate('created_at', Carbon::today())->where('type', 'IN')->sum('quantity');
                } catch (\Exception $e) {
                    $todayIn = 0;
                }
            }

            $stats = [
                ['label' => 'Tổng sản phẩm', 'value' => $productsCount],
                ['label' => 'Sắp hết hàng', 'value' => $lowStockCount],
                ['label' => 'Tồn kho', 'value' => $totalStock],
                ['label' => 'Nhập hôm nay', 'value' => $todayIn],
            ];

            // Chart: last 7 days inbound vs outbound
            $days = 7;
            $labels = [];
            $inData = [];
            $outData = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('d/m');

                if ($stockMovementsExist) {
                    try {
                        $in = DB::table('stock_movements')
                            ->whereDate('created_at', $date->toDateString())
                            ->where('type', 'in')
                            ->sum('quantity');

                        $out = DB::table('stock_movements')
                            ->whereDate('created_at', $date->toDateString())
                            ->where('type', 'out')
                            ->sum('quantity');

                        $inData[] = (int) $in;
                        $outData[] = (int) $out;
                    } catch (\Exception $e) {
                        $inData[] = 0;
                        $outData[] = 0;
                    }
                } elseif ($stockLogsExist) {
                    try {
                        $in = DB::table('stock_logs')
                            ->whereDate('created_at', $date->toDateString())
                            ->where('type', 'IN')
                            ->sum('quantity');

                        $out = DB::table('stock_logs')
                            ->whereDate('created_at', $date->toDateString())
                            ->where('type', 'OUT')
                            ->sum('quantity');

                        $inData[] = (int) $in;
                        $outData[] = (int) $out;
                    } catch (\Exception $e) {
                        $inData[] = 0;
                        $outData[] = 0;
                    }
                } else {
                    $inData[] = 0;
                    $outData[] = 0;
                }
            }

            return response()->json([
                'stats' => $stats,
                'chart' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Nhập',
                            'backgroundColor' => '#4CAF50',
                            'data' => $inData,
                        ],
                        [
                            'label' => 'Xuất',
                            'backgroundColor' => '#F44336',
                            'data' => $outData,
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard summary failed: '.$e->getMessage());

            // Return safe defaults so UI doesn't break
            $labels = [];
            $inData = [];
            $outData = [];
            for ($i = 6; $i >= 0; $i--) {
                $labels[] = Carbon::today()->subDays($i)->format('d/m');
                $inData[] = 0;
                $outData[] = 0;
            }

            return response()->json([
                'stats' => [
                    ['label' => 'Tổng sản phẩm', 'value' => 0],
                    ['label' => 'Sắp hết hàng', 'value' => 0],
                    ['label' => 'Tồn kho', 'value' => 0],
                    ['label' => 'Nhập hôm nay', 'value' => 0],
                ],
                'chart' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Nhập', 'backgroundColor' => '#4CAF50', 'data' => $inData],
                        ['label' => 'Xuất', 'backgroundColor' => '#F44336', 'data' => $outData],
                    ],
                ],
            ], 200);
        }
    }
}
