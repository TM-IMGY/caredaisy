<?php

namespace App\Lib\Entity;

use App\Lib\DomainService\ServiceItemCodeSpecification;

/**
 * サービス項目コード。
 */
class ServiceItemCode
{
    /**
     * @var int[] 介護医療院の摘要対象のサービスコードID
     */
    public const HOSPITAL_SUMMARY_TARGET_IDS = [
        // 55 1011 Ⅰ型医療院Ⅰⅱ１
        1013,
        // 55 1012 Ⅰ型医療院Ⅰⅱ１・夜減
        1014,
        // 55 1013 Ⅰ型医療院Ⅰⅱ２
        1015,
        // 55 1014 Ⅰ型医療院Ⅰⅱ２・夜減
        1016,
        // 55 1015 Ⅰ型医療院Ⅰⅱ３
        1017,
        // 55 1016 Ⅰ型医療院Ⅰⅱ３・夜減
        1018,
        // 55 1017 Ⅰ型医療院Ⅰⅱ４
        1019,
        // 55 1018 Ⅰ型医療院Ⅰⅱ４・夜減
        1020,
        // 55 1019 Ⅰ型医療院Ⅰⅱ５
        1021,
        // 55 1020 Ⅰ型医療院Ⅰⅱ５・夜減
        1022
    ];

    /**
     * 種類59のみが存在している。
     * @var int 特定入所者サービスコードのID
     */
    public const INCOMPETENT_RESIDENT_IDS = [
        // 59 5511 介護医療院食費
        2385,
        // 59 5521 介護医療院ユニット型個室
        2386,
        // 59 5522 介護医療院ユニット型個室的多床室
        2387,
        // 59 5523 介護医療院従来型個室
        2388,
        // 59 5524 介護医療院多床室
        2389
    ];

    /**
     * @var int[] 日割り対象のサービスコードID。
     */
    public const DAILY_IDS = [
        // 55 6501 医療院退所前訪問指導加算
        2375,
        // 55 6503 医療院訪問看護指示加算
        2377,
        // 55 6507 医療院退所後訪問指導加算
        2380,
        // 55 6831 医療院他科受診時費用
        2383
    ];

    /**
     * サービス項目コードには特別診療費コードのIDは国の規定では存在しない。
     * あくまでケアデイジーのシステム側の都合で作成された。
     * @var int 特別診療費コードのID
     */
    public const SPECIAL_MEDICAL_ID = 2384;

    /**
     * @var int 小計計算のID
     */
    public const SUBTOTAL_ID = 107;

    /**
     * @var int 合計計算のID
     */
    public const TOTAL_ID = 108;

    // 国が提示するテーブルにプレースホルダーとして存在する利用しないもの。
    // private int $reserve1;
    // private int $reserve2;
    // private int $reserve3;
    // private int $reserve4;
    private int $classificationSupportLimitFlg;
    private ?int $rank;
    private ?int $serviceCalcinfo1;
    private ?int $serviceCalcinfo2;
    private ?int $serviceCalcinfo3;
    private ?int $serviceCalcinfo4;
    private ?int $serviceCalcinfo5;

    /**
     * @var int サービス算定単位
     */
    private int $serviceCalculationUnit;

    private ?string $serviceEndDate;
    private string $serviceItemCode;
    private int $serviceItemCodeId;
    private string $serviceItemName;
    private int $serviceKind;
    private string $serviceStartDate;
    private int $serviceSyntheticUnit;
    private string $serviceTypeCode;
    private int $syntheticUnitInputFlg;

    public function __construct(
        int $classificationSupportLimitFlg,
        ?int $rank,
        ?int $serviceCalcinfo1,
        ?int $serviceCalcinfo2,
        ?int $serviceCalcinfo3,
        ?int $serviceCalcinfo4,
        ?int $serviceCalcinfo5,
        int $serviceCalculationUnit,
        ?string $serviceEndDate,
        string $serviceItemCode,
        int $serviceItemCodeId,
        string $serviceItemName,
        int $serviceKind,
        string $serviceStartDate,
        int $serviceSyntheticUnit,
        string $serviceTypeCode,
        int $syntheticUnitInputFlg
    ) {
        $this->classificationSupportLimitFlg = $classificationSupportLimitFlg;
        $this->rank = $rank;
        $this->serviceCalcinfo1 = $serviceCalcinfo1;
        $this->serviceCalcinfo2 = $serviceCalcinfo2;
        $this->serviceCalcinfo3 = $serviceCalcinfo3;
        $this->serviceCalcinfo4 = $serviceCalcinfo4;
        $this->serviceCalcinfo5 = $serviceCalcinfo5;
        $this->serviceCalculationUnit = $serviceCalculationUnit;
        $this->serviceEndDate = $serviceEndDate;
        $this->serviceItemCode = $serviceItemCode;
        $this->serviceItemCodeId = $serviceItemCodeId;
        $this->serviceItemName = $serviceItemName;
        $this->serviceKind = $serviceKind;
        $this->serviceStartDate = $serviceStartDate;
        $this->serviceSyntheticUnit = $serviceSyntheticUnit;
        $this->serviceTypeCode = $serviceTypeCode;
        $this->syntheticUnitInputFlg = $syntheticUnitInputFlg;
    }

