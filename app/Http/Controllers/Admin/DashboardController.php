<?php

namespace App\Http\Controllers\Admin;

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
     * Show the admin dashboard.
     */
    public function index()
    {
        return view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard'
        ]);
    }
}