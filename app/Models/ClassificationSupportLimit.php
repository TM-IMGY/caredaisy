<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 区分支給限度基準額マスタのテーブル操作に責任をもつクラス。
 */
class ClassificationSupportLimit extends Model
{
    protected $table = 'classification_support_limit';
    protected $connection = 'mysql';
    protected $primaryKey = 'classification_support_limit_id';

    protected $guarded = [
      'classification_support_limit_id'
    ];

    /**
     * 介護度IDとサービス種類コードIDから返す。
     * 区分支給限度基準額は介護度マスタIDとサービス種別マスタIDで検索をした時は一意のレコードが取得できるが、
     * 今後の改定によっては時期によって内容が変わってしまうため、
     * 対象年月での絞り込みをしている。
     * @param int $careLevelId 介護度ID
     * @param int $serviceTypeCodeId サービス種類コードID
     * @param int $year
     * @param int $month
     * @throws
     * @return array
     */
    public static function getByCareLevelAndServiceType(int $careLevelId, int $serviceTypeCodeId, $year, $month): array
    {
        $endOfMonth = (new CarbonImmutable("${year}-${month}"))->endOfMonth()->format('Y-m-d H:i:s');

        $data = self::where('care_level_id', $careLevelId)
            ->where('service_type_code_id', $serviceTypeCodeId)
            ->whereDate('start_date', '<=', $endOfMonth)
            ->whereDate('end_date', '>=', $endOfMonth)
            ->first();

        // レコードが1件もない場合。
        if($data === null){
            return [];
        }

        return $data->toArray();
    }
}
