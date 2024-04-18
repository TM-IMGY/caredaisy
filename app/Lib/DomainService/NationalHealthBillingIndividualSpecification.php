<?php

namespace App\Lib\DomainService;

use App\Lib\DomainService\BillingIndividualIncompetentResidentSpecification;
use App\Lib\DomainService\BillingIndividualServiceSpecification;
use App\Lib\DomainService\BillingIndividualSpecialMedicalSpecification;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserBenefitRecord;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\SpecialMedicalCode;
use App\Lib\Entity\ServiceResult;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

/**
 * 国保連請求の個別計算の仕様のクラス。
 * サービス、特別診療費、特定入所者介護サービスの仕様をラップしている。
 */
class NationalHealthBillingIndividualSpecification
{
    /**
     * 計算した値を返す。
     */
    public static function calculate(
        ?int $burdenLimit,
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserBenefitRecord $facilityUserBenefitRecord,
        ?FacilityUserPublicExpenseRecord $facilityUserPublicExpenseRecord,
        FacilityUserServiceRecord $facilityUserServiceRecord,
        ResultFlag $resultFlag,
        ServiceItemCode $serviceItemCode,
        ?SpecialMedicalCode $specialMedicalCode,
        int $year,
        int $month
    ): ServiceResult {
        if ($specialMedicalCode !== null) {
            return BillingIndividualSpecialMedicalSpecification::calculate(
                $facility,
                $facilityUser,
                $facilityUserBenefitRecord,
                $facilityUserPublicExpenseRecord,
                $facilityUserServiceRecord,
                $resultFlag,
                $serviceItemCode,
                $specialMedicalCode,
                $year,
                $month
            );
        } elseif ($serviceItemCode->isIncompetentResident()) {
            return BillingIndividualIncompetentResidentSpecification::calculate(
                $burdenLimit,
                $facility,
                $facilityUser,
                $facilityUserBenefitRecord,
                $facilityUserPublicExpenseRecord,
                $resultFlag,
                $serviceItemCode,
                $specialMedicalCode,
                $year,
                $month
            );
        } else {
            return BillingIndividualServiceSpecification::calculate(
                $facility,
                $facilityUser,
                $facilityUserBenefitRecord,
                $facilityUserPublicExpenseRecord,
                $facilityUserServiceRecord,
                $resultFlag,
                $serviceItemCode,
                $year,
                $month
            );
        }
    }
}
