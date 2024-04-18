<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\CarbonImmutable;

/**
 * 施設利用者と自立度のリレーション情報の操作に責任を持つクラス。
 * Userとはログインユーザーではなく施設利用者なので注意する。
 */
class UserIndependenceInformation extends Model
{
    protected $table = 'i_user_independence_informations';
    protected $connection = 'mysql';
    protected $primaryKey = 'user_independence_informations_id';

    protected $guarded = [
        'user_independence_informations_id',
    ];
}
