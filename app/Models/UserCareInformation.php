<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CareLevel;
use Exception;

/**
 * 施設利用者と介護度情報のリレーション情報の操作に責任を持つクラス。
 * ユーザーとはログインユーザーではなく施設利用者なので注意する。
 */
class UserCareInformation extends Model
{

    /* 認定状況 */
    public const CERTIFICATION_STATUS_APPLYING  = 1; // 申請中
    public const CERTIFICATION_STATUS_CERTIFIED = 2; // 認定済

    protected $table = 'i_user_care_informations';
    protected $primaryKey = 'user_care_info_id';
    protected $connection = 'mysql';


    /**
     * 施設利用者の対象年月の期間ごとの介護度情報を取得して返す
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function listFacilityUserTargetMonth(int $facilityUserId, int $year, int $month) : array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $data = self::whereDate('care_period_start', '<=', $targetMonthEndDate)
            ->whereDate('care_period_end', '>=', $targetMonthStartDate)
            ->where('facility_user_id', $facilityUserId)
            ->with('m_care_levels:care_level_id,care_level')
            ->orderBy('care_period_start', 'asc')
            ->get()
            ->toArray();

        return $data;
    }

    /**
     * 複数の施設利用者の対象月の最新のデータを返す
     * @param int[] $facilityUserIds
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function listFacilityUserTargetMonthLatest(array $facilityUserIds, int $year, int $month) : array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $data = self::
            whereDate('care_period_start', '<=', $targetMonthEndDate)
            ->whereDate('care_period_end', '>=', $targetMonthStartDate)
            ->whereIn('facility_user_id', $facilityUserIds)
            ->with('m_care_levels:care_level_id,care_level,care_level_name')
            ->orderBy('care_period_start', 'asc')
            ->get()
            ->toArray();

        if (count($data) === 0) {
            return [];
        }

        // 重複行をcare_period_startが最新のレコードで上書きする。
        $dataUniq = [];
        $notApplicables = [];
        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
            $facilityUserId = $data[$i]['facility_user_id'];
            // care_level_idに非該当がある場合の対応
            // TODO: 非該当は最新の介護情報の定義に含まれないのでクエリで除外できる。
            if ($data[$i]['care_level_id'] === CareLevel::NOT_APPLICABLE) {
                $notApplicables[$facilityUserId] = $data[$i];
            } else {
                $dataUniq[$facilityUserId] = $data[$i];
            }
        }
        // 非該当を除く最新レコードのセット
        foreach ($notApplicables as $keyFacilityUserId => $value) {
            if (!array_key_exists($keyFacilityUserId, $dataUniq)) {
                $dataUniq[$keyFacilityUserId] = $value;
            }
        }

        // インデックスをリセット
        $dataUniq = array_values($dataUniq);

        return $dataUniq;
    }

    public function m_care_levels()
    {
        return $this->belongsTo('App\Models\CareLevel', 'care_level_id', 'care_level_id');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        $startDate = "${year}-${month}-1";
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('care_period_start', '<=', $lastDate)
            ->whereDate('care_period_end', '>=', $startDate);
    }

    /**
     * 利用者の最新の認定情報を取得する
     */
    public static function getLatestApproval($facilityUserId)
    {
        try {
            $model = self::where("facility_user_id", $facilityUserId)
                ->orderBy('care_period_start', 'DESC')
                ->first();
        } catch (Exception $e) {
            report($e);
            return false;
        }
        return $model;
    }
}
