<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FridgeItem;

class ExpiryController extends Controller
{
    public function index(Request $r)
    {
        $user = $r->user();
        $days = max((int) $r->query('days', 3), 1);

        $fridgeIds = $user->fridges()->pluck('id');
        $from = today()->toDateString();
        $to   = today()->addDays($days)->toDateString();

        $expired = FridgeItem::with(['product', 'fridge'])
            ->whereIn('fridge_id', $fridgeIds)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $from)
            ->orderBy('expiry_date')
            ->get();

        $soon = FridgeItem::with(['product', 'fridge'])
            ->whereIn('fridge_id', $fridgeIds)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$from, $to])
            ->orderBy('expiry_date')
            ->get();

        return [
            'expired' => $expired,
            'soon'    => $soon,
        ];
    }
}
