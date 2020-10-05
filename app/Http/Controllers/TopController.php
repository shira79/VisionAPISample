<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Estate;

class TopController extends Controller
{

    public function __construct()
    {}

    public function index()
    {
        return view('index');
    }

    public function list(Request $request)
    {
        $conditions = $request->all();
        $estates = Estate::Search($conditions)->paginate();
        return view('list')->with(['estates' => $estates,'conditions' => $conditions]);
    }

}