<?php

namespace App\Lib\InMemoryRepository\FacilityUser;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUser\StayOutRecordRepositoryInterface;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;

/**
 * 外泊の記録のインメモリのリポジトリ。
 */
class StayOutRecordInMemoryRepository implements StayOutRecordRepositoryInterface
{
    private array $db;

    public function __construct()
    {
        $this->db = [];
    }

    public function find(int $facilityUserId): StayOutRecord
    {
        // 施設利用者の外泊を全て取得する。
        $stayOuts = [];
        foreach ($this->db as $index => $record) {
            if ($record['facility_user_id'] !== $facilityUserId) {
                continue;
            }

            $stayOut = new StayOut(
                $record['end_date'],
                $record['facility_user_id'],
                $record['id'],
                $record['meal_of_the_day_end_dinner'],
                $record['meal_of_the_day_end_lunch'],
                $record['meal_of_the_day_end_morning'],
                $record['meal_of_the_day_end_snack'],
                $record['meal_of_the_day_start_dinner'],
                $record['meal_of_the_day_start_lunch'],
                $record['meal_of_the_day_start_morning'],
                $record['meal_of_the_day_start_snack'],
                $record['start_date'],
                $record['reason_for_stay_out'],
                $record['remarks'],
                $record['remarks_reason_for_stay_out']
            );
            $stayOuts[] = $stayOut;
        }

        $stayOutRecord = new StayOutRecord($stayOuts);
        return $stayOutRecord;
    }

    public function insert(StayOutRecord $stayOutRecord): void
    {
        $stayOuts = $stayOutRecord->getAll();
        foreach ($stayOuts as $index => $stayOut) {
            $this->db[] = [
                'end_date' => $stayOut->getEndDate(),
                'facility_user_id' => $stayOut->getFacilityUserId(),
                'id' => count($this->db) + 1,
                'meal_of_the_day_end_dinner' => null,
                'meal_of_the_day_end_lunch' => null,
                'meal_of_the_day_end_morning' => null,
                'meal_of_the_day_end_snack' => null,
                'meal_of_the_day_start_dinner' => null,
                'meal_of_the_day_start_lunch' => null,
                'meal_of_the_day_start_morning' => null,
                'meal_of_the_day_start_snack' => null,
                'start_date' => $stayOut->getStartDate(),
                'reason_for_stay_out' => $stayOut->getReasonForStayOut(),
                'remarks' => $stayOut->getRemarks(),
                'remarks_reason_for_stay_out' => $stayOut->getRemarksReasonForStayOut()
            ];
        }
    }
}
