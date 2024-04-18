<?php

namespace App\Lib\Entity;

use App\Service\GroupHome\ActualDaysService;
use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの基本情報レコードのクラス。
 */
class BasicRecord
{
    private array $record;

    /**
     * コンストラクタ
     * @param string $blankValue ブランクの値
     * @param string $exchangeInformationNumber 交換情報識別番号
     * @param Facility $facility 事業所
     * @param FacilityUser $facilityUser 施設利用者
     * @param FacilityUserCare $facilityUserCare 施設利用者の介護情報
     * @param ?FacilityUserPublicExpense $facilityUserPublicExpense 施設利用者の公費
     * @param FacilityUserService $facilityUserService 施設利用者のサービス
     * @param NationalHealthBilling $nationalHealthBilling 施設利用者の国保連請求
     * @param int $serialNumber 連番
     * @param string $targetYm 対象年月
     */
    public function __construct(
        string $blankValue,
        string $exchangeInformationNumber,
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserCare $facilityUserCare,
        ?FacilityUserPublicExpense $facilityUserPublicExpense,
        FacilityUserService $facilityUserService,
        NationalHealthBilling $nationalHealthBilling,
        int $serialNumber,
        string $targetYm
    ) {
        $serviceResultTotal = $nationalHealthBilling->getServiceTotal();
        $serviceResultTotalIncompetent = $nationalHealthBilling->getIncompetentResidentTotal();
        $serviceResultTotalSpecial = $nationalHealthBilling->getSpecialMedicalTotal();

        // 入居日
        $startDate = (new CarbonImmutable($facilityUser->getStartDate()))->format('Ymd');

        // 退去日
        $endDate = '';
        $afterOutStatus = '';
        if ($facilityUser->hasEndDate()) {
            $endDate = (new CarbonImmutable($facilityUser->getEndDate()))->format('Ymd');
            $afterOutStatus = $facilityUser->getAfterOutStatus()->getAfterOutStatus();
        }

        // 入所（院）実日数。service_count_dateは0のため利用できない
        $actualDaysService = new ActualDaysService();
        $actualDays = $actualDaysService->get([
            'death_date' => $facilityUser->getDeathDate(),
            'end_date' => $endDate,
            'facility_user_id' => $facilityUser->getFacilityUserId(),
            'start_date' => $startDate,
            'target_ym' => $targetYm
        ]);

        // 公費1/負担者番号
        $bearerNumber = $blankValue;
        $recipientNumber = $blankValue;
        if ($facilityUserPublicExpense !== null) {
            $bearerNumber = $facilityUserPublicExpense->getBearerNumber();
            $recipientNumber = $facilityUserPublicExpense->getRecipientNumber();
        }

        // 保険/特定診療費請求額
        // 公費1/特定診療費請求額
        $InsuranceBenefitSpecialMedical = $blankValue;
        $publicSpendingAmountSpecialMedical = $blankValue;
        if ($serviceResultTotalSpecial !== null) {
            $InsuranceBenefitSpecialMedical = $serviceResultTotalSpecial->getInsuranceBenefit();
            $publicSpendingAmountSpecialMedical = $serviceResultTotalSpecial->getPublicSpendingAmount();
        }

        // 保険/特定入所者介護サービス費等請求額
        // 公費1/特定入所者介護サービス費等請求額
        $InsuranceBenefitIncompetent = $blankValue;
        $publicSpendingAmountIncompetent = $blankValue;
        if ($serviceResultTotalIncompetent !== null) {
            $InsuranceBenefitIncompetent = $serviceResultTotalIncompetent->getInsuranceBenefit();
            $publicSpendingAmountIncompetent = $serviceResultTotalIncompetent->getPublicSpendingAmount();
        }

        $this->record = [
            // 1 データレコード/レコード種別 マジックナンバー
            '2',
            // 2 データレコード/レコード番号(連番)
            $serialNumber,
            // 3 交換情報識別番号
            $exchangeInformationNumber,
            // 4 レコード種別コード マジックナンバー
            '01',
            // 5 サービス提供年月
            $targetYm,
            // 6 事業所番号
            $facility->getFacilityNumber(),
            // 7 証記載保険者番号
            sprintf('%08d', $facilityUser->getInsurerNo()),
            // 8 被保険者番号
            $facilityUser->getInsuredNo()->getValue(),
            // 9 公費1/負担者番号
            $bearerNumber,
            // 10 公費1/受給者番号
            $recipientNumber,
            // 11 公費2/負担者番号
            $blankValue,
            // 12 公費2/受給者番号
            $blankValue,
            // 13 公費3/負担者番号
            $blankValue,
            // 14 公費3/受給者番号
            $blankValue,
            // 15 被保険者情報/生年月日
            (new CarbonImmutable($facilityUser->getBirthday()))->format('Ymd'),
            // 16 被保険者情報/性別コード
            $facilityUser->getGender(),
            // 17 被保険者情報/要介護状態区分コード
            $facilityUserCare->getCareLevel()->getCareLevel(),
            // 18 被保険者情報/旧措置入所者特例コード
            $blankValue,
            // 19 被保険者情報/認定有効期間開始年月日
            (new CarbonImmutable($facilityUserCare->getCarePeriodStart()))->format('Ymd'),
            // 20 被保険者情報/認定有効期間終了年月日
            (new CarbonImmutable($facilityUserCare->getCarePeriodEnd()))->format('Ymd'),
            // 21 居宅サービス計画/居宅サービス計画作成区分コード
            $blankValue,
            // 22 居宅サービス計画/事業所番号
            $blankValue,
            // 23 開始年月日
            $blankValue,
            // 24 中止年月日
            $blankValue,
            // 25 中止理由
            $facilityUser->getBeforeInStatus(),
            // 26 入所（院）年月日
            $startDate,
            // 27 退所（院）年月日
            $endDate,
            // 28 入所院実日数
            $actualDays['actual_day_cnt'],

            // 29 外泊日数
            count($actualDays['stay_out_days']),
            // 30 退所（院）後の状態コード
            $afterOutStatus,
            // 31 保険給付率
            $serviceResultTotal->getBenefitRate() === null ? '0' : $serviceResultTotal->getBenefitRate(),
            // 32 公費１給付率
            $serviceResultTotal->getPublicBenefitRate(),
            // 33 公費2給付率
            $blankValue,
            // 34 公費3給付率
            $blankValue,

            // 35 保険/サービス単位数
            $serviceResultTotal->getServiceUnitAmount(),
            // 36 保険/請求額
            $serviceResultTotal->getInsuranceBenefit(),
            // 37 保険/利用者負担額
            $serviceResultTotal->getPartPayment(),
            // 38 保険/緊急時施設療養費請求額
            $blankValue,
            // 39 保険/特定診療費請求額
            $InsuranceBenefitSpecialMedical,
            // 40 保険/特定入所者介護サービス費等請求額
            $InsuranceBenefitIncompetent,

            // 41 公費1/サービス単位数
            $serviceResultTotal->getPublicExpenditureUnit(),
            // 42 公費1/請求額
            $serviceResultTotal->getPublicSpendingAmount(),
            // 43 公費1/本人負担額
            $serviceResultTotal->getPublicPayment(),
            // 44 公費1/緊急時施設療養費請求額
            $blankValue,
            // 45 公費1/特定診療費請求額
            $publicSpendingAmountSpecialMedical,
            // 46 公費1/特定入所者介護サービス費等請求額
            $publicSpendingAmountIncompetent,

            // 47 公費2/サービス単位数
            $blankValue,
            // 48 公費2/請求額
            $blankValue,
            // 49 公費2/本人負担額
            $blankValue,

            // 50 公費2/緊急時施設療養費請求額
            $blankValue,
            // 51 公費2/特定診療費請求額
            $blankValue,
            // 52 公費2/特定入所者介護サービス費等請求額
            $blankValue,

            // 53 公費3/サービス単位数
            $blankValue,
            // 54 公費3/請求額
            $blankValue,
            // 55 公費3/本人負担額
            $blankValue,
            // 56 公費3/緊急時施設療養費請求額
            $blankValue,
            // 57 公費3/特定診療費請求額
            $blankValue,
            // 58 公費3/特定入所者介護サービス費等請求額
            $blankValue
        ];
    }

    /**
     * レコードを返す。
     * @return array
     */
    public function getRecord(): array
    {
        return $this->record;
    }
}
