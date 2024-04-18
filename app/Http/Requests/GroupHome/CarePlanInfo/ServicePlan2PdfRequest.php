<?php

namespace App\Http\Requests\GroupHome\CarePlanInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 介護計画書 2：プレビュー用フォームリクエスト
 * outputServicePlan2Pdfメソッドのコメントより、バリデーション予定のため新規作成
 */
class ServicePlan2PdfRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('plan_id') && $this->plan_id != null) {
            return $this->authorizeServicePlanId($this->plan_id);
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
