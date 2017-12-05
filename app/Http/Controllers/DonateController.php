<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;

class DonateController extends BaseController
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    public function donate(Request $request)
    {
        return Response::view('admin/donate');
    }
}