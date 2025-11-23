<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CameraController extends Controller
{
    // 1. TRIGGER (Web Button)
    public function triggerCommand()
    {
        Cache::put('camera_trigger', true, 60);
        return response()->json(['status' => 'Command sent to Pi']);
    }

    // 2. CHECK (Raspberry Pi)
    public function checkCommand()
    {
        $shouldSnap = Cache::get('camera_trigger', false);

        if ($shouldSnap) {
            Cache::forget('camera_trigger');
        }

        return response()->json(['snap' => $shouldSnap]);
    }

    // 3. UPLOAD (Raspberry Pi)
    public function uploadPhotoAndResults(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file'], 400);
        }

        $path = $request->file('file')->store('uploads', 'public');

        $filename = basename($path);
        

        $forcedUrl = '/storage/uploads/' . $filename;

        $products = json_decode($request->input('products', '[]'));

        $data = [
            'image_url' => $forcedUrl, 
            'products' => $products,
            'timestamp' => now()->toDateTimeString()
        ];
        
        Cache::put('latest_camera_data', $data);

        return response()->json(['status' => 'success', 'path' => $path]);
    }

    // 4. GET DATA (Vue Frontend)
    public function getLatestData()
    {
        $data = Cache::get('latest_camera_data', [
            'image_url' => null,
            'products' => []
        ]);

        return response()->json($data);
    }
}
