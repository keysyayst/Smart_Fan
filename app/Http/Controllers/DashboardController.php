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
        ManualControl::create([
            'fan' => $request->fan,
            'led' => $request->led    
        ]);

        return redirect()->back();
    }
}