    /**
     * ランク
     * @return ?int
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

    /**
     * @return ?int
     */
    public function getServiceCalcinfo1(): ?int
    {
        return $this->serviceCalcinfo1;
    }

    /**
     * @return ?int
     */
    public function getServiceCalcinfo2(): ?int
    {
        return $this->serviceCalcinfo2;
    }

    /**
     * サービス算定単位を返す。
     * @return int
     */
    public function getServiceCalculationUnit(): int
    {
        return $this->serviceCalculationUnit;
    }

    /**
     * サービスコードを返す。
     * @return string
     */
    public function getServiceCode(): string
    {
        return $this->serviceTypeCode . $this->serviceItemCode;
    }

    /**
     * サービス項目コードを返す。
     * @return string
     */
    public function getServiceItemCode(): string
    {
        return $this->serviceItemCode;
    }

    /**
     * サービス項目コードIDを返す。
     * @return int
     */
    public function getServiceItemCodeId(): int
    {
        return $this->serviceItemCodeId;
    }

    /**
     * @return string
     */
    public function getServiceItemName(): string
    {
        return $this->serviceItemName;
    }

    /**
     * サービス合成単位
     * @return int
     */
    public function getServiceSyntheticUnit(): int
    {
        return $this->serviceSyntheticUnit;
    }

    /**
     * サービス種類コードを返す。
     * @return string
     */
    public function getServiceTypeCode(): string
    {
        return $this->serviceTypeCode;
    }

    /**
     * 日割り対象かを返す。
     * @return bool
     */
    public function isDaily(): bool
    {
        return in_array($this->serviceItemCodeId, self::DAILY_IDS);
    }

    /**
     * 事業所でかつ特殊計算かを返す。
     * @return bool
     */
    public function isFacilitySpecial(): bool
    {
        return $this->serviceSyntheticUnit === 0;
    }

    /**
     * 認知症対応型初期加算かを返す。
     */
    public function isDementitaInitialAddition(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::DEMENTIA_INITIAL_ADDITION_IDS);
    }

    /**
     * 看取り介護加算かを返す。
     */
    public function isEndOfLifeCare(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::END_OF_LIFE_CARE_IDS);
    }

    /**
     * 入院時費用かを返す。
     */
    public function isHospitalization(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::HOSPITALIZATION_IDS);
    }

    /**
     * 退院退所時連携加算かを返す。
     */
    public function isLeavingHospital(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::SPECIFIC_FACILITY_LEAVING_HOSPITAL_IDS);
    }

    /**
     * 介護医療院の摘要対象かを返す。
     * TODO: 設計側に開発チームの用語が適切か確認が必要。
     * @return bool
     */
    public function isHospitalSummaryTarget(): bool
    {
        return in_array($this->serviceItemCodeId, self::HOSPITAL_SUMMARY_TARGET_IDS);
    }

    /**
     * 特定入所者サービスかを返す。
     * @return bool
     */
    public function isIncompetentResident(): bool
    {
        return in_array($this->serviceItemCodeId, self::INCOMPETENT_RESIDENT_IDS);
    }

    /**
     * 若年性認知症受入加算かを返す。
     */
    public function isJuvenileDementia(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::JUVENILE_DEMENTIA_IDS);
    }

    /**
     * 実績フラグが立たないサービスコードかを返す。
     * @return bool
     */
    public function isNoResult(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::NO_RESULT_IDS);
    }

    /**
     * 月ごとに実績が立つサービスコードかを返す。
     * @return bool
     */
    public function isPerMonth(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::PER_MONTH_IDS);
    }

    /**
     * 月ごとに予定が立つサービスコードかを返す。
     */
    public function isScheduledPerMonth(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::SCHEDULED_PER_MONTH_IDS);
    }

    /**
     * 小計かを返す。
     * @return bool
     */
    public function isSubTotal(): bool
    {
        return $this->serviceItemCodeId === self::SUBTOTAL_ID;
    }

    /**
     * 特別診療費かを返す。
     * @return bool
     */
    public function isSpecialMedical(): bool
    {
        return $this->serviceItemCodeId === self::SPECIAL_MEDICAL_ID;
    }

    /**
     * 合計かを返す。
     * @return bool
     */
    public function isTotal(): bool
    {
        return $this->serviceItemCodeId === self::TOTAL_ID;
    }

    /**
     * 予定が立たないサービスコードかを返す。
     */
    public function isUnScheduled(): bool
    {
        return in_array($this->serviceItemCodeId, ServiceItemCodeSpecification::UNSCHEDULED_IDS);
    }
}
