<?php

namespace App\Http\Requests\GroupHome\TransmitInfo;

use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;

class GetInvoiceRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_id') && $this->facility_id != null) {
            return $this->authorizeFacilityId($this->facility_id);
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