<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WatchdogRequest;
use App\Service\Api\Invoice\WatchdogService;

class WatchdogController extends Controller
{
    public function index(WatchdogRequest $request)
    {
        $watchdogService = new WatchdogService();
        return $watchdogService->update($request->target_function, null);
    }
}
