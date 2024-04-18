<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MdcGroupNames extends Model
{
    protected $table = 'mdc_group_names';
    protected $connection = 'mysql';

    protected $guarded = [
        'id',
    ];

    /**
     * DPCコードから主傷病名を取得する
     */
    public static function getMdcGroupData($mdcCode, $groupCode)
    {
        return self::where('mdc_code', $mdcCode)
            ->where('group_code', $groupCode)
            ->get()
            ->toArray();
    }

}
