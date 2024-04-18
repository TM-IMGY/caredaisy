<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utility\S3;

/**
 * @author ttakenaka
 */
class TopController extends Controller
{
    public function index(Request $request)
    {
        $messageList = file(base_path('database/top_message/top_comment.txt'));
        return view('top', ['messageList' => $messageList]);
    }

    public function downloadOperationManual(Request $request)
    {
        $manual_name = 'operation_manual.pdf';
        $txt = file(base_path('maintenance/operation_manual_name.txt'));
        if (count($txt) > 0);
        {
            if (mb_strlen($txt[0]) > 1){
                $manual_name = $txt[0] . '.pdf';
            }
        }
        return response()->streamDownload(function () {
            print S3::getOperationManualPdf();
        }, $manual_name);
    }

    public function downloadTransmissionManual(Request $request)
    {
        $manual_name = 'transmission_manual.pdf';
        $txt = file(base_path('maintenance/transmission_manual_name.txt'));
        if (count($txt) > 0);
        {
            if (mb_strlen($txt[0]) > 1){
                $manual_name = $txt[0] . '.pdf';
            }
        }
        return response()->streamDownload(function () {
            print S3::getTransmissionManualPdf();
        }, $manual_name);
    }
}
