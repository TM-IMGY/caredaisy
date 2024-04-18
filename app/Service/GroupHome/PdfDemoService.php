<?php

namespace App\Service\GroupHome;

use App\Models\ClassificationSupportLimit;
use App\Models\CareReward;
use App\Models\CareRewardHistory;
use App\Models\ServiceType;
use App\Models\Service;
use App\Models\UserFacilityServiceInformation;

class PdfDemoService
{
    /**
     * 種別33で表示する情報を計算
     */
    public function getType33Units($facilityUserId, $param, $careLevel, $year, $month, $serviceTypeCode = null) : array
    {
        if (is_array($careLevel)) {
            foreach ($careLevel as $value) {
                if ($facilityUserId == $value['facility_user_id']) {
                    $careLevel = $value['care_level_id'];
                    break;
                }
            }
        }

        $serviceId = UserFacilityServiceInformation::yearMonth($year, $month)
            ->where('facility_user_id', $facilityUserId)
            ->where('usage_situation', UserFacilityServiceInformation::USAGE_SITUATION_IN_USE)
            ->select('service_id')
            ->first()
            ->attributesToArray();

        $careRewardId = CareReward::where('service_id', $serviceId['service_id'])
            ->select('id')
            ->first()
            ->attributesToArray();

        $serviceForm = CareRewardHistory::where('care_reward_id', $careRewardId['id'])
            ->select('service_form')
            ->first()
            ->attributesToArray();

        // サービス形態が「外部サービス利用型」
        if ($serviceForm['service_form'] == CareRewardHistory::SERVICE_FORM_EXTERNAL_SERVICE_USE_TYPE) {
\Log::debug('$serviceForm(service_form) -> '.print_r($serviceForm['service_form'], true));
            // 外部利用型上限管理対象単位数
            $type33Items['classification_support_benefit_limit_unit'] = $param['classification_support_limit_in_range'] + $param['classification_support_limit_over'];

            // 外部利用型外給付単位数
            $type33Items['classification_support_benefit_unit'] = $param['service_unit_amount'] - ($param['classification_support_limit_in_range'] + $param['classification_support_limit_over']);

            // 外部利用型給付上限単位数
            if (is_null($serviceTypeCode)) {
                $serviceTypeCodeId = Service::where('id', $serviceId['service_id'])
                    ->select('service_type_code_id')
                    ->first();
            } else {
                $serviceTypeCodeId = ServiceType::GetServiceTypeCodeId($serviceTypeCode)->first();
            }

            $classificationSupportLimitUnit = ClassificationSupportLimit::where('service_type_code_id', $serviceTypeCodeId['service_type_code_id'])
                ->where('care_level_id', $careLevel)
                ->select('classification_support_limit_units')
                ->first()
                ->attributesToArray();

            return array_merge($type33Items, $classificationSupportLimitUnit);
        }

        return [
            'classification_support_benefit_limit_unit' => null,
            'classification_support_benefit_unit' => null,
            'classification_support_limit_units' => null,
        ];
    }
}
