<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\UserMustHaveProduct;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function mustHaveList(Request $request)
    {
        return UserMustHaveProduct::with('product')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();
    }

    public function addMustHave(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
            'unit'       => 'nullable|string|max:30',
        ]);

        $entry = UserMustHaveProduct::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $data['product_id']],
            ['quantity' => $data['quantity'] ?? 1, 'unit' => $data['unit'] ?? 'Stück']
        );

        return UserMustHaveProduct::with('product')->find($entry->id);
    }

    public function updateMustHave(Request $request, UserMustHaveProduct $entry)
    {
        abort_unless($entry->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'unit'     => 'nullable|string|max:30',
        ]);

        $entry->update(array_filter($data, fn($v) => !is_null($v)));

        return response()->json(['success' => true]);
    }

    public function removeMustHave(Request $request, UserMustHaveProduct $entry)
    {
        abort_unless($entry->user_id === $request->user()->id, 403);
        $entry->delete();
        return response()->json(['success' => true]);
    }

    public function allergyList(Request $request)
    {
        return $request->user()->allergyProducts()->get();
    }

    public function addAllergy(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $request->user()->allergyProducts()->syncWithoutDetaching($request->product_id);
        return response()->json(['success' => true]);
    }

    public function removeAllergy(Request $request, Product $product)
    {
        $request->user()->allergyProducts()->detach($product->id);
        return response()->json(['success' => true]);
    }
}
