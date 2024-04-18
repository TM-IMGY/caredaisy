<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);

        if ($this->shouldReport($exception)) {
            // デイリーのカスタムエラーログ
            Log::channel('daily_error')->error($exception);

            // zabbix監視対象ログ
            Log::channel('app')->error($exception->getTraceAsString());
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if ($request->is('api/*')) {
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->authError();
            }
        }

        if ($exception instanceof TokenMismatchException) {
            if (!$request->ajax()) {
                return redirect(route('login'));
            }
        }

        // TODO: リリース1.9暫定対応
        // zabbixエラーを回避したいという要件についての対応で、リリース目前のために、
        // 暫定的にmethod_existsを追記したが例外処理をルール化した上で修正すること。
        if ($request->ajax() && $this->isHttpException($exception) && method_exists($exception, 'getStatusCode')) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }
}
