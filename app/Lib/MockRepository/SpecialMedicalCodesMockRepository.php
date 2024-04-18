<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\SpecialMedicalCodesRepositoryInterface;
use App\Lib\Entity\SpecialMedicalCode;
use App\Lib\Entity\SpecialMedicalCodes;
use App\Utility\SeedingUtility;

/**
 * 特別診療費コードの集まりのモックリポジトリクラス。
 */
class SpecialMedicalCodesMockRepository implements SpecialMedicalCodesRepositoryInterface
{
    public array $records;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->records = SeedingUtility::getData('database/seeding_src/special_medical_codes.csv');
    }

    /**
     * サービス項目コードを返す。getのラッパー。
     * TODO: 対象年月で絞っていない。
     * @param int $specialMedicalCodeId 特別診療費コードのID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return SpecialMedicalCode
     */
    public function find(int $specialMedicalCodeId, int $year, int $month): SpecialMedicalCode
    {
        $specialMedicalCodes = $this->get([$specialMedicalCodeId], $year, $month);
        return $specialMedicalCodes->find($specialMedicalCodeId);
    }

    /**
     * 特別診療費コードを返す。
     * TODO: 対象年月で絞っていない。
     * @param array $specialMedicalCodeIds
     * @param int $year
     * @param int $month
     * @return ?SpecialMedicalCodes
     */
    public function get(array $specialMedicalCodeIds, int $year, int $month): ?SpecialMedicalCodes
    {
        // サービス項目コードを作成する。
        $specialMedicalCodes = [];
        foreach ($this->records as $record) {
            $id = $record['id'];
            if (!in_array($id, $specialMedicalCodeIds)) {
                continue;
            }

            $specialMedicalCodes[] = new SpecialMedicalCode(
                $record['end_date'],
                $record['history_num'],
                $record['id'],
                $record['identification_num'],
                $record['service_type_code'],
                $record['special_medical_name'],
                $record['start_date'],
                $record['unit']
            );
        }

        return new SpecialMedicalCodes($specialMedicalCodes);
    }
}
