<?php

namespace App\Http\Controllers;

use App\Models\Fridge;
use App\Models\FridgeItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FridgeItemController extends Controller
{
    // Add item to fridge
    public function store(Request $request, $fridge_id)
    {
        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
        ]);

        if (($data['expiry_date'] ?? null) === '') {
            $data['expiry_date'] = null;
        }

        $fridge = \App\Models\Fridge::findOrFail($fridge_id);
        $this->authorize('update', $fridge);

        $item = $fridge->items()->create($data);
        return $item->load('product');
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = \App\Models\FridgeItem::findOrFail($id);
        $fridge = $item->fridge;
        $this->authorize('update', $fridge);

        $data = $request->only('quantity', 'expiry_date');

        if (array_key_exists('expiry_date', $data) && $data['expiry_date'] === '') {
            $data['expiry_date'] = null;
        }

        $item->update($data);
        return $item->load('product');
    }

    // Delete item
    public function destroy(FridgeItem $item)
    {
        $this->authorize('update', $item->fridge);
        $item->delete();
        return response()->noContent();
    }
}
