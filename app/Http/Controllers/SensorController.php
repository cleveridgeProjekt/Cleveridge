<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function store(Request $request)
{
    Log::info('API /api/sensor called'); // This logs when the endpoint is hit
    Log::info('Sensor request payload:', $request->all()); // Logs the incoming data

    // Example data handling
    $temperature = $request->input('temperature');
    $humidity = $request->input('humidity');

    // Optional error check
    if (!$temperature || !$humidity) {
        Log::warning('Missing sensor data!', ['temperature' => $temperature, 'humidity' => $humidity]);
        return response()->json(['error' => 'Invalid data'], 400);
    }

    // Proceed with saving data or whatever is needed
    Log::info('Sensor data saved successfully');

    return response()->json(['status' => 'success']);
}
}
