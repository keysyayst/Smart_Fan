<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\ManualControl;

class DashboardController extends Controller
{
    public function index()
    {
        $data = SensorData::latest()->first();
        return view('dashboard', compact('data'));
    }

    public function manual(Request $request)
    {
        // Simpan kontrol manual: mode MANUAL + perintah FAN
        ManualControl::create([
            'mode' => 'MANUAL',
            'fan' => $request->fan
        ]);

        return redirect()->back();
    }

    public function auto()
    {
        // Reset ke mode AUTO - sensor data akan mengontrol fan
        ManualControl::create([
            'mode' => 'AUTO',
            'fan' => null
        ]);

        return redirect()->back();
    }
}
