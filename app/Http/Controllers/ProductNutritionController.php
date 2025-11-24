<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductNutrition;
use Illuminate\Http\Request;

class ProductNutritionController extends Controller
{
    public function show(Product $product)
    {
        $nutrition = $product->nutrition;
        if (!$nutrition) return response()->json(['message' => 'Nutrition not found'], 404);
        return response()->json($nutrition);
    }

    public function store(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $nutrition = ProductNutrition::updateOrCreate(
            ['product_id' => $product->id],
            $data
        );
        return response()->json($nutrition, 201);
    }

    public function update(Request $request, Product $product)
    {
        $nutrition = $product->nutrition;
        if (!$nutrition) {
            // create if missing
            return $this->store($request, $product);
        }
        $nutrition->update($this->validated($request));
        return response()->json($nutrition);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'short_description'    => 'nullable|string',
            'calories'             => 'nullable|numeric',
            'protein'              => 'nullable|numeric',
            'carbs'                => 'nullable|numeric',
            'sugar'                => 'nullable|numeric',
            'fiber'                => 'nullable|numeric',
            'fat'                  => 'nullable|numeric',
            'saturated_fat'        => 'nullable|numeric',
            'monounsaturated_fat'  => 'nullable|numeric',
            'polyunsaturated_fat'  => 'nullable|numeric',
            'salt'                 => 'nullable|numeric',
            'vitamins_minerals'    => 'nullable|string',
            'serving_size'         => 'nullable|string',
            'allergens'            => 'nullable|string',
            'common_uses'          => 'nullable|string',
        ]);
    }
}
