<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\ServiceItemCodes;

/**
 * サービス項目コードの集まりのリポジトリのインターフェース。
 * サービス項目コードを単体ではなく、ある程度のまとまりとして扱う想定が増えたため作成した。
 */
interface ServiceItemCodesRepositoryInterface
{
    public function get(array $serviceItemCodeIds, int $year, int $month): ServiceItemCodes;

    public function getByServiceItemCodes(string $typeCode, array $itemCodes, int $year, int $month): ServiceItemCodes;
}
