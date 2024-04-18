<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceCodeConditionalBranchRepositoryInterface;
use App\Lib\Entity\ServiceCodeConditionalBranch;

/**
 * サービスコードの条件分岐表のモックリポジトリ。
 */
class ServiceCodeConditionalBranchMockRepository implements ServiceCodeConditionalBranchRepositoryInterface
{
    /**
     * サービスコードの条件分岐表を返す。
     * TODO: 対象年月で絞り込みをする。
     * @param string $serviceType サービス種類
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ServiceCodeConditionalBranch
     */
    public function find(string $serviceType, int $year, int $month): ServiceCodeConditionalBranch
    {
        // JSONファイルのディレクトリのパス。
        $directoryPath = 'database/json/auto_service_code/';

        // jsonファイルのパスを取得する。
        $jsonFilePath = "${directoryPath}${serviceType}_202104_202403.json";

        // jsonファイルを取得する。
        $jsonFile = json_decode(file_get_contents($jsonFilePath), true);

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
