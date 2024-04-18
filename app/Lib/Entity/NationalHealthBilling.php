<?php

namespace App\Lib\Entity;

/**
 * 国保連請求クラス。
 * 計算種別 > 実績種別の順で分類が優先される。
 */
class NationalHealthBilling
{
    /**
     * @var int 施設利用者ID
     */
    private int $facilityUserId;

    /**
     * @var ServiceResult[] サービス実績
     */
    private array $serviceResults;

    /**
     * コンストラクタ
     * @param int $facilityUserId 施設利用者ID
     * @param ServiceResult[] $serviceResults サービス実績
     */
    public function __construct(
        int $facilityUserId,
        array $serviceResults
    ) {
        $this->facilityUserId = $facilityUserId;
        // TODO: 理想は配列の中身が正しくサービス実績クラスであることを検閲すること。
        $this->serviceResults = $serviceResults;
    }

    /**
     * 明細を返す。
     * @return ServiceResult[]
     */
    public function getDetails(): array
    {
        $details = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isIndividual() || $serviceResult->isFacility() || $serviceResult->isFacilitySpecial();
        });
        return array_values($details);
    }

    /**
     * 施設利用者IDを返す。
     * @return int
     */
    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 特定入所者介護サービスを全て返す。
     * todo メソッド名を説明に合わせる
     * @return ServiceResult[]
     */
    public function getIncompetentResidents(): array
    {
        $incompetentResidents = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isIncompetentResident();
        });
        return array_values($incompetentResidents);
    }

    /**
     * 特定入所者介護サービスの明細を返す。
     * todo メソッド名を説明に合わせる
     * @return ServiceResult[]
     */
    public function getIncompetentResidentIndividuals(): array
    {
        $individuals = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isIncompetentResident() && $serviceResult->isIndividual();
        });
        return array_values($individuals);
    }

    /**
     * 特定入所者介護サービスの合計を返す。
     * @return ?ServiceResult
     */
    public function getIncompetentResidentTotal(): ?ServiceResult
    {
        $total = null;
        foreach ($this->serviceResults as $serviceResult) {
            if ($serviceResult->isIncompetentResident() && $serviceResult->isTotal()) {
                $total = $serviceResult;
                // 特定入所者介護サービスの合計は一つしか存在しえないので抜ける。
                break;
            }
        }
        return $total;
    }

    /**
     * 計算種別個別を全て返す。
     * @return ServiceResult[]
     */
    public function getIndividuals(): array
    {
        $individuals = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isIndividual();
        });

        return array_values($individuals);
    }

    /**
     * サービスの明細を返す。
     * @return ServiceResult[]
     */
    public function getServiceDetails(): array
    {
        $details = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isService() && ($serviceResult->isIndividual() || $serviceResult->isFacility() || $serviceResult->isFacilitySpecial());
        });
        return array_values($details);
    }

    /**
     * 実績種別サービスでかつ個別を全て返す。
     * @return ServiceResult[]
     */
    public function getServiceIndividuals(): array
    {
        $individuals = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isService() && $serviceResult->isIndividual();
        });

        return array_values($individuals);
    }

    /**
     * @return ServiceResult[]
     */
    public function getServiceResults(): array
    {
        return $this->serviceResults;
    }

    /**
     * 計算種別合計で実績種別サービスを返す。
     * @return ?ServiceResult
     */
    public function getServiceTotal(): ?ServiceResult
    {
        $totalService = null;
        foreach ($this->serviceResults as $serviceResult) {
            if ($serviceResult->isTotal() && $serviceResult->isService()) {
                $totalService = $serviceResult;
            }
        }
        return $totalService;
    }

    /**
     * 特別診療費の明細を返す。
     * @param ?InjuriesSickness 傷病
     * @return ServiceResult[]
     */
    public function getSpecialMedicalIndividuals(?InjuriesSickness $injuriesSickness): array
    {
        $individuals = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isSpecialMedical() && $serviceResult->isIndividual();
        });

        if ($injuriesSickness === null) {
            return array_values($individuals);
        }

        // 傷病のグループ順、選択位置でソートする。
        // TODO: コンストラクタでソートしてしまえば面倒はない。
        // TODO: 傷病を渡すのではなく特別診療費が傷病を持つように再実装する。
        usort($individuals, function ($a, $b) use ($injuriesSickness) {
            $specialMedicalCodeIdA = $a->getSpecialMedicalCode()->getSpecialMedicalCodeId();
            $specialMedicalCodeIdB = $b->getSpecialMedicalCode()->getSpecialMedicalCodeId();
            $detailA = $injuriesSickness->findDetail($specialMedicalCodeIdA);
            $detailB = $injuriesSickness->findDetail($specialMedicalCodeIdB);
            if ($detailA->getGroup() < $detailB->getGroup()) {
                return -1;
            }
            $selectedPositionA = $detailA->findRelation($specialMedicalCodeIdA)->getSelectedPosition();
            $selectedPositionB = $detailB->findRelation($specialMedicalCodeIdB)->getSelectedPosition();
            if ($detailA->getGroup() === $detailB->getGroup() && $selectedPositionA < $selectedPositionB) {
                return -1;
            }
            return 1;
        });

        return array_values($individuals);
    }

    /**
     * 実績種別が特別診療費で、計算種別が合計を返す。
     * @return ?ServiceResult
     */
    public function getSpecialMedicalTotal(): ?ServiceResult
    {
        $total = null;
        foreach ($this->serviceResults as $serviceResult) {
            if ($serviceResult->isSpecialMedical() && $serviceResult->isTotal()) {
                $total = $serviceResult;
                // 特別診療費の合計は一つしか存在しないので抜ける。
                break;
            }
        }
        return $total;
    }

    /**
     * 合計を全て返す。
     * NOTE: 種類55のみ実績種別サービス、特別診療費コード、特定入所者介護サービスの三つを返す。それ以外はサービスのみ。
     * @return ServiceResult[]
     */
    public function getTotals(): array
    {
        $totals = array_filter($this->serviceResults, function ($serviceResult) {
            return $serviceResult->isTotal();
        });
        return array_values($totals);
    }

    /**
     * サービス明細を持つかを返す。
     * @return bool
     */
    public function hasDetailService(): bool
    {
        return count($this->getServiceDetails()) > 0;
    }

    /**
     * サービス実績を持つかを返す。
     * @return bool
     */
    public function hasServiceResults(): bool
    {
        return count($this->serviceResults) > 0;
    }

    /**
     * 計算種別合計で実績種別サービスを持つかを返す。
     * @return bool
     */
    public function hasTotalService(): bool
    {
        return $this->getServiceTotal() !== null;
    }

    /**
     * 特別診療費を持つかを返す。
     * @return bool
     */
    public function hasSpecialMedical(): bool
    {
        return count($this->getSpecialMedicalIndividuals(null)) > 0;
    }

    /**
     * 特定入所者介護サービスを持つかを返す
     * @return bool
     */
    public function hasIncompetentResident(): bool
    {
        return count($this->getIncompetentResidentIndividuals()) > 0;
    }
}
