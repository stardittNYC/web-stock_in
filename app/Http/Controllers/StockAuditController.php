<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StockAuditController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('business');

        $products = Product::where('business_id', $business->id)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'current_stock', 'category_id']);

        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        return view('dashboard.stock-audit', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $business = $request->attributes->get('business');

        $productId = filter_var($request->input('product_id'), FILTER_VALIDATE_INT);
        $physicalStock = filter_var($request->input('physical_stock'), FILTER_VALIDATE_INT);
        $notes = trim((string) $request->input('notes', ''));

        if (
            $productId === false || $productId === null
            || $physicalStock === false || $physicalStock === null || $physicalStock < 0
        ) {
            return response()->json([
                'success' => false,
                'message' => t('invalid_data'),
            ]);
        }

        try {
            DB::beginTransaction();

            $product = Product::where('id', $productId)
                ->where('business_id', $business->id)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw new \Exception(t('product_not_found'));
            }

            $stockBefore = (int) $product->current_stock;
            $difference = $physicalStock - $stockBefore;

            if ($difference === 0) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => t('no_stock_change'),
                ]);
            }

            $product->update(['current_stock' => $physicalStock]);

            InventoryTransaction::create([
                'business_id' => $business->id,
                'product_id' => $productId,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'transaction_type' => 'stock_audit',
                'quantity' => $difference,
                'stock_before' => $stockBefore,
                'stock_after' => $physicalStock,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => t('audit_save_success'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
