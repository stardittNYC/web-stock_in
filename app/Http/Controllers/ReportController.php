<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private const SORTABLE = ['product_name', 'stock_in', 'stock_out', 'profit', 'current_stock'];

    public function index(Request $request)
    {
        $business = $request->attributes->get('business');
        [$dateFrom, $dateTo] = $this->resolveDates($request);

        $sort = (string) $request->input('sort', 'profit');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'profit';
        }

        $order = strtoupper((string) $request->input('order', 'DESC'));
        if (! in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $reports = $this->aggregateQuery($business->id, $dateFrom, $dateTo)
            ->orderBy($sort, $order)
            ->get();

        $totalProducts = $reports->count();
        $totalStock = $reports->sum(fn ($row) => (int) $row->current_stock);
        $totalStockIn = $reports->sum(fn ($row) => (int) $row->stock_in);
        $totalStockOut = $reports->sum(fn ($row) => (int) $row->stock_out);
        $totalProfit = $reports->sum(fn ($row) => (float) $row->profit);

        $totalModalValue = (float) DB::table('products')
            ->where('business_id', $business->id)
            ->sum(DB::raw('current_stock * purchase_price'));

        return view('dashboard.reports', compact(
            'reports',
            'totalProducts',
            'totalStock',
            'totalStockIn',
            'totalStockOut',
            'totalProfit',
            'totalModalValue',
            'dateFrom',
            'dateTo',
            'sort',
            'order'
        ));
    }

    public function analytics(Request $request)
    {
        $business = $request->attributes->get('business');
        [$dateFrom, $dateTo] = $this->resolveDates($request);

        $rows = $this->aggregateQuery($business->id, $dateFrom, $dateTo)
            ->orderByDesc('stock_out')
            ->limit(10)
            ->get();

        $productNames = $rows->pluck('product_name')->all();
        $stockIns = $rows->map(fn ($row) => (int) $row->stock_in)->all();
        $stockOuts = $rows->map(fn ($row) => (int) $row->stock_out)->all();
        $profits = $rows->map(fn ($row) => (float) $row->profit)->all();

        return view('dashboard.reports-analytics', compact(
            'productNames',
            'stockIns',
            'stockOuts',
            'profits',
            'dateFrom',
            'dateTo'
        ));
    }

    public function exportPdf(Request $request)
    {
        $business = $request->attributes->get('business');
        [$dateFrom, $dateTo] = $this->resolveDates($request);

        $reports = $this->aggregateQuery($business->id, $dateFrom, $dateTo)
            ->orderBy('product_name', 'asc')
            ->get();

        $totalProducts = $reports->count();
        $totalStock = $reports->sum(fn ($row) => (int) $row->current_stock);
        $totalStockIn = $reports->sum(fn ($row) => (int) $row->stock_in);
        $totalStockOut = $reports->sum(fn ($row) => (int) $row->stock_out);
        $totalProfit = $reports->sum(fn ($row) => (float) $row->profit);

        $totalModalValue = (float) DB::table('products')
            ->where('business_id', $business->id)
            ->sum(DB::raw('current_stock * purchase_price'));

        return view('dashboard.export-pdf', compact(
            'reports',
            'totalProducts',
            'totalStock',
            'totalStockIn',
            'totalStockOut',
            'totalProfit',
            'totalModalValue',
            'dateFrom',
            'dateTo'
        ));
    }

    private function aggregateQuery(int $businessId, string $dateFrom, string $dateTo)
    {
        return Product::query()
            ->leftJoin('inventory_transactions as it', function ($join) use ($dateFrom, $dateTo) {
                $join->on('it.product_id', '=', 'products.id')
                    ->whereRaw('DATE(it.transaction_date) BETWEEN ? AND ?', [$dateFrom, $dateTo]);
            })
            ->where('products.business_id', $businessId)
            ->groupBy('products.id', 'products.product_name', 'products.current_stock')
            ->selectRaw('products.id, products.product_name, products.current_stock')
            ->selectRaw("COALESCE(SUM(CASE WHEN it.transaction_type = 'stock_in' THEN it.quantity ELSE 0 END), 0) AS stock_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN it.transaction_type = 'stock_out' THEN it.quantity ELSE 0 END), 0) AS stock_out")
            ->selectRaw("COALESCE(SUM(CASE WHEN it.transaction_type = 'stock_out' THEN (it.selling_price - it.purchase_price) * it.quantity ELSE 0 END), 0) AS profit");
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveDates(Request $request): array
    {
        $dateFrom = (string) $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = (string) $request->input('date_to', now()->format('Y-m-d'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = now()->startOfMonth()->format('Y-m-d');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = now()->format('Y-m-d');
        }

        return [$dateFrom, $dateTo];
    }
}
