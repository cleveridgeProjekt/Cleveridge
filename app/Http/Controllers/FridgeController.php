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
    public function show($id)
    {
        $fridge = Fridge::with('items.product')->findOrFail($id);
        $this->authorize('view', $fridge);
        return $fridge;
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
    public function update(Request $request, $id)
    {
        $fridge = Fridge::findOrFail($id);
        $this->authorize('update', $fridge);
        $fridge->update($request->only('name', 'temperature', 'humidity'));
        return $fridge;
    }

    // Delete fridge
    public function destroy($id)
    {
        $fridge = Fridge::findOrFail($id);
        $this->authorize('delete', $fridge);
        $fridge->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

