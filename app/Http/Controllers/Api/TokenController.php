<?php

namespace App\Http\Controllers\Api;

use Exception;
use Laravel\Passport\Exceptions\OAuthServerException;
use Log;

class TokenController extends \Laravel\Passport\Http\Controllers\AccessTokenController
{
    public function issue(
        \App\Http\Requests\Api\TokenRequest $request,
        \Psr\Http\Message\ServerRequestInterface $interface)
    {
        try {
            // 当メソッド内では $request は使用していないが DI しないとバリデーションが起動しないので注意
            $response = parent::issueToken($interface);
            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return $response;
        } catch (OAuthServerException $e) {
            report($e);
            return response()->validationError('[E00001]認証に失敗しました。');
        } catch (Exception $e) {
            report($e);
            return response()->error('[E00008]サーバ内で予期せぬエラーが発生しました。');
        }
    }
}
