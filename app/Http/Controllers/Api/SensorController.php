<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SensorData;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        SensorData::create([
            'temperature' => $request->temperature,
            'humidity'    => $request->humidity,
            'fan_status'  => $request->fan_status,
            'led_status'  => $request->led_status
        ]);

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
