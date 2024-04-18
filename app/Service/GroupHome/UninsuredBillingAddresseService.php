<?php

namespace App\Service\GroupHome;

use App\Models\UninsuredBillingAddress;

class UninsuredBillingAddresseService
{

    public function save($params)
    {
        $params = UninsuredBillingAddress::encryptUninsuredBillingAddresse($params);

        $row = UninsuredBillingAddress::where([
                ['facility_id',$params["facility_id"]],
                ['facility_user_id',$params["facility_user_id"]]
            ])->first();

        if (!$row) {
            $res = UninsuredBillingAddress::insert($params);
        } else {
            $res = $row->where([
                ['facility_id',$params["facility_id"]],
                ['facility_user_id',$params["facility_user_id"]]
            ])->update($params);
        }
        return $res;
    }
    public function getData($params)
    {

        $facilityUsers = UninsuredBillingAddress::where([
                ['facility_id',$params['facility_id']],
                ['facility_user_id',$params['facility_user_id']],
            ])
            ->get();
        return $facilityUsers;
    }
}
