<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Show the reports page.
     */
    public function index()
    {
        return view('admin.reports', [
            'pageTitle' => 'Reports Management'
        ]);
    }
}
