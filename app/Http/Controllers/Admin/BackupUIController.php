<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BackupUIController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:backup-view');
    }

    public function index()
    {
        return view('admin.backup-center');
    }
}

