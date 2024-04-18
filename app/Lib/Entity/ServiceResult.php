<?php

namespace App\Lib\Entity;

use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;

/**
 * サービス実績。
 * 日本語名称は国保連請求csvの用語を優先する。
 */
class ServiceResult
{
    // 承認状態
    public const APPROVAL = 1;
    public const NOT_APPROVAL = 0;

    // 国保連請求の請求計算の分類。
    // 個別
    public const CALC_KIND_INDIVIDUAL = 1;
    // 事業所
    public const CALC_KIND_FACILITY = 2;
    // サービス種別小計
    public const CALC_KIND_SUBTOTAL = 3;
    // 特殊
    public const CALC_KIND_FACILITY_SPECIAL = 4;
    // 合計
    public const CALC_KIND_TOTAL = 5;

    // 国保連請求の実績の分類。
    // サービス
    public const RESULT_KIND_SERVICE = 1;
    // 特別診療
    public const RESULT_KIND_SPECIAL_MEDICAL = 2;
    // 特定入所者サービス
    public const RESULT_KIND_INCOMPETENT_RESIDENT = 3;

    // 単位数が確定されるもの。
    public const UNIT_PRICE_INCOMPETENT_RESIDENT = 100;
    public const UNIT_PRICE_SPECIAL_MEDICAL_CODE = 1000;
    
    private ?int $approval;

    /**
     * @var ?int 給付率
     */
    private ?int $benefitRate;

    /**
     * @var ?int 負担者限度額
     */
    private ?int $burdenLimit;

    /**
     * @var ?int 計算種別 CALC_KIND_INDIVIDUAL などに詳細あり。
     */
    private ?int $calcKind;

    private ?int $classificationSupportLimitInRange;

    private string $documentCreateDate;

    private int $facilityId;

    private ?string $facilityNameKanji;

    private string $facilityNumber;

    private int $facilityUserId;

    /**
     * @var ?int 保険給付額
     * 様式第九の二: 保険請求額
     */
    private ?int $insuranceBenefit;

    /**
     * @var ?int 利用者負担(保険対象)
     */
    private ?int $partPayment;

    /**
     * @var ?int 公費給付率
     */
    private ?int $publicBenefitRate;

    /**
     * @var ?int 公費単位数合計
     */
    private ?int $publicExpenditureUnit;

    /**
     * @var ?int 公費利用者負担額
     */
    private ?int $publicPayment;

    /**
     * @var ?int 公費請求額
     */
    private ?int $publicSpendingAmount;

    /**
     * @var ?int 公費１対象日数・回数
     * 類語: 公費分回数等
     */
    private ?int $publicSpendingCount;

    /**
     * @var ?int 公費1対象サービス単位数
     */
    private ?int $publicSpendingUnitNumber;

    /**
     * @var ?int 公費単位数単価
     */
    private ?int $publicUnitPrice;

    private ?int $rank;

    /**
     * @var ResultFlag 実績フラグ
     */
    private ResultFlag $resultFlag;

    /**
     * @var int 実績種別。RESULT_KINDS参照。
     */
    private int $resultKind;

    private int $serviceCount;

    private int $serviceEndTime;

    /**
     * @var ServiceItemCode サービス項目コード
     */
    private ServiceItemCode $serviceItemCode;

    private int $serviceItemCodeId;

    private ?int $serviceResultId;

    private int $serviceStartTime;

    /**
     * @var ?int サービス単位数
     * 様式第九の二: 給付単位数、単位数合計など。
     */
    private ?int $serviceUnitAmount;

    private string $serviceUseDate;

    /**
     * @var ?SpecialMedicalCode 特別診療費コード
     */
    private ?SpecialMedicalCode $specialMedicalCode;

    private string $targetDate;

    /**
     * @var ?int 費用総額(保険対象分)
     */
    private ?int $totalCost;

    /**
     * @var ?int 単位数
     */
    private ?int $unitNumber;

    /**
     * @var ?int 単位数単価
     */
    private ?int $unitPrice;

