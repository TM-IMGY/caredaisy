<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * ビューコンポーザー用のサービスプロバイダー
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * 全アプリケーションサービスの初期起動
     *
     * @return void
     */
    public function boot()
    {
        // ログイン後共通パーツのビューコンポーザー
        View::composer('layouts/application', 'App\Http\View\Composers\ApplicationComposer');
    }
}
