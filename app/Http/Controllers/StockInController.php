<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('business');

        $products = Product::where('business_id', $business->id)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'product_code', 'current_stock', 'category_id']);

        $productCount = $products->count();

        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        return view('dashboard.stock-in', compact('products', 'productCount', 'categories'));
    }

    public function store(Request $request)
    {
        $business = $request->attributes->get('business');
        $businessId = $business->id;

        $productId = (int) $request->input('product_id');
        $quantity = filter_var($request->input('quantity'), FILTER_VALIDATE_INT);

        $notesInput = $request->input('notes', '');
        $notes = is_string($notesInput) ? trim($notesInput) : '';
        $notes = $notes === '' ? null : $notes;

        if ($productId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_business_product'),
            ], 400);
        }

        if ($quantity === false || $quantity === null || $quantity < 1) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_quantity'),
            ], 400);
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
                    'status' => 'error',
                    'message' => t('product_access_denied'),
                ], 404);
            }

            $stockBefore = (int) $product->current_stock;
            $maxStock = 2147483647;

            if ($quantity > $maxStock - $stockBefore) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => t('stock_limit_exceeded'),
                ], 400);
            }

            $stockAfter = $stockBefore + $quantity;

            $product->update(['current_stock' => $stockAfter]);

            $transaction = InventoryTransaction::create([
                'business_id' => $businessId,
                'product_id' => $productId,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'transaction_type' => 'stock_in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'total_amount' => 0,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => t('stock_in_save_success'),
                'transaction_id' => $transaction->id,
                'product_id' => $productId,
                'quantity_added' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('stock_in_save_failed'),
            ], 500);
        }
    }
}
