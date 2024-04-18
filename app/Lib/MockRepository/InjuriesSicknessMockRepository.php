<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\InjuriesSicknessRepositoryInterface;
use App\Lib\Entity\InjuriesSickness;
use App\Lib\Entity\InjuriesSicknessDetail;
use App\Lib\Entity\InjuriesSicknessRelation;
use App\Lib\MockRepository\DataSets\InjuriesSicknessDataSets;

/**
 * 傷病の集まりのモックリポジトリクラス。
 */
class InjuriesSicknessMockRepository implements InjuriesSicknessRepositoryInterface
{
    /**
     * 指定の傷病名を返す。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return ?InjuriesSickness
     */
    public function find(int $facilityUserId, int $year, int $month): ?InjuriesSickness
    {
        $injuriesSicknesses = $this->get([$facilityUserId], $year, $month);
        if (count($injuriesSicknesses) === 0) {
            return null;
        }
        return $injuriesSicknesses[0];
    }

    /**
     * 傷病名を返す。
     * @param array $facilityUserIds
     * @param int $year
     * @param int $month
     * @return InjuriesSickness[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array
    {
        // 作成する傷病名を確保する領域を宣言する。
        $injuriesSicknesses = [];
        // 作成する傷病名詳細を確保する領域を宣言する。
        $details = [];

        foreach (InjuriesSicknessDataSets::DATA as $record) {
            foreach ($record['details'] as $detailRecords) {
                // 配列の初期化
                $targetRelations = [];
                foreach ($detailRecords['relation'] as $relationRecords) {
                    $targetRelations[] = new InjuriesSicknessRelation(
                        $relationRecords['relation_id'],
                        $relationRecords['selected_position'],
                        $relationRecords['special_medical_code_id']
                    );
                }

                // 傷病名詳細を作成する。
                $detail = new InjuriesSicknessDetail(
                    $detailRecords['detail_id'],
                    $detailRecords['group'],
                    $targetRelations,
                    $detailRecords['name']
                );
                $details[] = $detail;
            }

            // 傷病名を作成する。
            $injuriesSickness = new InjuriesSickness(
                $record['end_date'],
                $record['facility_user_id'],
                $record['id'],
                $details,
                $record['start_date']
            );

            $injuriesSicknesses[] = $injuriesSickness;
        }

        return $injuriesSicknesses;
    }
}
