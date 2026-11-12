<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $business = $request->attributes->get('business');
        $categoryName = trim((string) $request->input('category_name', ''));

        if ($categoryName === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('category_name_required'),
            ]);
        }

        $exists = Category::where('business_id', $business->id)
            ->whereRaw('LOWER(category_name) = LOWER(?)', [$categoryName])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => t('category_exists'),
            ]);
        }

        try {
            $category = Category::create([
                'business_id' => $business->id,
                'category_name' => $categoryName,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('category_save_failed'),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => t('category_save_success'),
            'category' => [
                'id' => $category->id,
                'category_name' => $category->category_name,
            ],
        ]);
    }
}
