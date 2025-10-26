<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    /**
     * Show the security page.
     */
    public function index()
    {
        return view('admin.users.security', [
            'pageTitle' => 'Security Management'
        ]);
    }
}
