<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyReportController extends Controller
{
    /**
     * Show the my reports page.
     */
    public function index()
    {
        return view('reporter.my-reports', [
            'pageTitle' => 'My Reports'
        ]);
    }
}