    public function __construct(
        ?int $approval,
        ?int $benefitRate,
        ?int $burdenLimit,
        ?int $calcKind,
        ?int $classificationSupportLimitInRange,
        string $documentCreateDate,
        int $facilityId,
        ?string $facilityNameKanji,
        string $facilityNumber,
        int $facilityUserId,
        ?int $insuranceBenefit,
        ?int $partPayment,
        ?int $publicBenefitRate,
        ?int $publicExpenditureUnit,
        ?int $publicPayment,
        ?int $publicSpendingAmount,
        ?int $publicSpendingCount,
        ?int $publicSpendingUnitNumber,
        ?int $publicUnitPrice,
        ?int $rank,
        ResultFlag $resultFlag,
        int $resultKind,
        int $serviceCount,
        int $serviceEndTime,
        ServiceItemCode $serviceItemCode,
        int $serviceItemCodeId,
        ?int $serviceResultId,
        int $serviceStartTime,
        ?int $serviceUnitAmount,
        string $serviceUseDate,
        ?SpecialMedicalCode $specialMedicalCode,
        string $targetDate,
        ?int $totalCost,
        ?int $unitNumber,
        ?int $unitPrice
    ) {
        $this->approval = $approval;
        $this->benefitRate = $benefitRate;
        $this->burdenLimit = $burdenLimit;
        $this->calcKind = $calcKind;
        $this->classificationSupportLimitInRange = $classificationSupportLimitInRange;
        $this->documentCreateDate = $documentCreateDate;
        $this->facilityId = $facilityId;
        $this->facilityNameKanji = $facilityNameKanji;
        $this->facilityNumber = $facilityNumber;
        $this->facilityUserId = $facilityUserId;
        $this->insuranceBenefit = $insuranceBenefit;
        $this->partPayment = $partPayment;
        $this->publicBenefitRate = $publicBenefitRate;
        $this->publicExpenditureUnit = $publicExpenditureUnit;
        $this->publicPayment = $publicPayment;
        $this->publicSpendingAmount = $publicSpendingAmount;
        $this->publicSpendingCount = $publicSpendingCount;
        $this->publicSpendingUnitNumber = $publicSpendingUnitNumber;
        $this->publicUnitPrice = $publicUnitPrice;
        $this->rank = $rank;
        $this->resultFlag = $resultFlag;
        $this->resultKind = $resultKind;
        $this->serviceCount = $serviceCount;
        $this->serviceEndTime = $serviceEndTime;
        $this->serviceItemCode = $serviceItemCode;
        $this->serviceItemCodeId = $serviceItemCodeId;
        $this->serviceResultId = $serviceResultId;
        $this->serviceStartTime = $serviceStartTime;
        $this->serviceUnitAmount = $serviceUnitAmount;
        $this->serviceUseDate = $serviceUseDate;
        $this->specialMedicalCode = $specialMedicalCode;
        $this->targetDate = $targetDate;
        $this->totalCost = $totalCost;
        $this->unitNumber = $unitNumber;
        $this->unitPrice = $unitPrice;
    }

    public function getApproval(): ?int
    {
        return $this->approval;
    }

    /**
     * 給付率
     */
    public function getBenefitRate(): ?int
    {
        return $this->benefitRate;
    }

    /**
     * 負担者限度額
     */
    public function getBurdenLimit(): ?int
    {
        return $this->burdenLimit;
    }

    /**
     * 計算種別
     */
    public function getCalcKind(): ?int
    {
        return $this->calcKind;
    }

    public function getClassificationSupportLimitInRange(): ?int
    {
        return $this->classificationSupportLimitInRange;
    }

    public function getDocumentCreateDate(): string
    {
        return $this->documentCreateDate;
    }

    public function getFacilityId(): int
    {
        return $this->facilityId;
    }

    public function getFacilityNameKanji(): ?string
    {
        return $this->facilityNameKanji;
    }

    public function getFacilityNumber(): string
    {
        return $this->facilityNumber;
    }

    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 保険給付額を返す。
     */
    public function getInsuranceBenefit(): ?int
    {
        return $this->insuranceBenefit;
    }

    /**
     * 利用者負担(保険対象)を返す。
     */
    public function getPartPayment(): ?int
    {
        return $this->partPayment;
    }

    /**
     * 公費給付率を返す。
     */
    public function getPublicBenefitRate(): ?int
    {
        return $this->publicBenefitRate;
    }

    /**
     * 公費単位数合計を返す。
     */
    public function getPublicExpenditureUnit(): ?int
    {
        return $this->publicExpenditureUnit;
    }

    /**
     * 公費利用者負担額を返す。
     */
    public function getPublicPayment(): ?int
    {
        return $this->publicPayment;
    }

    /**
     * 公費請求額を返す。
     */
    public function getPublicSpendingAmount(): ?int
    {
        return $this->publicSpendingAmount;
    }

    /**
     * 公費分回数を返す。
     */
    public function getPublicSpendingCount(): ?int
    {
        return $this->publicSpendingCount;
    }

    /**
     * 公費対象単位数を返す。
     */
    public function getPublicSpendingUnitNumber(): ?int
    {
        return $this->publicSpendingUnitNumber;
    }

    public function getPublicUnitPrice(): ?int
    {
        return $this->publicUnitPrice;
    }

    /**
     * ランク
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

    /**
     * 実績フラグを返す。
     */
    public function getResultFlag(): ResultFlag
    {
        return $this->resultFlag;
    }

    /**
     * 実績種別を返す。
     */
    public function getResultKind(): int
    {
        return $this->resultKind;
    }

    public function getServiceCount(): int
    {
        return $this->serviceCount;
    }

    public function getServiceEndTime(): int
    {
        return $this->serviceEndTime;
    }

    /**
     * サービス項目コードを返す。
     */
    public function getServiceItemCode(): ServiceItemCode
    {
        return $this->serviceItemCode;
    }

    /**
     * サービス項目コードIDを返す。
     */
    public function getServiceItemCodeId(): int
    {
        return $this->serviceItemCode->getServiceItemCodeId();
    }

