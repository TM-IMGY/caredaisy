<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUserPublicExpense;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;

/**
 * 施設利用者の公費のリポジトリのインターフェース。
 */
interface FacilityUserPublicExpenseRecordRepositoryInterface
{
    /**
     * 施設利用者の公費の記録を返す。
     * @param Facility $facility 事業所。事業所を渡すことで、そのサービス種類から公費を絞ることができる。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUserPublicExpenseRecord
     */
    public function find(Facility $facility, int $facilityUserId, int $year, int $month): ?FacilityUserPublicExpenseRecord;

    /**
     * 施設利用者の公費を返す。
     * 施設利用者の公費は対象年月単位で取り扱うのが原則だが、一部で個別で取得する必要があったため追加された。
     * @param int $facilityUserPublicExpenseId 施設利用者の公費のID
     */
    public function findById(int $facilityUserPublicExpenseId): ?FacilityUserPublicExpense;

    /**
     * 施設利用者の公費の記録を返す。
     * @param Facility $facility 事業所。事業所を渡すことで、そのサービス種類から公費を絞ることができる。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserPublicExpenseRecord[]
     */
    public function get(Facility $facility, array $facilityUserIds, int $year, int $month): array;
}
