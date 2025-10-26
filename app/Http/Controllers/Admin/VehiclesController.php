<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehiclesController extends Controller
{
    /**
     * Show the vehicles page.
     */
    public function index()
    {
        return view('admin.vehicles', [
            'pageTitle' => 'Vehicles Management'
        ]);
    }
}
