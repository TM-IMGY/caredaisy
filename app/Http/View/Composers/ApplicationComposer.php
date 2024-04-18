<?php

namespace App\Http\View\Composers;

use App\Service\GroupHome\UserService;
use Illuminate\View\View;

/**
 * ログイン後共通パーツのビューコンポーザー
 */
class ApplicationComposer
{
    protected $users;

    public function __construct(UserService $users)
    {
        $this->users = $users;
    }

    /**
     * データをビューと結合
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // ヘッダーの「伝送情報」リンク表示/非表示の判定
        $view->with('hasTransmission', $this->users->hasTransmission());
    }
}
