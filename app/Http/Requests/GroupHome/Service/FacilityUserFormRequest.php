<?php

namespace App\Http\Requests\GroupHome\Service;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Models\FacilityUser;
use App\Models\UserFacilityInformation;
use App\Service\GroupHome\InsurerService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class FacilityUserFormRequest extends CareDaisyBaseFormRequest
{
    const VALID_BIRTH_DATE = '1900-01-01';
    const VALID_START_DATE = '2000-03-31';
    const VALID_END_DATE = '2100-01-01';

    /**
     * @return bool
     */
    public function authorize()
    {
        if ($this->has('facility_id') && $this->facility_id != null) {
            // 新規か更新かの権限チェック判定
            if ($this->has('facility_user_id')) {
                return $this->authorizeFacilityIdFUserId($this->facility_user_id, $this->facility_id);
            } else {
                return $this->authorizeFacilityId($this->facility_id);
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
            'errors' => array('選択している利用者情報の編集は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'facility_id' => 'bail | required | integer',
            'contractor_number' => ['bail', 'nullable', 'max:20', 'regex:/^[a-zA-Z0-9]+$/'],
            'last_name' => 'bail | required | max:30',
            'first_name' => 'bail | required | max:30',
            'last_name_kana' => ['bail', 'required', 'max:30', 'regex:/\A[ァ-ヴーｦ-ﾟ]+\z/u'],
            'first_name_kana' => ['bail', 'required', 'max:30', 'regex:/\A[ァ-ヴーｦ-ﾟ]+\z/u'],
            'gender' => 'bail | required',
            'birthday' => ['bail', 'required', 'date', 'after:' . self::VALID_BIRTH_DATE, 'before:tomorrow'],
            'insured_no' => ['bail', 'required', 'max:10', 'regex:/^[a-zA-Z0-9]+$/'],
            'insurer_no' => ['bail', 'required', 'digits:6', 'regex:/^[0-9]+$/'],
            'postal_code' => ['nullable', 'regex:/\A\d{3}[-]\d{4}\z/'],
            'location1' => 'nullable | max:20',
            'location2' => 'nullable | max:20',
            'phone_number' => 'nullable | max:20',
            'cell_phone_number' => 'nullable | max:20',
            'start_date' => ['bail', 'required', 'date', 'after:' . self::VALID_START_DATE, 'before:' . self::VALID_END_DATE],
            'before_in_status_id' => 'bail | required',
            'end_date' => ['bail', 'nullable', 'date', 'after:' . self::VALID_START_DATE, 'before:' . self::VALID_END_DATE, 'after_or_equal:start_date', 'required_with:after_out_status_id'],
            'after_out_status_id' => 'nullable | required_with:end_date',
            'diagnosis_date' => ['bail', 'nullable', 'date', 'after:' . self::VALID_START_DATE, 'before:tomorrow', 'required_with:diagnostician,consent_date'],
            'diagnostician' => 'nullable | required_with:diagnosis_date',
            'consent_date' => ['bail', 'nullable', 'date', 'after:' . self::VALID_START_DATE, 'before:tomorrow', 'required_with:consenter', 'after_or_equal:diagnosis_date'],
            'consenter' => 'nullable | required_with:consent_date',
            'consenter_phone_number' => 'nullable | max:20',
            'death_date' => ['bail', 'nullable', 'date', 'after:' . self::VALID_START_DATE, 'before:tomorrow', 'after_or_equal:consent_date'],
            'invalid_flag' => 'nullable | integer',
            'spacial_address_flag' => 'nullable | integer',
        ];

        if ($this->has('facility_user_id')) {
            $rules['facility_user_id'] = 'bail | required | integer';
        }

        return $rules;
    }

    /**
     * バリデータインスタンスの設定
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // ユーザ情報を取得
            $userFacilityInformations = UserFacilityInformation::where('facility_id', $this->facility_id);
            if ($this->filled('facility_user_id')) {
                $userFacilityInformations->where('facility_user_id', '<>', $this->facility_user_id);
            }
            $userFacilityInformations = $userFacilityInformations->get();

            // 2022年11月7日の緊急リリース対応の為に一時的にコメントアウト
            // if (!$validator->errors()->has('contractor_number') && $this->filled('contractor_number')) {
            //     // 事業所内で契約者番号が重複していないか
            //     $sameNumberUserInfo = $userFacilityInformations->where('contractor_number', $this->contractor_number);
            //     if ($sameNumberUserInfo->isNotEmpty()) {
            //         $validator->errors()->add('contractor_number', '契約者番号が重複しています。');
            //     }
            // }

            // 2022年11月7日の緊急リリース対応の為に一時的にコメントアウト
            // 事業所内で被保険者番号が重複していないか
            // if (!$validator->errors()->has('insured_no') && $this->filled('insured_no')) {
            //     $ids = $userFacilityInformations->pluck('facility_user_id');
            //     // 暗号化の関係で被保険者番号でwhere出来ないので事業所内の番号を全て取得する
            //     $insuredNoList = FacilityUser::whereIn('facility_user_id', $ids)
            //         ->pluck('insured_no')
            //         ->toArray();
            //     if (in_array($this->insured_no, $insuredNoList)) {
            //         $validator->errors()->add('insured_no', '被保険者番号が重複しています。');
            //     }
            // }

            // 保険者マスタテーブルに登録されている番号と一致しているか
            if (!$validator->errors()->has('insurer_no') && $this->filled('insurer_no')) {
                $insureService = new InsurerService();
                $name = $insureService->get($this->insurer_no, today()->year, today()->month);
                if (empty($name)) {
                    $validator->errors()->add('insurer_no', '保険者番号が不正です。入力内容を見直してください。');
                }
            }
            // 看取り日が入居日以降に設定されているか
            if (
                !$validator->errors()->has('start_date') &&
                !$validator->errors()->has('death_date') &&
                $this->filled('death_date')
            ) {
                $startDate = new Carbon($this->start_date);
                $deathDate = new Carbon($this->death_date);
                if($deathDate->lt($startDate)){
                    $validator->errors()->add('death_date', '看取り日は入居日以降の日付を入力してください。');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'required'                          => ':attribute は必須項目です。',
            'integer'                           => ':attribute の形式が不正です。',
            'regex'                             => ':attribute の桁数が不正です。',
            'after_or_equal'                    => '開始日と終了日の関係性に誤りがあるので確認してください',
            'before'                            => ':attribute は2099/12/31までに日付を入力してください。',
            'after'                             => ':attribute は2000/4/1以降の日付を入力してください。',
            'max'                               => ':attribute は :max文字（桁）以下で入力してください。',
            'date_format'                       => ':attribute を正しく入力してください。',
            'required_with'                     => ':attribute を入力してください。',
            'date'                              => ':attribute を正しく入力してください。',
            'contractor_number.max'             => '契約者番号は20文字以内で入力してください。',
            'contractor_number.regex'           => '契約者番号は英数字で入力してください。',
            'last_name.max'                     => '利用者姓は30文字以内で入力してください。',
            'first_name.max'                    => '利用者名は30文字以内で入力してください。',
            'last_name_kana.max'                => '利用者姓（カナ）は30文字以内で入力してください。',
            'last_name_kana.regex'              => '利用者姓（カナ）はカタカナで入力してください。',
            'first_name_kana.max'               => '利用者名（カナ）は30文字以内で入力してください。',
            'first_name_kana.regex'             => '利用者名（カナ）はカタカナで入力してください。',
            'birthday.after'                    => '生年月日は1900年1月1日以降の日付を入力してください。',
            'birthday.before'                   => '未来の日付は入力できません。',
            'insured_no.regex'                  => '被保険者番号は10文字以内の半角英数字で入力してください。',
            'insurer_no.max'                    => '被保険者番号は10文字以内の半角英数字で入力してください。',
            'insurer_no.regex'                  => '保険者番号は6文字の半角数字で入力してください。',
            'insurer_no.digits'                 => '保険者番号は6文字の半角数字で入力してください。',
            'postal_code.regex'                 => '郵便番号は8文字（ハイフンを含む半角数字）で入力してください。',
            'phone_number.regex'                => '電話番号をハイフンを含め正しく入力してください。',
            'cell_phone_number.regex'           => '携帯番号をハイフンを含め正しく入力してください。',
            'end_date.after_or_equal'           => '入居日と退居日の関係性に誤りがあるので確認してください',
            'after_out_status_id.required_with' => '退居後の状況を正しく入力してください。',
            'diagnosis_date.before'             => '未来の日付は入力できません。',
            'consent_date.before'               => '未来の日付は入力できません。',
            'consent_date.after_or_equal'       => '同意日は診断日より後の日付を入力してください。',
            'death_date.before'                 => '未来の日付は入力できません。',
            'death_date.after_or_equal'         => '看取り日は同意日より後の日付を入力してください。',

        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'          => '利用者選択',
            'facility_id'               => '事業所',
            'contractor_number'         => '契約者番号',
            'last_name'                 => '利用者姓',
            'first_name'                => '利用者名',
            'last_name_kana'            => '利用者姓(カナ)',
            'first_name_kana'           => '利用者名(カナ)',
            'gender'                    => '性別',
            'birthday'                  => '生年月日',
            'blood_type'                => '血液型(ABO)',
            'rh_type'                   => '血液型(RH)',
            'insured_no'                => '被保険者番号',
            'insurer_no'                => '保険者番号',
            'postal_code'               => '郵便番号',
            'location1'                 => '住所1',
            'location2'                 => '住所2',
            'phone_number'              => '電話番号',
            'cell_phone_number'         => '携帯番号',
            'start_date'                => '入居日',
            'end_date'                  => '退居日',
            'before_in_status_id'       => '入居前の状況',
            'after_out_status_id'       => '退居後の状況',
            'diagnosis_date'            => '診断日',
            'diagnostician'             => '診断者',
            'consent_date'              => '同意日',
            'consenter'                 => '同意者',
            'consenter_phone_number'    => '同意者連絡先',
            'death_date'                => '看取り日',
            'death_reason'              => '看取り理由',
            'invalid_flag'              => '無効フラグ',
            'spacial_address_flag'      => '住所地特例',
        ];
    }
}
