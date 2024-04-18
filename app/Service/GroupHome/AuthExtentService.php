<?php

namespace App\Service\GroupHome;

use App\Models\AuthExtent;

class AuthExtentService
{
    const ADMINISTRATOR = 1;
    const CLAIMANT = 2;
    const PLANNER = 3;
    const PLANNER_AND_CLAIMANT = 4;

    public function save($params)
    {
        if ($params['planner']) {
            $auth = self::PLANNER;
        }
        if ($params['claimant']) {
            $auth = self::CLAIMANT;
        }
        if ($params['planner'] and $params['claimant']) {
            $auth = self::PLANNER_AND_CLAIMANT;
        }
        if ($params['administrator']) {
            $auth = self::ADMINISTRATOR;
        }

        $auth_extent_params = [
            'staff_id' => $params['staff_id'],
            'auth_extent_id' => $params['auth_extent_id'],
            'corporation_id' => $params['corporation_id'],
            'institution_id' => $params['institution_id'],
            'facility_id' => $params['facility_id'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],

            'auth_id' => $auth,
        ];

        $auth_extent_row = AuthExtent::where([
            ['id',$params["auth_extent_id"]]
        ])->first();

        if (!$auth_extent_row) {
            $authExtent = AuthExtent::create($auth_extent_params);
        } else {
            $auth_extent_row->update($auth_extent_params);
            $authExtent = $auth_extent_row;
        }
        $auth_extent = AuthExtent::where([
            ['staff_id',$params["staff_id"]]
        ])->with(['corporation','institution','facility','auth'])->get();
        
        return ['auth_extent' => $auth_extent, 'auth_extent_id' => $authExtent->id ];
    }
    
    /**
     * 履歴を取得
     *
     * @param int $staff_id
     * @return authExtent object
     */
    public function getAuthExtentHistoryData($params)
    {
        $authExtent = AuthExtent::where([
            ['staff_id',$params['staff_id']]
        ])->with(['corporation','institution','facility','auth'])->get();
        return  $authExtent;
    }
    
    /**
     * 権限範囲 現在日付での有効期限の権限を返す
     */
    public function getAuthExtent($staff_id){
        
        $today = date("Y-m-d");
        return AuthExtent::where('staff_id', $staff_id)->where('start_date', '<=', $today)
            ->where(function($query) use($today){
                    $query->where('end_date', '>=', $today)
                        ->orWhereNull('end_date');
            })->get();
    }
}
