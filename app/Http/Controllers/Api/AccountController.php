<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class AccountController
{
    public function index(Request $request)
    {
        return $request->user();
    }
}
