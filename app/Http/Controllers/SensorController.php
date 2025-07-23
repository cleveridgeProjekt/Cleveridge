<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        $temperature = $request->input('temperature');
        $humidity = $request->input('humidity');

        // For now, just return a response (or later, save to DB)
        return response()->json([
            'status' => 'success',
            'temperature' => $temperature,
            'humidity' => $humidity,
        ]);
    }
}
