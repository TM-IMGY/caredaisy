<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 介護度マスタ情報の操作に責任を持つクラス。
 * 国レベルの介護認定度改定が無い限りは、変更が起きえないテーブルとなる。
 * 非該当と事業対象者を別として、レコードは昇順で重篤度が表現されている(区分支給限度基準額もそのように推移している)。
 */
class CareLevel extends Model
{
    protected $table = 'm_care_levels';
    protected $connection = 'mysql';

    // 要支援の閾値となるID
    public const ID_AS_THRESHOLD_FOR_ASSISTANCE_REQUIRED = 4;
    // 要介護の閾値となるID
    public const ID_AS_THRESHOLD_FOR_CARE_REQUIRED = 6;
    // 非該当のID
    public const NOT_APPLICABLE = 1;

    public function i_user_care_informations()
    {
        return $this->hasMany('App\Models\UserCareInformation');
    }
}
