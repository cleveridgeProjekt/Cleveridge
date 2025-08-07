<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function mustHaveList(Request $request)
    {
        return $request->user()->mustHaveProducts()->get();
    }

    public function addMustHave(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $request->user()->mustHaveProducts()->syncWithoutDetaching($request->product_id);
        return response()->json(['success' => true]);
    }

    public function removeMustHave(Request $request, Product $product)
    {
        $request->user()->mustHaveProducts()->detach($product->id);
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

