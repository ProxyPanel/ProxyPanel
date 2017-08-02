<?php

namespace App\Http\Controllers;

use Request;
use Response;

/**
 * 前端控制器
 * Class Home
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        return Response::view('index');
    }

}