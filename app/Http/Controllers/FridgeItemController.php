<?php

namespace App\Http\Controllers;

use App\Models\Fridge;
use App\Models\FridgeItem;
use App\Models\Product;
use Illuminate\Http\Request;

class FridgeItemController extends Controller
{
    // Add item to fridge
    public function store(Request $request, $fridge_id)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
        ]);
        $fridge = Fridge::findOrFail($fridge_id);
        $this->authorize('update', $fridge);
        $item = $fridge->items()->create($data);
        return $item->load('product');
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = FridgeItem::findOrFail($id);
        $fridge = $item->fridge;
        $this->authorize('update', $fridge);
        $item->update($request->only('quantity', 'expiry_date'));
        return $item->load('product');
    }

    // Delete item
    public function destroy($id)
    {
        $item = FridgeItem::findOrFail($id);
        $fridge = $item->fridge;
        $this->authorize('update', $fridge);
        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
