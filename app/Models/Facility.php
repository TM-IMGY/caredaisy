<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{

    /* 事業所番号 */
    public const FACILITY_NUMBER_GROUP_HOME_YOKI = '2070500448'; // グループホーム陽気

    protected $table = 'i_facilities';
    protected $connection = 'mysql';
    protected $primaryKey = 'facility_id';
    protected $guarded = [
        'facility_id'
    ];

    /**
     * $facilityIdの事業所の伝送機能の有効フラグを取得する
     * allow_transmission は mysql のtinyint(1) （1: true, 0: false）
     *
     * @param  string $facilityId
     * @return integer
     */
    public static function getTransmissionMode($facilityId)
    {
        return self::where('facility_id', $facilityId)->select('allow_transmission')->first()->allow_transmission;
    }
}
