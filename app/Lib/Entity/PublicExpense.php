<?php

namespace App\Lib\Entity;

use App\Lib\DomainService\LegalNumberSpecification;

/**
 * 公費。
 */
class PublicExpense
{
    private int $benefitRate;
    private string $effectiveStartDate;
    private string $expiryDate;
    private ?int $id;
    private string $legalName;
    private int $legalNumber;
    private int $priority;
    private ?int $serviceTypeCodeId;

    /**
     * コンストラクタ。
     * @param int $benefitRate
     * @param string $effectiveStartDate
     * @param string $expiryDate
     * @param ?int $id
     * @param string $legalName 公費略称
     * @param int $legalNumber 法別番号
     * @param int $priority
     * @param ?int $serviceTypeCodeId
     */
    public function __construct(
        int $benefitRate,
        string $effectiveStartDate,
        string $expiryDate,
        ?int $id,
        string $legalName,
        int $legalNumber,
        int $priority,
        ?int $serviceTypeCodeId
    ) {
        $this->benefitRate = $benefitRate;
        $this->effectiveStartDate = $effectiveStartDate;
        $this->expiryDate = $expiryDate;
        $this->id = $id;
        $this->legalName = $legalName;
        $this->legalNumber = $legalNumber;
        $this->priority = $priority;
        $this->serviceTypeCodeId = $serviceTypeCodeId;
    }

    /**
     * 給付率を返す。
     */
    public function getBenefitRate(): int
    {
        return $this->benefitRate;
    }

    /**
     * 開始日を返す。
     */
    public function getEffectiveStartDate(): string
    {
        return $this->effectiveStartDate;
    }

    /**
     * 終了日を返す。
     */
    public function getExpiryDate(): ?string
    {
        return $this->expiryDate;
    }

    /**
     * idを返す。
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * 公費略称を返す。
     */
    public function getLegalName(): string
    {
        return $this->legalName;
    }

    /**
     * 法別番号を返す。
     */
    public function getLegalNumber(): string
    {
        return $this->legalNumber;
    }

    /**
     * 優先度を返す。
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * サービス種類コードIDを返す。
     */
    public function getServiceTypeCodeId(): ?int
    {
        return $this->serviceTypeCodeId;
    }

    /**
     * 公費が給付率について100%しか持たないかを返す。
     */
    public function is100Percent(): bool
    {
        // TODO: ハードコーディングを止める。
        return in_array($this->legalNumber, [12, 15, 19, 25, 51, 54, 66, 81, 86, 87, 88]);
    }

    /**
     * 本人支払い額がありえるかを返す。
     */
    public function isAmountBornePersonPossible(): bool
    {
        $isChina = $this->isChina();

        $isIncurableDisease = $this->isIncurableDisease();

        $isPublicAssistance = $this->isPublicAssistance();

        $isRehabilitation = $this->isRehabilitation();

        // 条件は中国残留邦人等、難病公費、生活保護、自立更生であること。
        return $isChina || $isIncurableDisease || $isPublicAssistance || $isRehabilitation;
    }

    /**
     * 中国残留邦人等かを返す。
     */
    public function isChina(): bool
    {
        return $this->legalNumber === LegalNumberSpecification::CHINA;
    }

    /**
     * 難病公費かを返す。
     */
    public function isIncurableDisease(): bool
    {
        return $this->legalNumber === LegalNumberSpecification::INCURABLE_DISEASE;
    }

    /**
     * 生活保護かを返す。
     */
    public function isPublicAssistance(): bool
    {
        return $this->legalNumber === LegalNumberSpecification::PUBLIC_ASSISTANCE;
    }

    /**
     * 自立更生かを返す。
     */
    public function isRehabilitation(): bool
    {
        return $this->legalNumber === LegalNumberSpecification::REHABILITATION;
    }

    /**
     * サービス種類を対象とするかを返す。
     * 例えば法別番号12の生活保護は種類32、33、35、36、37、55、59などを対象としている。
     * この時、それぞれの種類が生活保護を持つのではなく、生活保護がそれぞれの種類を対象とするニュアンスの方が近いので注意する。
     * (つまりサービス種類が違っても生活保護は生活保護)。
     * @param string $serviceTypeCode サービス種類コード。
     * @return bool
     */
    public function isAvailable(string $serviceTypeCode): bool
    {
        // TODO: 種類59以外でのみ必要な判定だが、他の種類の条件分岐も追加する。
        // TODO: ハードコーディングを止める。
        if ($serviceTypeCode === '59') {
            return in_array($this->legalNumber, [12, 25]);
        }

        return false;
    }
}
