<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StakeholderController extends Controller
{
    /**
     * Show the stakeholder registration page.
     */
    public function index()
    {
        return view('admin.registration.stakeholder', [
            'pageTitle' => 'Stakeholder Registration'
        ]);
    }
}
