<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function showCurrent(Request $request)
    {
        $list = $request->user()
            ->shoppingLists()
            ->firstOrCreate(['name' => 'default']);

        $list->load(['items.product']);
        return $list;
    }
}