    public function getServiceStartTime(): int
    {
        return $this->serviceStartTime;
    }

    /**
     * サービス単位金額を返す。
     */
    public function getServiceUnitAmount(): ?int
    {
        return $this->serviceUnitAmount;
    }

    public function getServiceUseDate(): string
    {
        return $this->serviceUseDate;
    }

    /**
     * 特別診療コードを返す。
     */
    public function getSpecialMedicalCode(): ?SpecialMedicalCode
    {
        return $this->specialMedicalCode;
    }

    public function getTargetDate(): string
    {
        return $this->targetDate;
    }

    public function getTotalCost(): ?int
    {
        return $this->totalCost;
    }

    /**
     * 単位数を返す。
     */
    public function getUnitNumber(): ?int
    {
        return $this->unitNumber;
    }

    /**
     * 単位数単価を返す。
     */
    public function getUnitPrice(): ?int
    {
        return $this->unitPrice;
    }

    /**
     * 公費単位数合計を返す。
     */
    public function hasPublicExpenditureUnit(): bool
    {
        return $this->publicExpenditureUnit !== null;
    }

    /**
     * 特別診療コードをもつかを返す。
     */
    public function hasSpecialMedicalCode(): bool
    {
        return $this->specialMedicalCode !== null;
    }

    /**
     * 承認されているかを返す。
     */
    public function isApproval(): bool
    {
        return $this->approval === 1;
    }

    /**
     * 計算種別が事業所であるかを返す。
     */
    public function isFacility(): bool
    {
        return $this->calcKind === self::CALC_KIND_FACILITY;
    }

    /**
     * 計算種別が事業所(特殊)であるかを返す。
     */
    public function isFacilitySpecial(): bool
    {
        return $this->calcKind === self::CALC_KIND_FACILITY_SPECIAL;
    }

    /**
     * 実績種別が特定入所者サービスであるかを返す。
     */
    public function isIncompetentResident(): bool
    {
        return $this->resultKind === self::RESULT_KIND_INCOMPETENT_RESIDENT;
    }

    /**
     * 計算種別が個別であるかを返す。
     */
    public function isIndividual(): bool
    {
        return $this->calcKind === self::CALC_KIND_INDIVIDUAL;
    }

    /**
     * 実績種別がサービスであるかを返す。
     */
    public function isService(): bool
    {
        return $this->resultKind === self::RESULT_KIND_SERVICE;
    }

    /**
     * 実績種別が特別診療費であるかを返す。
     */
    public function isSpecialMedical(): bool
    {
        return $this->resultKind === self::RESULT_KIND_SPECIAL_MEDICAL;
    }

    /**
     * 計算種別が小計であるかを返す。
     */
    public function isSubTotal(): bool
    {
        return $this->calcKind === self::CALC_KIND_SUBTOTAL;
    }

    /**
     * 計算種別が合計であるかを返す。
     */
    public function isTotal(): bool
    {
        return $this->calcKind === self::CALC_KIND_TOTAL;
    }

    /**
     * @param int $classificationSupportLimitInRange
     */
    public function setClassificationSupportLimitInRange(int $classificationSupportLimitInRange): void
    {
        $this->classificationSupportLimitInRange = $classificationSupportLimitInRange;
    }

    /**
     * @param int $insuranceBenefit
     */
    public function setInsuranceBenefit(int $insuranceBenefit): void
    {
        $this->insuranceBenefit = $insuranceBenefit;
    }

    /**
     * @param int $partPayment
     */
    public function setPartPayment(int $partPayment): void
    {
        $this->partPayment = $partPayment;
    }

    /**
     * @param int $publicExpenditureUnit
     */
    public function setPublicExpenditureUnit(int $publicExpenditureUnit): void
    {
        $this->publicExpenditureUnit = $publicExpenditureUnit;
    }

    /**
     * @param int $publicPayment
     */
    public function setPublicPayment(int $publicPayment): void
    {
        $this->publicPayment = $publicPayment;
    }

    /**
     * @param int $publicSpendingUnitNumber
     */
    public function setPublicSpendingUnitNumber(int $publicSpendingUnitNumber): void
    {
        $this->publicSpendingUnitNumber = $publicSpendingUnitNumber;
    }

    /**
     * @param int $publicSpendingAmount
     */
    public function setPublicSpendingAmount(int $publicSpendingAmount): void
    {
        $this->publicSpendingAmount = $publicSpendingAmount;
    }

    /**
     * サービス単位金額をセットする。
     * @param int $serviceUnitAmount
     */
    public function setServiceUnitAmount(int $serviceUnitAmount): void
    {
        $this->serviceUnitAmount = $serviceUnitAmount;
    }

    /**
     * @param int $totalCost
     */
    public function setTotalCost(int $totalCost): void
    {
        $this->totalCost = $totalCost;
    }

    /**
     * @param int $unitNumber
     */
    public function setUnitNumber(int $unitNumber): void
    {
        $this->unitNumber = $unitNumber;
    }
}
