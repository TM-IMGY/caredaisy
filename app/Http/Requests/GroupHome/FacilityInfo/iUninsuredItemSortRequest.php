<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class iUninsuredItemSortRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (($this::has('service_id') && $this->service_id != null)
                && ($this::has('uninsured_item_history_id_list') && $this->uninsured_item_history_id_list != null)) {
            foreach ($this->uninsured_item_history_id_list as $uninsured_item_history_id) {
                $res = $this->authorizeUninsuredItemHistoryId($uninsured_item_history_id);
                if (!$res) {
                    return false;
                }
            }
            return $this->authorizeServiceId($this->service_id);
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
        return [];
    }

    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }
}
