<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\Utility\SlackUtility;

class SlackNotificationController extends Controller
{
    public function notification(Request $request)
    {
        $message = $request->message;
        $channel = $request->channel ?? false;

        SlackUtility::notification($message, $channel);
    }
}
