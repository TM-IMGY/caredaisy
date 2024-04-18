<?php

namespace App\Service\GroupHome;

use Illuminate\Support\Facades\DB;

/**
 * ユーザーに関するサービス
 */
class UserService
{
    /**
     * ログイン中のアカウントが伝送請求が有効の事業所を所有しているか確認する
     *
     * @return boolean
     *
     * @todo Userモデルにリレーションを定義し、それを用いたクエリ組み立てに変更
     */
    public static function hasTransmission(): bool
    {
        $result = DB::table('i_accounts AS a')
            ->join('corporation_account AS ca', 'a.account_id', '=', 'ca.account_id')
            ->join('i_institutions AS iis', 'ca.corporation_id', '=', 'iis.corporation_id')
            ->join('i_facilities AS ifa', 'iis.id', '=', 'ifa.institution_id')
            ->where('a.account_id', \Auth::id())
            ->where('ifa.allow_transmission', 1)
            ->select([
                DB::raw('COUNT(1) AS count_allowed'),
            ])
            ->first();

        if (!empty($result) && $result->count_allowed > 0) {
            return true;
        }

        return false;
    }
}
