<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventPackageController extends Controller
{
    /**
     * Display a listing of event packages.
     */
    public function index()
    {
        return view('admin.event-packages.index');
    }
}
