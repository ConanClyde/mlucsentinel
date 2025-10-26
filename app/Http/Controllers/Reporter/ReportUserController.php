<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportUserController extends Controller
{
    /**
     * Show the report user page.
     */
    public function index()
    {
        return view('reporter.report-user', [
            'pageTitle' => 'Report User'
        ]);
    }
}
