<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceItemCodesRepositoryInterface;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\ServiceItemCodes;
use App\Utility\SeedingUtility;

/**
 * サービス項目コードの集まりのリポジトリ。
 */
class ServiceItemCodesMockRepository implements ServiceItemCodesRepositoryInterface
{
    public array $records;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->records = SeedingUtility::getData('database/seeding_src/m_service_codes.csv');
    }

    /**
     * サービス項目コードを返す。getのラッパー。
     * TODO: 対象年月で絞っていない。
     * @param int $serviceItemCodeId サービス項目コードID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ServiceItemCode
     */
    public function find(int $serviceItemCodeId, int $year, int $month): ServiceItemCode
    {
        $serviceItemCodes = $this->get([$serviceItemCodeId], $year, $month);
        return $serviceItemCodes->find($serviceItemCodeId);
    }

    /**
     * サービス項目コードを返す。
     * TODO: 対象年月で絞っていない。
     * @param array $serviceItemCodeIds サービス項目コード
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ServiceItemCodes
     */
    public function get(array $serviceItemCodeIds, int $year, int $month): ServiceItemCodes
    {
        // サービス項目コードを作成する。
        $serviceItemCodes = [];
        foreach ($this->records as $record) {
            $serviceItemCodeId = $record['service_item_code_id'];
            if (!in_array($serviceItemCodeId, $serviceItemCodeIds)) {
                continue;
            }

            $serviceItemCodes[] = new ServiceItemCode(
                $record['classification_support_limit_flg'],
                $record['rank'],
                array_key_exists('service_calcinfo_1', $record) ? $record['service_calcinfo_1'] : null,
                array_key_exists('service_calcinfo_2', $record) ? $record['service_calcinfo_2'] : null,
                array_key_exists('service_calcinfo_3', $record) ? $record['service_calcinfo_3'] : null,
                array_key_exists('service_calcinfo_4', $record) ? $record['service_calcinfo_4'] : null,
                array_key_exists('service_calcinfo_5', $record) ? $record['service_calcinfo_5'] : null,
                $record['service_calculation_unit'],
                $record['service_end_date'],
                $record['service_item_code'],
                $serviceItemCodeId,
                $record['service_item_name'],
                $record['service_kind'],
                $record['service_start_date'],
                $record['service_synthetic_unit'],
                $record['service_type_code'],
                $record['synthetic_unit_input_flg']
            );
        }

        return new ServiceItemCodes($serviceItemCodes);
    }

    /**
     * TODO: 対象年月で絞っていない。
     */
    public function getByServiceItemCodes(string $typeCode, array $itemCodes, int $year, int $month): ServiceItemCodes
    {
        $data = [];
        foreach ($this->records as $record) {
            $isSameServiceTypeCode = $record['service_type_code'] === $typeCode;
            $isSameServiceItemCode = in_array($record['service_item_code'], $itemCodes);

            if ($isSameServiceTypeCode && $isSameServiceItemCode) {
                $data[] = new ServiceItemCode(
                    $record['classification_support_limit_flg'],
                    $record['rank'],
                    array_key_exists('service_calcinfo_1', $record) ? $record['service_calcinfo_1'] : null,
                    array_key_exists('service_calcinfo_2', $record) ? $record['service_calcinfo_2'] : null,
                    array_key_exists('service_calcinfo_3', $record) ? $record['service_calcinfo_3'] : null,
                    array_key_exists('service_calcinfo_4', $record) ? $record['service_calcinfo_4'] : null,
                    array_key_exists('service_calcinfo_5', $record) ? $record['service_calcinfo_5'] : null,
                    $record['service_calculation_unit'],
                    $record['service_end_date'],
                    $record['service_item_code'],
                    $record['service_item_code_id'],
                    $record['service_item_name'],
                    $record['service_kind'],
                    $record['service_start_date'],
                    $record['service_synthetic_unit'],
                    $record['service_type_code'],
                    $record['synthetic_unit_input_flg']
                );
            }
        }

        return new ServiceItemCodes($data);
    }
}
