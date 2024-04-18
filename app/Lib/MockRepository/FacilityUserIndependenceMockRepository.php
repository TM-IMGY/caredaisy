<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserIndependenceRepositoryInterface;
use App\Lib\Entity\FacilityUserIndependence;

/**
 * 施設利用者の自立度のモックリポジトリクラス。
 */
class FacilityUserIndependenceMockRepository implements FacilityUserIndependenceRepositoryInterface
{
    /**
     * 施設利用者の対象年月の自立度を返す。
     * 対象年月に複数の自立度が存在する場合は最新を返す。
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUserIndependence
     */
    public function find(int $facilityUserId, int $year, int $month): ?FacilityUserIndependence
    {
        // TODO: 実装する。
        return null;
    }
}
