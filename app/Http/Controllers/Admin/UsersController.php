<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Show the users page.
     */
    public function index()
    {
        return view('admin.users', [
            'pageTitle' => 'Users Management',
        ]);
    }
}
