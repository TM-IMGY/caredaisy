<?php

namespace App\Lib\Entity;

use Carbon\Carbon;

/**
 * 施設利用者のサービスの記録クラス。
 * 施設利用者は対象年月中にサービスを一つではなく複数持ちうるためニーズが発生した。
 */
class FacilityUserServiceRecord
{
    private int $facilityUserId;

    /**
     * @var FacilityUserService[] 対象年月の施設利用者のサービス全て。
     */
    private array $facilityUserServices;

    /**
     * コンストラクタ
     * @param int $facilityUserId 施設利用者ID
     * @param FacilityUserService[] $facilityUserServices 対象年月の施設利用者のサービス全て。
     */
    public function __construct(
        int $facilityUserId,
        array $facilityUserServices
    ) {
        $this->facilityUserId = $facilityUserId;
        $this->facilityUserServices = $facilityUserServices;
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
     * サービス種別を返す。
     * @return array
     */
    public function getFacilityUserServices(): array
    {
        return $this->facilityUserServices;
    }

    /**
     * 有効な日付のサービス種別を返す。
     * @param string $date 対象の日付
     * @return FacilityUserService
     */
    public function getTargetDate($date): ?FacilityUserService
    {
        $targetYmDate = new Carbon($date);
        for ($i = 0; $i < count($this->facilityUserServices); $i++) {
            $startDate = new Carbon($this->facilityUserServices[$i]->getUseStart());
            $useEnd = $this->facilityUserServices[$i]->getUseEnd();
            // 対象日が終了日以下、または終了日がnull(無期限)を判定する。
            $isLessEndDate = is_null($useEnd) || (new Carbon($useEnd))->timestamp >= $targetYmDate->timestamp;
            if ($startDate->timestamp <= $targetYmDate->timestamp && $isLessEndDate) {
                return $this->facilityUserServices[$i];
            }
        }
        return null;
    }

    /**
     * 施設利用者の最新のサービス種類を返す。利用中のみを参照する。
     * @return FacilityUserService
     */
    public function getLatest(): FacilityUserService
    {
        $latest = null;
        foreach ($this->facilityUserServices as $index => $service) {
            // 利用中でないものは無視する。
            if (!$service->isInUse()) {
                continue;
            }

            // 初回。
            if ($latest === null) {
                $latest = $service;
                continue;
            }

            $latestStart = new Carbon($latest->getUseStart());
            $serviceStart = new Carbon($service->getUseStart());

            // 開始日が後のものを採用する。
            if ($latestStart->timestamp < $serviceStart->timestamp) {
                $latest = $service;
            }
        }
        return $latest;
    }

    /**
     * 履歴を持つかを返す。
     * @return bool
     */
    public function hasRecord(): bool
    {
        return count($this->facilityUserServices) > 0;
    }
}
