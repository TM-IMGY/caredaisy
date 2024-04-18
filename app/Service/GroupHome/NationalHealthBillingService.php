<?php

namespace App\Service\GroupHome;

use App\Lib\Entity\NationalHealthBillingCsv;
use App\Lib\Repository\BasicRemarkRepository;
use App\Lib\Repository\FacilityRepository;
use App\Lib\Repository\FacilityUserCareRecordRepository;
use App\Lib\Repository\FacilityUserPublicExpenseRecordRepository;
use App\Lib\Repository\FacilityUserRepository;
use App\Lib\Repository\FacilityUserServiceRecordRepository;
use App\Lib\Repository\InjuriesSicknessRepository;
use App\Lib\Repository\NationalHealthBillingRepository;
use App\Models\ServiceResult;
use App\Models\UserPublicExpenseInformation;
use App\Lib\EndOfLifeCareAddition;
use App\Service\GroupHome\UserBenefitInformationService;
use App\Service\GroupHome\UserCareInformationService;
use App\Lib\Common\Consts;

use Carbon\CarbonImmutable;
use DB;
use Exception;

class NationalHealthBillingService
{
    public const BLANK_VALUE = '';

    /**
     * 国保連請求のcsvデータを返す。
     * @param int $facilityId 事業所ID
     * @param array $facilityUserIds 施設利用者ID
     * @param int $invoiceFlg 伝送フラグ
     * @param int $year 対象年
     * @param int $month 対象月
     * @return string
     */
    public function getCsvData(int $facilityId, array $facilityUserIds, int $invoiceFlg, int $year, int $month): string
    {
        // csvのデータを格納するプレースホルダー。
        $data = [];

        // 事業所を取得する。
        $facility = null;
        try {
            // TODO: DIする。
            $facilityRepository = new FacilityRepository();
            $facility = $facilityRepository->find($facilityId);
        } catch (Exception $e) {
            abort(422, '事業所の取得に失敗しました。');
        }
        if ($facility === null) {
            abort(422, '事業所が見つかりません。');
        }

        // 施設利用者を全て取得する。
        $facilityUsers = null;
        $facilityUserCareRecords = null;
        $facilityUserPublicExpenseRecord = null;
        $facilityUserServiceRecords = null;
        try {
            $facilityUserRepository = new FacilityUserRepository();
            $facilityUserCareRecordRepository = new FacilityUserCareRecordRepository();
            $facilityUserPublicExpenseRecordRepository = new FacilityUserPublicExpenseRecordRepository();
            $facilityUserServiceRecordRepository = new FacilityUserServiceRecordRepository();

            $facilityUsers = $facilityUserRepository->get($facilityUserIds, $year, $month);
            $facilityUserCareRecords = $facilityUserCareRecordRepository->get($facilityUserIds, $year, $month);
            $facilityUserPublicExpenseRecord = $facilityUserPublicExpenseRecordRepository->get($facility, $facilityUserIds, $year, $month);
            $facilityUserServiceRecords = $facilityUserServiceRecordRepository->get($facilityUserIds, $year, $month);
        } catch (Exception $e) {
            abort(422, '施設利用者情報の取得に失敗しました。');
        }

        // 施設利用者の基本摘要を取得する。
        $basicRemarks = null;
        try {
            // TODO: DIする。
            $basicRemarkRepository = new BasicRemarkRepository();
            $basicRemarks = $basicRemarkRepository->get($facilityUserIds, $year, $month);
        } catch (Exception $e) {
            abort(422, '基本摘要の取得に失敗しました。');
        }
        if ($basicRemarks === null) {
            abort(422, '基本摘要が見つかりません。');
        }

        // 施設利用者の傷病名を取得する。
        $injuriesSickness = null;
        try {
            // TODO: DIする。
            $injuriesSicknessRepository = new InjuriesSicknessRepository();
            $injuriesSickness = $injuriesSicknessRepository->get($facilityUserIds, $year, $month);
        } catch (Exception $e) {
            abort(422, '傷病名の取得に失敗しました。');
        }
        if ($injuriesSickness === null) {
            abort(422, '傷病名が見つかりません。');
        }

        // サービス実績を施設利用者全てについて取得する。
        $nationalHealthBillings = null;
        try {
            $nationalHealthBillingRepository = new NationalHealthBillingRepository();
            $nationalHealthBillings = $nationalHealthBillingRepository->get($facilityId, $facilityUserIds, $year, $month);
        } catch (Exception $e) {
            abort(422, 'サービス実績の合計の取得に失敗しました。');
        }
        if ($nationalHealthBillings === null) {
            abort(422, 'サービス実績が見つかりません。');
        }

        // 国保連請求csvクラスを作成する。
        $nationalHealthBillingCsv = new NationalHealthBillingCsv(
            $basicRemarks,
            $facility,
            $facilityUserCareRecords,
            $facilityUserPublicExpenseRecord,
            $facilityUsers,
            $facilityUserServiceRecords,
            $injuriesSickness,
            $invoiceFlg,
            $nationalHealthBillings,
            $year,
            $month
        );

        // csvファイルに書き込むテキストデータを作成する。
        $records = $nationalHealthBillingCsv->getRecords();
        $csvTxtData = '';
        for ($i = 0, $cnt = count($records); $i < $cnt; $i++) {
            $records[$i] = array_map(function ($value) {
                // TODO: 値がない場合はブランクになるというのはビジネスドメインの知識。
                // ケアデイジーとしてはブランクとダブルクォーテーションで出力する仕様で統一する。
                return in_array($value, [null, ''], true) ? '' : '"'.$value.'"';
            }, $records[$i]);
            $csvTxtData .= implode(',', $records[$i])."\r\n";
        }
        // SJISに変換する。
        mb_convert_variables('SJIS', 'UTF-8', $csvTxtData);

        return $csvTxtData;
    }

