<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('business');

        $products = Product::where('business_id', $business->id)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'current_stock', 'selling_price', 'category_id']);

        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        return view('dashboard.stock-out', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $business = $request->attributes->get('business');
        $businessId = $business->id;

        $productId = (int) $request->input('product_id');
        $quantity = filter_var($request->input('quantity'), FILTER_VALIDATE_INT);
        $notes = trim((string) $request->input('notes', ''));

        if ($productId < 1 || $quantity === false || $quantity === null || $quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => t('invalid_data'),
            ]);
        }

        try {
            DB::beginTransaction();

            $product = Product::where('id', $productId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => t('product_not_found'),
                ]);
            }

            if ((int) $product->current_stock < $quantity) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => t('insufficient_stock'),
                ]);
            }

            $stockBefore = (int) $product->current_stock;
            $stockAfter = $stockBefore - $quantity;
            $totalAmount = $quantity * (float) $product->selling_price;

            $product->update(['current_stock' => $stockAfter]);

            InventoryTransaction::create([
                'business_id' => $businessId,
                'product_id' => $productId,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'transaction_type' => 'stock_out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => t('stock_out_save_success'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => t('history_save_failed'),
            ]);
        }
    }
}
