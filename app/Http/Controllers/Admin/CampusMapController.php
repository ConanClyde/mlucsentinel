<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CampusMapController extends Controller
{
    /**
     * Display the campus map.
     */
    public function index(): View
    {
        return view('admin.campus-map', [
            'pageTitle' => 'Campus Map',
        ]);
    }
}
