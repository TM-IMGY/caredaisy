<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceCodeConditionalBranchRepositoryInterface;
use App\Lib\Entity\ServiceCodeConditionalBranch;
use Carbon\CarbonImmutable;
use DB;
use File;
use Exception;

/**
 * サービスコードの条件分岐表のリポジトリクラス。
 */
class ServiceCodeConditionalBranchRepository implements ServiceCodeConditionalBranchRepositoryInterface
{
    public const STORAGE_PATH = 'json/auto_service_code/';

    /**
     * サービスコードの条件分岐表を返す。
     * @param string $serviceType サービス種類
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ServiceCodeConditionalBranch
     */
    public function find(string $serviceType, int $year, int $month): ServiceCodeConditionalBranch
    {
        // ストレージ内のjsonファイルを全て取得する。
        $dirPath = database_path(self::STORAGE_PATH);
        $files = glob($dirPath."*.json");
        if (count($files) === 0) {
            throw new Exception('can not get conditional branch.');
        }

        // 対象年月のjsonファイルのパスを取得する。
        $jsonFilePath = null;
        $targetYm = new CarbonImmutable("${year}-${month}-1");
        for ($i = 0, $cnt = count($files); $i < $cnt; $i++) {
            preg_match("#${dirPath}${serviceType}_([0-9]{4})([0-9]{2})_([0-9]{4})([0-9]{2}).json#", $files[$i], $result);
            if (count($result) === 0) {
                continue;
            }
            $startY = $result[1];
            $startM = $result[2];
            $endY = $result[3];
            $endM = $result[4];
            $startYm = new CarbonImmutable("${startY}-${startM}-1");
            $endYm = new CarbonImmutable("${endY}-${endM}-1");
            if ($startYm <= $targetYm && $endYm >= $targetYm) {
                $jsonFilePath = $files[$i];
                break;
            }
        }
        if ($jsonFilePath === null) {
            throw new Exception('can not get conditional branch.');
        }

        // jsonファイルを取得してデコードして返す。
        $jsonFile = json_decode(File::get($jsonFilePath), true);

        // jsonファイルのキー名がサービス種類によって異なるので調整する。
        // 32と37の場合。
        $jsonFileKey = 'dementia_communal_living_care';
        if (in_array($serviceType, ['33', '35', '36'])) {
            $jsonFileKey = 'care_addition_information';
        }

        $conditionalBranch = new ServiceCodeConditionalBranch($jsonFile[$jsonFileKey]);

        return $conditionalBranch;
    }
}
