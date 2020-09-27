<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TopController extends Controller
{

    public function __construct()
    {}

    public function index(Request $request)
    {
        return view('index');
    }

}