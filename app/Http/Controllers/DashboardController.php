<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // You can fetch products or shopping list from database here
        // For now, use demo data:
        $products = [
            ['name' => 'Pizza', 'img' => 'pizza.jpg'],
            ['name' => 'Tomate', 'img' => 'tomate.JPG'],
        ];

        $shoppingList = [
            ['name' => 'Milch', 'amount' => 2],
            ['name' => 'Tomaten', 'amount' => 5],
        ];

        return view('dashboard', compact('products', 'shoppingList'));
    }
}
