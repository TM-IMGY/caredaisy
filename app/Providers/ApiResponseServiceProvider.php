<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Log;

class ApiResponseServiceProvider extends ServiceProvider
{
    public static function notice($class, $function, $line, $response)
    {
        $log = '[' . $class .':'. $function .':'. $line . '] api:';
        $log .= preg_replace(['/\s+/s','/^Array \(/','/\)[^\)]*?$/'], [' ','{','}'], print_r($response, true));
        Log::channel('api')->notice($log);
    }


    public static function generateDefaultResponse($message)
    {
        $response = [
            'result_info' => [
                'result'      => 'NG',
                'result_code' => 'E00008',
                'message'     => 'サーバ内で予期せぬエラーが発生しました。',
            ],
        ];

        if (preg_match('/^\[([^ ]+)\](.+)$/', $message, $matches)) {
            $response['result_info']['result_code'] = $matches[1];
            $response['result_info']['message'] = $matches[2];
        }

        return $response;
    }


    public function boot()
    {
        Response::macro('warning', function ($message) {
            $response = ApiResponseServiceProvider::generateDefaultResponse($message);
            $response['result_info']['result'] = 'OK';
            ApiResponseServiceProvider::notice(__CLASS__, __FUNCTION__, __LINE__, $response);
            return response()->json($response, 200);
        });

        Response::macro('error', function ($message) {
            $response = ApiResponseServiceProvider::generateDefaultResponse($message);
            ApiResponseServiceProvider::notice(__CLASS__, __FUNCTION__, __LINE__, $response);
            return response()->json($response, 500);
        });

        Response::macro('authError', function () {
            $response = ApiResponseServiceProvider::generateDefaultResponse('[E00009]認証エラーが発生しました。');
            ApiResponseServiceProvider::notice(__CLASS__, __FUNCTION__, __LINE__, $response);
            return response()->json($response, 401);
        });

        Response::macro('validationError', function ($error) {
            $response = ApiResponseServiceProvider::generateDefaultResponse($error);
            ApiResponseServiceProvider::notice(__CLASS__, __FUNCTION__, __LINE__, $response);
            return response()->json($response, 400);
        });
    }
}
