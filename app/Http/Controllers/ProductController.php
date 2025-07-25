<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()    { return Product::all(); }
    public function show($id)  { return Product::findOrFail($id); }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'barcode' => 'nullable|string',
            'default_expiry_days' => 'nullable|integer',
        ]);
        return Product::create($data);
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->only('name', 'barcode', 'default_expiry_days'));
        return $product;
    }
    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
