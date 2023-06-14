<?php

namespace App\Utils\Library\Templates;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface Gateway
{
    public function purchase(Request $request): JsonResponse;

    public function notify(Request $request);
}
