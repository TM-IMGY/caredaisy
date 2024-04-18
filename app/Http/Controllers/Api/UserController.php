<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Service\Api\UserService;
use Exception;
use Log;

class UserController
{
    public function __construct(UserService $user)
    {
        $this->user = $user;
    }


    public function index(UserRequest $request)
    {
        try {
            $employeeNumber = $request->user()->employee_number;
            $facilityNumber = $request->get('facility_number');
            $allGetFlg      = $request->get('all_get_flg');

            $facilityId = $this->user->getFacilityId($employeeNumber, $facilityNumber);
            if (empty($facilityId)) {
                // facility_numberからfacility_idが取得できなかった場合はE00005を返す
                return response()->validationError('[E00005]指定した事業所が見つかりません。');
            }

            $response = $this->user->generateList($facilityId, $facilityNumber, $employeeNumber, $allGetFlg);

            if (is_null($response)) {
                throw new Exception("data error " . __FILE__.':'.__LINE__);
            }

            // 正常系動作
            if ($response['facility_user_count'] == 0) {
                return response()->warning('[W00001]条件に一致する利用者情報が存在しません。');
            }

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return $response;
        } catch (Exception $e) {
            report($e);
            return response()->error('[E00008]サーバ内で予期せぬエラーが発生いたしました。');
        }
    }
}
