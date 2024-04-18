<?php

namespace App\Service\GroupHome;

use App\Models\ServiceCode;

/**
 * サービスコードに関するユースケースを解決するクラス。
 */
class ServiceCodeService
{
    // 事業所加算だがブラウザより手動で追加できるサービスコード
    public const FACILITY_ADDITION_MANUALLY_ADDABLE_SERVICE_CODES = ['4001', '4002'];

    /**
     * サービスコードを返す
     * @param array $params
     * @return array
     */
    public function getServiceCodes(array $params)
    {
        // 個別に分類されるサービスコードを取得する
        $serviceCodeIndividual = ServiceCode::
            date($params['year'], $params['month'])
            ->where('service_type_code', $params['service_type_code'])
            ->where('service_kind', 1)
            // リリース1.6暫定対応
            ->where('classification_support_limit_flg', '<>', 1)
            ->select('service_item_code_id', 'service_item_code', 'service_item_name', 'service_synthetic_unit', 'synthetic_unit_input_flg')
            ->get()
            ->toArray();

        // 個別ではないが特例として個別として扱う認知症対応型生活機能向上連携加算を取得する
        $serviceCodeLifeFunction = ServiceCode::
            date($params['year'], $params['month'])
            ->whereIn('service_item_code', self::FACILITY_ADDITION_MANUALLY_ADDABLE_SERVICE_CODES)
            ->where('service_kind', 2)
            ->where('service_type_code', $params['service_type_code'])
            ->select('service_item_code', 'service_item_code_id', 'service_item_name', 'service_synthetic_unit', 'synthetic_unit_input_flg')
            ->get()
            ->toArray();

        return array_merge($serviceCodeIndividual, $serviceCodeLifeFunction);
    }

    /**
     * 特定入所者サービスのサービスコードを返す。
     * @param int $year 対象年。
     * @param int $month 対象月。
     * @return array
     */
    public function listIncompetentResidents(int $year, int $month): array
    {
        $incompetentResidents = ServiceCode::listIncompetentResidents($year, $month);
        return $incompetentResidents;
    }
}
