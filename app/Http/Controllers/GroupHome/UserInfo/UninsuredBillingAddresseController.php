<?php

namespace App\Http\Controllers\GroupHome\UserInfo;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\UserInfo\UninsuredBillingAddresseRequest;
use App\Http\Requests\GroupHome\UserInfo\GetUninsuredBillingAddresseRequest;
use App\Http\Requests\GroupHome\UserInfo\GetFacilityUserRequest;
use App\Service\GroupHome\UninsuredBillingAddresseService;
use App\Service\GroupHome\FacilityUserService;

class UninsuredBillingAddresseController extends Controller
{
    public function save(UninsuredBillingAddresseRequest $request)
    {
        $params = [
            'facility_id' => $request->facility_id,
            'facility_user_id' => $request->facility_user_id,
            'payment_method' => $request->payment_method,
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'fax_number' => $request->fax_number,
            'postal_code' => $request->postal_code,
            'location1' => $request->location1,
            'location2' => $request->location2,
            'bank_number' => $request->bank_number,
            'bank' => $request->bank,
            'branch_number' => $request->branch_number,
            'branch' => $request->branch,
            'bank_account' => $request->bank_account,
            'type_of_account' => $request->type_of_account,
            'depositor' => $request->depositor,
            'remarks_for_receipt' => $request->remarks_for_receipt,
            'remarks_for_bill' => $request->remarks_for_bill,
        ];
        
        $uninsuredBillingAddresseService = new UninsuredBillingAddresseService();
        $res = $uninsuredBillingAddresseService->save($params);

        if ($res) {
            return [];
        }
        abort(500);
    }
    public function get_facility_user(GetFacilityUserRequest $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_id,
        ];

        $facilityUserService = new FacilityUserService();
        return $facilityUserService->getFacilityUser($params);
    }
    
    public function get_billing_address(GetUninsuredBillingAddresseRequest $request)
    {
        $params = [
            'facility_id' => $request->facility_id,
            'facility_user_id' => $request->facility_user_id,
        ];
        
        $uninsuredBillingAddresseService = new UninsuredBillingAddresseService();
        return $uninsuredBillingAddresseService->getData($params);
    }
}
