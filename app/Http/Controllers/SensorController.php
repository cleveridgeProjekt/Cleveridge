<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SensorController extends Controller
{
    // Simple static storage (resets on each app restart)
    private static $latest = [
        'temperature' => null,
        'humidity' => null,
    ];

    public function store(Request $request)
    {
        $data = $request->only(['temperature', 'humidity']);
        
        // Optional: validate the incoming data
        $request->validate([
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
        ]);

        // Log for debugging
        Log::info('Sensor data received:', $data);

        // Store data temporarily
        self::$latest = $data;

        return response()->json(['message' => 'Sensor data stored'], 200);
    }

    public function latest()
    {
        return response()->json([
            'temperature' => self::$latest['temperature'],
            'humidity' => self::$latest['humidity'],
        ]);
    }
}
