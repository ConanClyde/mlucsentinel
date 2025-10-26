<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is handled at route level
    }

    /**
     * Show the reporter dashboard.
     */
    public function index()
    {
        return view('reporter.dashboard', [
            'pageTitle' => 'Reporter Dashboard'
        ]);
    }
}