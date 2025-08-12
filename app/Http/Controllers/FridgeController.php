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
    public function update(Request $request, Fridge $fridge)
    {
        $this->authorize('update', $fridge);

        $data = $request->validate([
            'name'         => 'nullable|string|max:255',
            'temperature'  => 'nullable|numeric',
            'humidity'     => 'nullable|numeric',
        ]);

        $fridge->update($data);

        return $fridge->load('items.product');
    }

    // Delete fridge
    public function destroy(Fridge $fridge)
    {
        $this->authorize('delete', $fridge);

        $fridge->items()->delete();
        $fridge->sensors()->delete();

        $fridge->delete();

        return response()->noContent();
    }
}

