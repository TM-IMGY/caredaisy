<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Models\CorporationAccount;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CorporationRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 他画面で"corporation_id"を送る処理があった場合共通化
        $corporationId = (int)$this->corporation_id;
        $userAffiliationCorporationId = CorporationAccount::where('account_id', \Auth::id())->select('corporation_id')->first();
        if ($userAffiliationCorporationId->corporation_id === $corporationId) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択している情報の編集は許可されていません。'),
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
