<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StakeholdersController extends Controller
{
    /**
     * Show the stakeholders page.
     */
    public function index()
    {
        return view('admin.users.stakeholders', [
            'pageTitle' => 'Stakeholders Management'
        ]);
    }
}
