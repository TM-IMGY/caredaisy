<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;

class PopupUpdataIndependenceRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (($this::has('facility_user_id') && $this->facility_user_id != null)
                && ($this::has('user_independence_informations_id') && $this->user_independence_informations_id != null)) {
            return $this->authorizeFacilityUserId($this->facility_user_id) &&
                $this->authorizeUserIndependenceInformationId($this->user_independence_informations_id);
        }

        return false;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('この操作は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}