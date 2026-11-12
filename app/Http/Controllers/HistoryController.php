<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('business');

        $allowedTypes = ['stock_in', 'stock_out', 'stock_audit'];

        $type = (string) $request->input('type', '');
        $dateFrom = (string) $request->input('date_from', '');
        $dateTo = (string) $request->input('date_to', '');
        $search = trim((string) $request->input('search', ''));

        if (! in_array($type, $allowedTypes, true)) {
            $type = '';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = '';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = '';
        }

        $query = InventoryTransaction::where('business_id', $business->id)
            ->select([
                'id',
                'product_name',
                'product_code',
                'transaction_type',
                'quantity',
                'stock_before',
                'stock_after',
                'notes',
                'transaction_date',
                'selling_price',
                'total_amount',
            ]);

        if ($type !== '') {
            $query->where('transaction_type', $type);
        }

        if ($dateFrom !== '') {
            $query->whereRaw('DATE(transaction_date) >= ?', [$dateFrom]);
        }

        if ($dateTo !== '') {
            $query->whereRaw('DATE(transaction_date) <= ?', [$dateTo]);
        }

        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('product_name', 'like', '%'.$search.'%')
                    ->orWhere('product_code', 'like', '%'.$search.'%')
                    ->orWhere('notes', 'like', '%'.$search.'%');
            });
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $total = $transactions->count();

        return view('dashboard.history', compact(
            'transactions',
            'total',
            'type',
            'dateFrom',
            'dateTo',
            'search',
            'allowedTypes'
        ));
    }
}
