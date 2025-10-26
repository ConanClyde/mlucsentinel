<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Show the staff page.
     */
    public function index()
    {
        return view('admin.users.staff', [
            'pageTitle' => 'Staff Management'
        ]);
    }
}
