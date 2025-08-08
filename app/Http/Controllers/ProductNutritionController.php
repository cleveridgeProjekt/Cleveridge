<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductNutritionController extends Controller
{
    public function show(Product $product)
    {
        $nutrition = $product->nutrition;
        if (!$nutrition) {
            return response()->json(['message' => 'Nutrition not found'], 404);
        }
        return response()->json($nutrition);
    }
}
