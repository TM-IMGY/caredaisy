<?php

namespace App\Models;

use App\Lib\Entity\FacilityUserPublicExpense;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 施設利用者の公費テーブルの操作に責任を持つクラス。
 */
class UserPublicExpenseInformation extends Model
{
    protected $table = 'i_user_public_expense_informations';
    protected $connection = 'mysql';
    protected $primaryKey = "public_expense_information_id";

    protected $guarded = [
        'public_expense_information_id'
    ];

    /**
     * 引数で渡す年月を範囲に含むか
     * @return Builder
     */
    public function scopeYearMonth($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('effective_start_date', '<=', "${year}-${month}-1")
            ->whereDate('expiry_date', '>=', $lastDate);
    }

    /**
     * 対象年月の情報を最新で返す。
     * @param int $facilityUserId 施設利用者ID。
     * @param int $year 対象年。
     * @param int $month 対象月。
     * @return array
     */
    public static function getTargetYmLatest(int $facilityUserId, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $data = self::where('facility_user_id', $facilityUserId)
            ->whereDate('effective_start_date', '<=', $targetMonthEndDate)
            ->whereDate('expiry_date', '>=', $targetMonthStartDate)
            ->orderBy('effective_start_date', 'desc')
            ->first();

        // レコードが1件もない場合。
        if($data === null){
            return [];
        }

        return $data->toArray();
    }
}
