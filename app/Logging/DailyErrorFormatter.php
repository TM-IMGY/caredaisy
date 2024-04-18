<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

/**
 * デイリーのカスタムエラーログフォーマッター
 */
class DailyErrorFormatter
{
    /**
     * 渡されたロガーインスタンスのカスタマイズ
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        $format = "[%datetime%][%level_name%][" . getmypid() . "]%message%\nExtra:\n%extra%\nContext:\n%context%\n";
        $formatter = new LineFormatter($format, 'Y-m-d H:i:s', true);

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($formatter);

            // プロセッサーで出力情報を追加
            $handler->pushProcessor(function ($record) {
                // ログイン済みの場合はユーザーIDをセット
                if (!is_null(request()->user())) {
                    $record['extra']['account_id'] = request()->user()->account_id;
                }

                // リクエストデータから必要なものをセット
                $requests['uri'] = request()->path();
                $requests['fullUrl'] = request()->fullUrl();
                $requests['method'] = request()->method();
                // パスワード等は出力しない
                $requests['parameters'] = request()->except(['password', 'password_confirmation']);

                $record['extra']['request'] = $requests;

                return $record;
            });
        }
    }
}
