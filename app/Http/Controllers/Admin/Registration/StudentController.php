<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Show the student registration page.
     */
    public function index()
    {
        return view('admin.registration.student', [
            'pageTitle' => 'Student Registration'
        ]);
    }
}
