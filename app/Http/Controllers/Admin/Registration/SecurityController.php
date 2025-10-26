<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    /**
     * Show the security registration page.
     */
    public function index()
    {
        return view('admin.registration.security', [
            'pageTitle' => 'Security Registration'
        ]);
    }
}
