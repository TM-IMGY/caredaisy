<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 保険者マスタのテーブル操作に責任をもつクラス。
 */
class InsurerMaster extends Model
{
    protected $table = 'insurer_master';
    protected $connection = 'mysql';
    protected $primaryKey = 'insurer_id';

    protected $guarded = [
        'insurer_id'
    ];

    /**
     * マスタからレコードを一つ取得して返す。
     * 保険者マスタは保険者番号で検索をした時は一意のレコードが取得できるが、
     * 今後の改定によっては時期によって内容が変わってしまうため、
     * 対象年月での絞り込みをした方がいい。
     * @param int $insurerNo 保険者番号
     * @param int $year 対象年
     * @param int $month 対象月
     * @return array
     */
    public static function getTargetYm(int $insurerNo, int $year, int $month): array
    {
        $endOfMonth = (new CarbonImmutable("${year}-${month}"))->endOfMonth()->format('Y-m-d H:i:s');

        $data = self::whereDate('insurer_start_date', '<=', $endOfMonth)
            ->whereDate('insurer_end_date', '>=', $endOfMonth)
            ->where('insurer_no', $insurerNo)
            ->first();

        // レコードが1件もない場合。
        if($data === null){
            return [];
        }

        return $data->toArray();
    }
}
