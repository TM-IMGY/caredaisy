<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\Facility;

/**
 * 事業所のリポジトリのインターフェース。
 */
interface FacilityRepositoryInterface
{
    /**
     * 事業所を返す。
     * @param int $facilityId 事業所ID
     * @return ?Facility
     */
    public function find(int $facilityId): ?Facility;
}
