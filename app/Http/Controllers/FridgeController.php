<?php

namespace App\Http\Controllers;

use App\Models\Fridge;
use Illuminate\Http\Request;

class FridgeController extends Controller
{
    // List all fridges for authenticated user
    public function index()
    {
        return auth()->user()->fridges()->with('items.product')->get();
    }

    // Show a single fridge (with items)
    public function show(Fridge $fridge)
    {
        $this->authorize('view', $fridge);
        return $fridge->load('items.product');
    }

    // Create a new fridge
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
        ]);
        $fridge = auth()->user()->fridges()->create($data);
        return response()->json($fridge, 201);
    }

    // Update fridge
    public function update(Request $request, FridgeItem $item)
    {
        $this->authorize('update', $item->fridge);

        $data = $request->only('quantity', 'expiry_date');
        if (array_key_exists('expiry_date', $data) && $data['expiry_date'] === '') {
            $data['expiry_date'] = null;
        }

        $item->update($data);
        return $item->load('product');
    }

    // Delete fridge
    public function destroy(Fridge $fridge)
    {
        $this->authorize('delete', $fridge);

        $fridge->items()->delete();
        $fridge->delete();

        return response()->noContent();
    }
}