    /**
     * 事業所IDから国保連請求のcsvデータを返す
     * @param array $params key: facility_id, month, year
     * @return string
     */
    public function getCsvDataWithFacilityId(array $params) : string
    {
        // 事業所IDに紐づく施設利用者のIDのリスト(請求対象のみ)を取得する
        $faciliyUsers = ServiceResult::date($params['year'], $params['month'])
            ->where('approval', Consts::VALID)
            ->where('facility_id', $params['facility_id'])
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->select('facility_user_id')
            ->orderBy('facility_user_id', 'asc')
            ->get()
            ->toArray();
        $facilityUserIds = array_column($faciliyUsers, 'facility_user_id');
        $params['facility_user_ids'] = array_values(array_unique($facilityUserIds));

        return $this->getCsvData($params['facility_id'], $params['facility_user_ids'], 0, $params['year'], $params['month']);
    }

    /**
     * 施設利用者IDのリストから国保連請求のcsvデータを返す
     * @param array array $facilityUserIds
     * @param int $invoiceFlg
     * @param int $year
     * @param int $month
     * @return string
     */
    public function getCsvDataWithFacilityUserIds(array $facilityUserIds, int $invoiceFlg, int $year, int $month): string
    {
        // 施設利用者IDのリストの内、請求対象の施設利用者のみを抽出する
        // また抽出した施設利用者に紐づく事業所のIDを取得する
        $faciliyUsers = ServiceResult::date($year, $month)
            ->where('approval', Consts::VALID)
            ->whereIn('facility_user_id', $facilityUserIds)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->select('facility_id', 'facility_user_id')
            ->orderBy('facility_user_id', 'asc')
            ->get()
            ->toArray();
        // TODO: 引数で渡されたものと区別させる。
        $facilityUserIds = array_column($faciliyUsers, 'facility_user_id');
        $facilityUserIds = array_values(array_unique($facilityUserIds));

        // GHでは事業所は一つのみが想定される。
        $facilityService = new FacilityService();
        $facilities = $facilityService->getRelatedData();
        $facilityIds = array_column($facilities, 'facility_id');
        $facilityId = $facilityIds[0];

        return $this->getCsvData($facilityId, $facilityUserIds, $invoiceFlg, $year, $month);
    }
}
