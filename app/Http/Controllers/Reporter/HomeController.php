<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the reporter home page.
     */
    public function index()
    {
        return view('reporter.home', [
            'pageTitle' => 'Reporter Home'
        ]);
    }
}
