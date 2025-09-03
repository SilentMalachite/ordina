<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LogUIController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:log-view');
    }

    public function index()
    {
        return view('admin.log-management');
    }
}

