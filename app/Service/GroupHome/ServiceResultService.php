<?php

namespace App\Service\GroupHome;

use App\Lib\ServiceScheduleFlagCalculation;
use App\Models\FacilityUser;
use App\Models\ServiceCode;
use App\Models\Service;
use App\Models\ServiceResult;
use App\Models\SpecialMedicalCode;
use App\Models\UserFacilityServiceInformation;
use DB;
use Exception;

class ServiceResultService
{
    /**
     * 給付費請求を返す
     * @param array $param
     * @param array $param['facility_user_id']
     * @param string $param['year']
     * @param string $param['month']
     */
    public function getBenefitBilling($param): array
    {
        return ServiceResult::
            date($param['year'], $param['month'])
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->whereIn('facility_user_id', $param['facility_user_id'])
            ->get()
            ->toArray();
    }

    /**
     * 施設利用者の対象年月のサービス実績を返す。
     * TODO: 初期の外部キーの貼られていない実装から放置されているため各テーブルから情報を取得後ロジックで結合する動きになっている。
     * @param int $facilityUserId 施設利用者ID。
     * @param int $year 対象年。
     * @param int $month 対象月。
     * @return array
     */
    public function getFacilityUserTargetYm(int $facilityUserId, int $year, int $month): array
    {
        // 施設利用者の対象年月のサービス実績を取得する。
        $serviceResults = ServiceResult::getFacilityUserTargetYm($facilityUserId, $year, $month);

        // サービス実績がない場合はそのまま返す
        if (count($serviceResults) == 0) {
            return $serviceResults;
        }

        // サービス実績に紐づくサービスコードを取得する。
        $serviceItemCodeIds = array_column($serviceResults, 'service_item_code_id');
        $serviceCodes = ServiceCode::getValidDuringTheTargetYm($serviceItemCodeIds, $year, $month);
        $serviceCodes = array_column($serviceCodes, 'service_item_name', 'service_item_code_id');

        // サービス実績に紐づく特別診療コードを取得する。
        $specialMedicalCodeIds = array_column($serviceResults, 'special_medical_code_id');
        $specialMedicalCodes = SpecialMedicalCode::getValidDuringTheTargetYm($specialMedicalCodeIds, $year, $month);
        $specialMedicalCodes = array_column($specialMedicalCodes, null, 'id');

        // 施設利用者の入居情報を取得する。
        $startDate = FacilityUser::where('facility_user_id', $facilityUserId)
            ->select('start_date')
            ->first()
            ->start_date;

        // レスポンスを作成する。
        // TODO: クラス化する。
        // TODO: 厳格に実装するなら、このメソッドでレスポンスの作成まで責任を持つべきではない。
        $response = [];
        for ($i = 0, $cnt = count($serviceResults); $i < $cnt; $i++) {
            $serviceItemCodeId = $serviceResults[$i]['service_item_code_id'];

            $response[$i] = array_merge($serviceResults[$i], [
                'service_item_name' => $serviceCodes[$serviceItemCodeId],
                'date_daily_rate_schedule' => ServiceScheduleFlagCalculation::getTargetYm($serviceItemCodeId, $startDate, $year, $month)
            ]);

            // 特別診療の場合。
            if ($serviceItemCodeId === ServiceCode::SPECIAL_MEDICAL_CODE_ID) {
                $specialMedicalCodeId = $serviceResults[$i]['special_medical_code_id'];
                $specialMedicalCode = $specialMedicalCodes[$specialMedicalCodeId];

                $response[$i] = array_merge($response[$i], [
                    'special_medical_name' => $specialMedicalCode['special_medical_name'],
                    'unit' => $specialMedicalCode['unit']
                ]);
            // それ以外の場合。
            } else {
                $response[$i] = array_merge($response[$i], ['special_medical_name' => null, 'unit' => null]);
            }
        }

        return $response;
    }

    /**
     * 給付費明細を返す
     * @param array $param key: facility_id,month,year
     */
    public function getInsuranceBilling($param): array
    {
        $year = $param['year'];
        $month = $param['month'];
        $facilityID = $param['facility_id'];

        $serviceResultList = ServiceResult::
            date($year, $month)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            // ->where('facility_id',$facilityID) // calc_kind 5 は 0固定
            ->select(
                'insurance_benefit',
                'part_payment',
                // 'public_spending_amount',
                'service_unit_amount',
                'total_cost'
            )
            ->get()
            ->toArray();

        // 合計値を算出
        $insuranceBenefit = array_sum(array_column($serviceResultList, 'insurance_benefit'));
        $partPayment = array_sum(array_column($serviceResultList, 'part_payment'));
        // $ = array_sum(array_column($serviceResultList,'public_spending_amount'));
        $serviceUnitAmount = array_sum(array_column($serviceResultList, 'service_unit_amount'));
        $totalCost = array_sum(array_column($serviceResultList, 'total_cost'));

        // 件数を算出
        $cnt = count($serviceResultList);

        return [
            'cnt' => $cnt,
            'part_payment' => $partPayment,
            // 'public_spending_amount' => '',
            'service_unit_amount' => $serviceUnitAmount,
            'total_cost' => $totalCost,
            'insurance_benefit' => $insuranceBenefit,
        ];
    }
}
