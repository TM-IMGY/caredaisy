<?php

namespace App\Lib\Entity;

use App\Lib\EndOfLifeCareAddition;
use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの明細情報レコード。
 */
class DetailRecord
{
    private array $record;

    public function __construct(
        string $blankValue,
        string $exchangeInformationNumber,
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserService $facilityUserService,
        ServiceResult $serviceResult,
        int $serialNumber,
        string $targetYm
    ) {
        $summary = $blankValue;
        // サービスコードが日割り対象の場合。
        $serviceItemCode = $serviceResult->getServiceItemCode();
        if ($serviceItemCode->isDaily()) {
            $dateDailyRate = str_split($serviceResult->getResultFlag()->getDateDailyRate());
            for ($i = 0, $cnt = count($dateDailyRate); $i < $cnt; $i++) {
                $rate = $dateDailyRate[$i];
                if ($rate === '0') {
                    continue;
                }
                $summary .= ($i + 1).',';
            }
            $summary = rtrim($summary, ',');
        // サービスコードが介護医療院の摘要対象の場合。
        } elseif ($serviceItemCode->isHospitalSummaryTarget()) {
            // 顧客に対象がいないというだけで本来は1固定ではない。
            $summary = 1;
        // サービスコードが看取りの場合。
        } elseif ($serviceItemCode->isEndOfLifeCare()) {
            $tmpDeathDate = new CarbonImmutable($facilityUser->getDeathDate());
            $summary = $tmpDeathDate->format('Ymd');
        }

        $this->record = [
            // 1 レコード種別 マジックナンバー
            '2',
            // 2 レコード番号(連番)
            $serialNumber,
            // 3 交換情報識別番号
            $exchangeInformationNumber,
            // 4 レコード種別コード マジックナンバー
            '02',
            // 5 サービス提供年月
            $targetYm,
            // 6 事業所番号
            $facility->getFacilityNumber(),
            // 7 証記載保険者番号
            sprintf('%08d', $facilityUser->getInsurerNo()),
            // 8 被保険者番号
            $facilityUser->getInsuredNo()->getValue(),
            // 9 サービス種類コード
            $facilityUserService->getServiceTypeCode()->getServiceTypeCode(),
            // 10 サービス項目コード
            $serviceResult->getServiceItemCode()->getServiceItemCode(),
            // 11 単位数
            $serviceResult->getUnitNumber(),
            // 12 日数・回数
            $serviceResult->getResultFlag()->getServiceCountDate(),
            // 13 公費１対象日数・回数
            $serviceResult->getPublicSpendingCount(),
            // 14 公費2対象日数・回数
            $blankValue,
            // 15 公費3対象日数・回数
            $blankValue,
            // 16 サービス単位数
            $serviceResult->getServiceUnitAmount(),
            // 17 公費1対象サービス単位数
            $serviceResult->getPublicExpenditureUnit(),
            // 18 公費2対象サービス単位数
            $blankValue,
            // 19 公費3対象サービス単位数
            $blankValue,
            // 20 摘要
            $summary
        ];
    }

    /**
     * レコードを返す。
     * @return array
     */
    public function getRecord(): array
    {
        return $this->record;
    }
}
