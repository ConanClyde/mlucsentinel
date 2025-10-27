<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class MyVehiclesController extends Controller
{
    /**
     * Show the my vehicles page.
     */
    public function index()
    {
        $vehicles = Vehicle::where('user_id', Auth::id())
            ->with(['type'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'active' => $vehicles->where('is_active', true)->count(),
            'inactive' => $vehicles->where('is_active', false)->count(),
            'total' => $vehicles->count(),
        ];

        return view('reporter.my-vehicles', [
            'pageTitle' => 'My Vehicles',
            'vehicles' => $vehicles,
            'stats' => $stats,
        ]);
    }
}
