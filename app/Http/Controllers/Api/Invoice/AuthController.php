<?php

namespace App\Http\Controllers\Api\Invoice;

use Exception;
use Log;

class AuthController extends \Laravel\Passport\Http\Controllers\AccessTokenController
{
    public function issue(
        \App\Http\Requests\Api\Invoice\AuthRequest $request,
        \Psr\Http\Message\ServerRequestInterface $interface)
    {
        try {
            $response = parent::issueToken($interface);
            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return $response;
        } catch (Exception $e) {
            report($e);
            return response()->error('[E00008]サーバ内で予期せぬエラーが発生いたしました。');
        }
    }
}
