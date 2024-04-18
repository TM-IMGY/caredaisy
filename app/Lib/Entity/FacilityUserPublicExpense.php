<?php

namespace App\Lib\Entity;

use Carbon\Carbon;

/**
 * 施設利用者の公費。
 */
class FacilityUserPublicExpense
{
    private int $amountBornePerson;
    private ?string $applicationClassification;

    /**
     * @var ?string 負担者番号
     */
    private ?string $bearerNumber;

    private ?int $burdenStage;

    /**
     * 公費情報確認日
     */
    private ?string $confirmationMedicalInsuranceDate;

    private string $effectiveStartDate;
    private ?string $expiryDate;
    private int $facilityUserId;
    private ?int $foodExpensesBurdenLimit;
    private ?int $hospitalizationBurden;
    private ?int $livingExpensesBurdenLimit;
    private ?int $outpatientContribution;
    private PublicExpense $publicExpense;
    private int $publicExpenseInformationId;

    /**
     * @var ?string 受給者番号
     */
    private ?string $recipientNumber;

    private ?string $specialClassification;

    /**
     * コンストラクタ。
     * @param int amountBornePerson
     * @param ?string applicationClassification
     * @param ?string bearerNumber 負担者番号
     * @param ?int burdenStage
     * @param ?string confirmationMedicalInsuranceDate 公費情報確認日
     * @param string effectiveStartDate
     * @param ?string expiryDate
     * @param int facilityUserId 施設利用者ID
     * @param ?int foodExpensesBurdenLimit
     * @param ?int hospitalizationBurden
     * @param ?int livingExpensesBurdenLimit
     * @param ?int outpatientContribution
     * @param int publicExpenseInformationId
     * @param ?string recipientNumber 受給者番号
     * @param ?string specialClassification
     * @param int $benefitRate 公費マスタ給付率
     * @param string $effectiveStartDate 公費マスタ開始日
     * @param string $expiryDate 公費マスタ終了日
     * @param ?int $id 公費マスタID
     * @param string $legalName 公費マスタ名
     * @param int $legalNumber 公費マスタ法別番号
     * @param int $priority 公費マスタ優先度
     * @param ?int $serviceTypeCodeId 公費マスタサービス種類コードID
     */
    public function __construct(
        int $amountBornePerson,
        ?string $applicationClassification,
        ?string $bearerNumber,
        ?int $burdenStage,
        ?string $confirmationMedicalInsuranceDate,
        string $effectiveStartDate,
        ?string $expiryDate,
        int $facilityUserId,
        ?int $foodExpensesBurdenLimit,
        ?int $hospitalizationBurden,
        ?int $livingExpensesBurdenLimit,
        ?int $outpatientContribution,
        int $publicExpenseInformationId,
        ?string $recipientNumber,
        ?string $specialClassification,
        // 公費マスタ
        int $benefitRate,
        string $masterEffectiveStartDate,
        string $masterExpiryDate,
        ?int $id,
        string $legalName,
        int $legalNumber,
        int $priority,
        ?int $serviceTypeCodeId
    ) {
        $this->amountBornePerson = $amountBornePerson;
        $this->applicationClassification = $applicationClassification;
        $this->bearerNumber = $bearerNumber;
        $this->burdenStage = $burdenStage;
        $this->confirmationMedicalInsuranceDate = $confirmationMedicalInsuranceDate;
        $this->effectiveStartDate = $effectiveStartDate;
        $this->expiryDate = $expiryDate;
        $this->facilityUserId = $facilityUserId;
        $this->foodExpensesBurdenLimit = $foodExpensesBurdenLimit;
        $this->hospitalizationBurden = $hospitalizationBurden;
        $this->livingExpensesBurdenLimit = $livingExpensesBurdenLimit;
        $this->outpatientContribution = $outpatientContribution;
        $this->publicExpenseInformationId = $publicExpenseInformationId;
        $this->recipientNumber = $recipientNumber;
        $this->specialClassification = $specialClassification;

        // 公費マスタ
        $this->publicExpense = new PublicExpense(
            $benefitRate,
            $masterEffectiveStartDate,
            $masterExpiryDate,
            $id,
            $legalName,
            $legalNumber,
            $priority,
            $serviceTypeCodeId
        );
    }

    /**
     * 本人支払い額を返す。
     */
    public function getAmountBornePerson(): int
    {
        return $this->amountBornePerson;
    }

    /**
     * 負担者番号を返す。
     */
    public function getBearerNumber(): string
    {
        return $this->bearerNumber;
    }

    /**
     * 公費給付率を返す。
     */
    public function getBenefitRate(): int
    {
        return $this->publicExpense->getBenefitRate();
    }

    /**
     * 公費情報確認日を返す。
     */
    public function getConfirmationMedicalInsuranceDate(): ?string
    {
        return $this->confirmationMedicalInsuranceDate;
    }

    /**
     * 有効開始日を返す。
     */
    public function getEffectiveStartDate(): string
    {
        return $this->effectiveStartDate;
    }

    /**
     * 有効終了日を返す。
     */
    public function getExpiryDate(): ?string
    {
        return $this->expiryDate;
    }

    /**
     * 施設利用者IDを返す。
     */
    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 法別番号を返す。
     */
    public function getLegalNumber(): string
    {
        return $this->publicExpense->getLegalNumber();
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->publicExpense->getPriority();
    }

    /**
     * 公費マスター情報を返す。
     */
    public function getPublicExpense(): PublicExpense
    {
        return $this->publicExpense;
    }

    /**
     * IDを返す。
     */
    public function getPublicExpenseInformationId(): int
    {
        return $this->publicExpenseInformationId;
    }

    /**
     * 受給者番号を返す。
     */
    public function getRecipientNumber(): string
    {
        return $this->recipientNumber;
    }

    /**
     * 月途中公費かを返す。
     * @param int $year 対象年
     * @param int $month $対象月
     * @return bool
     */
    public function isMidMonth(int $year, int $month): bool
    {
        // 対象年月の日付全てについて公費の期間内かを判定し、日数を取得する。
        $date = new Carbon("${year}/${month}/1");
        $daysInMonth = $date->daysInMonth;
        $startDate = (new Carbon($this->effectiveStartDate));
        $endDate = (new Carbon($this->expiryDate));
        $dateCount = 0;
        for ($i = 0; $i < $daysInMonth; $i++) {
            if ($date->between($startDate, $endDate)) {
                $dateCount++;
            }
            $date->addDay();
        }

        return $dateCount !== $daysInMonth;
    }
}
