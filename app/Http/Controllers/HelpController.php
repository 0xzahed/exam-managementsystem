<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Show the help center index.
     */
    public function index()
    {
        return view('help.index');
    }
}
