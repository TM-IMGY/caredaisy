<?php

namespace App\Http\Controllers\Api\Hospitac;

use Exception;
use Laravel\Passport\Exceptions\OAuthServerException;
use Log;

class AuthController extends \Laravel\Passport\Http\Controllers\AccessTokenController
{
    public function issue(
        \App\Http\Requests\Api\Hospitac\AuthRequest $request,
        \Psr\Http\Message\ServerRequestInterface $interface)
    {
        try {
            $response = parent::issueToken($interface);
            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return $response;
        } catch (OAuthServerException $e) {
            report($e);
            return response()->json([
                'result_info'  => [
                    'result' => 'NG',
                    'result_code' => 'E00001',
                    'error'  => '認証に失敗しました。',
                ],
            ], 401);
        } catch (Exception $e) {
            report($e);
            return response()->json([
                'result_info'  => [
                    'result' => 'NG',
                    'result_code' => 'E00008',
                    'error'  => 'サーバ内で予期せぬエラーが発生しました。',
                ],
            ], 500);
        }
    }
}
