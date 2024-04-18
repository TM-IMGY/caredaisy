<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUser;

use App\Lib\Entity\FacilityUser\StayOutRecord;

/**
 * 施設利用者の外泊の記録のリポジトリのインターフェース。
 */
interface StayOutRecordRepositoryInterface
{
    /**
     * 施設利用者の外泊の記録を返す。
     * @param int $facilityUserId 施設利用者のID
     * @return StayOutRecord
     */
    public function find(int $facilityUserId): StayOutRecord;
}
