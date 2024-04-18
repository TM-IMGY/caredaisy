<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Models\CorporationAccount;
use App\Models\Institution;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InstitutionRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 他画面で"institution_id"を送る処理があった場合共通化
        $institutionId = (int)$this->institution_id;
        $corporationId = CorporationAccount::where('account_id', \Auth::id())->select('corporation_id')->first();
        $institutionIdList = Institution::where('corporation_id', $corporationId->corporation_id)->select('id')->get()->toArray();
        $userAffiliationInstitutionId = array_column($institutionIdList, 'id');
        if (in_array($institutionId, $userAffiliationInstitutionId, true) === true) {
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
