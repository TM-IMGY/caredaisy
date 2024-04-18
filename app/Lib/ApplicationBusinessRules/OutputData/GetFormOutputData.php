<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式取得の出力データクラス。
 */
class GetFormOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param GetFormDetailOutputData[] $details
     * @param ?GetFormTotalOutputData $total
     * @param int $serviceTypeCodeId
     * @param GetFormSpecialMedicalOutputData[] $specialMedicals
     * @param ?GetFormTotalSpecialMedicalOutputData $totalSpecialMedical
     * @param GetFormIncompetentResidentOutputData[] $incompetentResidents
     * @param ?GetFormTotalIncompetentResidentOutputData $totalIncompetentResident
     */
    public function __construct(
        array $details,
        ?GetFormTotalOutputData $total,
        int $serviceTypeCodeId,
        array $specialMedicals,
        ?GetFormTotalSpecialMedicalOutputData $totalSpecialMedical,
        array $incompetentResidents,
        ?GetFormTotalIncompetentResidentOutputData $totalIncompetentResident
    ) {
        $this->data = [
            // 給付費明細欄
            'details' => array_map(function ($detail) {
                return $detail->getData();
            }, $details),

            // 請求集計欄(保険分、公費分)
            'total' => $total === null ? null : $total->getData(),

            // 特別診療費
            'special_medicals' => array_map(function ($specialMedical) {
                return $specialMedical->getData();
            }, $specialMedicals),

            // 請求集計欄(保険分特定治療・特別診療費、公費分特定治療・特別診療費)
            'total_special_medical' => $totalSpecialMedical === null ? null : $totalSpecialMedical->getData(),

            // 特定入所者介護サービス費
            'incompetent_residents' => array_map(function ($incompetentResident) {
                return $incompetentResident->getData();
            }, $incompetentResidents),

            // 特定入所者介護サービス費(合計)
            'total_incompetent_resident' => $totalIncompetentResident === null ? null : $totalIncompetentResident->getData(),

            // サービス種類コードID
            'service_type_code_id' => $serviceTypeCodeId
        ];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
