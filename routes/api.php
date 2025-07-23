use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::post('/sensor', function (Request $request) {
    // You can validate or sanitize the request here
    $data = $request->only(['status', 'temperature', 'humidity']);

    // Save the data in cache or database (for now, we use cache)
    Cache::put('sensor_data', $data, now()->addMinutes(10));

    return response()->json(['success' => true]);
});

Route::get('/sensor', function () {
    return Cache::get('sensor_data', [
        'status' => 'unknown',
        'temperature' => null,
        'humidity' => null,
    ]);
});
