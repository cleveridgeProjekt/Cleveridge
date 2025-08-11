<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;

class ShoppingListItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name'       => 'nullable|string',
            'quantity'   => 'nullable|integer|min:1',
            'unit'       => 'nullable|string|max:30',
        ]);

        if (empty($data['product_id']) && empty($data['name'])) {
            return response()->json(['message' => 'Either product_id or name is required'], 422);
        }

        $list = $request->user()
            ->shoppingLists()
            ->firstOrCreate(['name' => 'default']);

        $name = $data['name'] ?? (
        !empty($data['product_id']) ? Product::find($data['product_id'])->name : null
        );

        $item = $list->items()->create([
            'product_id'  => $data['product_id'] ?? null,
            'name'        => $name,
            'quantity'    => $data['quantity'] ?? 1,
            'unit'        => $data['unit'] ?? 'Stück',
            'checked_off' => false,
        ]);

        $item->load('product');
        return response()->json($item, 201);
    }

    public function update(Request $request, ShoppingListItem $item)
    {
        abort_if($item->shoppingList->user_id !== $request->user()->id, 403);

        $item->update($request->only('quantity','unit','checked_off'));
        $item->load('product');

        return $item;
    }

    public function destroy(Request $request, ShoppingListItem $item)
    {
        abort_if($item->shoppingList->user_id !== $request->user()->id, 403);

        $item->delete();
        return response()->json(['success' => true]);
    }
}
