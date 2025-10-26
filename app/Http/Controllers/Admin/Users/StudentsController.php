<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    /**
     * Show the students page.
     */
    public function index()
    {
        return view('admin.users.students', [
            'pageTitle' => 'Students Management'
        ]);
    }
}
