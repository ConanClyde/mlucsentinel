<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Show the staff registration page.
     */
    public function index()
    {
        return view('admin.registration.staff', [
            'pageTitle' => 'Staff Registration'
        ]);
    }
}
