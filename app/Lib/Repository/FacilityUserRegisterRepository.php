<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRegisterRepositoryInterface;
use App\Lib\Entity\FacilityUserRegister;
use DB;

/**
 * 事業所のユーザーの名簿のリポジトリ。
 */
class FacilityUserRegisterRepository implements FacilityUserRegisterRepositoryInterface
{
    /**
     * 事業所のユーザーの名簿を返す。
     * @param int $accountId アカウントのID
     */
    public function find(
        int $accountId
    ): FacilityUserRegister {
        // 法人を取得する。
        $corporation = DB::table('corporation_account')
            ->where('account_id', $accountId)
            ->select('corporation_id')
            ->first();

        // 法人が持つ施設を全て取得する。
        $institutions = DB::table('i_institutions')
            ->where('corporation_id', $corporation->corporation_id)
            ->select('id')
            ->get();

        // 取得した施設が持つ事業所を全て取得する。
        $facilities = DB::table('i_facilities')
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->select('facility_id')
            ->get();

        // 取得した事業所が持つ施設利用者を全て取得する。
        $records = DB::table('i_user_facility_informations')
            ->whereIn('facility_id', $facilities->pluck('facility_id'))
            ->select('facility_user_id')
            ->get();
        
        // 施設利用者IDをユニークで取得する。
        $facilityUserIds = $records->pluck('facility_user_id')->unique()->values()->toArray();

        $register = new FacilityUserRegister($facilityUserIds);

        return $register;
    }
}
