<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyVehiclesController extends Controller
{
    /**
     * Show the my vehicles page.
     */
    public function index()
    {
        return view('reporter.my-vehicles', [
            'pageTitle' => 'My Vehicles'
        ]);
    }
}
