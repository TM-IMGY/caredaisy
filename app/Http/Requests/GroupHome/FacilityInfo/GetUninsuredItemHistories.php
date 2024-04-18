<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetUninsuredItemHistories extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('id') && $this->id != null) {
            // リクエストパラメータにservice_idが含まれているかの判定
            if ($this::has('service_id') && $this->service_id != null) {
                return $this->authorizeServiceId($this->service_id) &&
                    $this->authorizeUninsuredItemId($this->id);
            } else {
                return $this->authorizeUninsuredItemId($this->id);
            }
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
