<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Sensor;

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
        // Persist to the database
        Sensor::create([
            'temperature' => $data['temperature'],
            'humidity' => $data['humidity'],
        ]);

        return response()->json(['message' => 'Sensor data stored'], 200);
    }

public function latest()
{
    $latest = Sensor::latest()->first();

    if (!$latest) {
        return response()->json(['message' => 'No sensor data found'], 404);
    }

    return response()->json([
        'temperature' => $latest->temperature,
        'humidity' => $latest->humidity,
        'timestamp' => $latest->created_at,
    ]);
}

}
