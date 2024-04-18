<?php

namespace App\Service\GroupHome;

use App\Models\CorporationAccount;
use App\Models\Facility;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;

class FacilityService
{
    /**
     * アカウントに紐づく事業所情報を返す。
     * @return array
     */
    public function getRelatedData(): array
    {
        // アカウントIDに紐づく法人IDのを全て取得する。
        $corporationAccounts = CorporationAccount::where('account_id', Auth::id())
            ->select('corporation_id')
            ->get()
            ->toArray();
        $corporationIds = array_column($corporationAccounts, 'corporation_id');

        // 法人IDのリストに紐づく施設IDを全て取得する。
        $institutions = Institution::whereIn('corporation_id', $corporationIds)
            ->select('id')
            ->get()
            ->toArray();
        $institutionIds = array_column($institutions, 'id');

        // 施設IDのリストに紐づく事業所を全て取得する。
        $facilities = Facility::whereIn('institution_id', $institutionIds)
            ->select('facility_id', 'facility_name_kanji', 'facility_number')
            ->get()
            ->toArray();

        return $facilities;
    }

  /**
   * @param array param key:clm_list,facility_id_list
   * @return array
   */
    public function getData($param) : array
    {
        return Facility::whereIn('facility_id', $param['facility_id_list'])
            ->select($param['clm_list'])
            ->get()
            ->toArray();
    }
}
