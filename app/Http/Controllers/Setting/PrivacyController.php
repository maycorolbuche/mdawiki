<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;

class PrivacyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.privacy');
    }
}
