<?php

namespace App\Http\Middleware;

use Closure;

/**
 * レスポンスヘッダーを変更しブラウザにキャッシュさせないようにするミドルウェア
 */
class NoCache
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // キャッシュ関連のヘッダーを指定できるレスポンスにのみ指定する
        // NOTE: マニュアルPDFダウンロード等のファイルダウンロードレスポンス等にはheaderメソッドが存在しない
        if (method_exists($response, 'header')) {
            $response->header('Cache-Control', 'no-store');
            $response->header('Pragma', 'no-cache');
        }

        return $response;
    }
}
