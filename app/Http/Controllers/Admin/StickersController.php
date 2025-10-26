<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StickersController extends Controller
{
    /**
     * Show the stickers page.
     */
    public function index()
    {
        return view('admin.stickers', [
            'pageTitle' => 'Stickers Management'
        ]);
    }
}
