<?php

namespace App\Service\GroupHome;

use App\Lib\Common\Consts;
use App\Models\ServiceResult;
use App\Models\ServiceType;
use App\Models\UserFacilityServiceInformation;
use App\Models\Service;
use App\Lib\NationalHealthBilling;
use App\Service\GroupHome\UsageFeesForType55CalcService;
use App\Utility\JapaneseImperialYear;
use Carbon\Carbon;

class OwnUninsuranceBillService
{
    private const COUNT_USE_FULL_MIN = 26;
    private const COUNT_PER_PAGE = 28;
    private const COUNT_INSURED_FIRST = 29;
    private const COUNT_FULL_PAGE = 30;
    private const TYPE_ID = 6;
    private const SPECIAL_MEDICAL_NAME = '特別診療費コード';

    /**
     * 連票の情報を取得
     */
    public function getLedgerSheets($facilityId, $outputMonth, $facilityUserIds, $endOfMonth)
    {
        $dbName = config('database.connections.confidential.database');
        $params = explode(",", $facilityUserIds);
        $bindings = trim(str_repeat('?,', count($params)), ',');

        $bases = self::getUserAndFacilityInformation($facilityId, $outputMonth, $endOfMonth, $dbName, $params, $bindings);

        $facilityUserIdList = $bases->pluck('facility_user_id')->toArray();

        $inClauseUr = '';
        $inClauseSr = '';
        if (count($facilityUserIdList) > 0) {
            $inClauseUr = 'ur.facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIdList)), 1) . ') AND';
            $inClauseSr = 'sr.facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIdList)), 1) . ') AND';
        }

        //サービス対象期間
        foreach ($bases as $base) {
            $startDateSubstr = mb_substr($base->start_date, 0, 7);
            $startDateReplace = str_replace('-', '', $startDateSubstr);

            $endDateSubstr = mb_substr($base->end_date, 0, 7);
            $endDateReplace = str_replace('-', '', $endDateSubstr);
            // パラメータの対象月と入所月・退所月が一致しない場合
            if ($outputMonth !== $startDateReplace || $outputMonth !== $endDateReplace) {
                // パラメータの月初と月末を和暦表示
                $base->start_date = JapaneseImperialYear::get(date('Y-m-d', strtotime("first day of" . $outputMonth)));
                $base->end_date = JapaneseImperialYear::get(date('Y-m-d', strtotime("last day of" . $outputMonth)));

                // 国保連の請求対象かのフラグを設定する。
                // TODO: 介護度情報が存在しない場合は暫定的に国保連請求の表示をする。異常系のメッセージ表示が実装されたら換装する。
                $base->can_be_billed = $base->care_level_id == null ? true : NationalHealthBilling::canBeBilled($base->care_level_id);
            }
        }

        $endOfMonth = (new Carbon($outputMonth))->format('Y-m-t');
        // 利用内訳(保険対象外)
        $useDetails = self::getBreakdownOfNonInsurance($inClauseUr, $outputMonth, $endOfMonth, $facilityUserIdList);

        // 保険請求超過分
        $insuranceExcess = collect();
        // 種別33のidを取得
        $service_type_code = ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE;

        $mServiceTypesSql = <<< SQL
          select service_type_code_id
          from m_service_types mst
          where service_type_code = {$service_type_code}
        SQL;
        // 種別33のサービスIDを取得
        $iServiceSql = <<<SQL
select id from i_services where service_type_code_id = ? and facility_id = ?
SQL;

        $serviceTypeCodeId = \DB::select($mServiceTypesSql);
        $serviceTypeCodeId = array_column($serviceTypeCodeId, 'service_type_code_id');
        $values = array_merge($serviceTypeCodeId, [$facilityId]);

        $serviceIds = \DB::select($iServiceSql, $values);
        $serviceIdList = array_column($serviceIds, 'id');
        $conditionsInService = '';
        $conditionsInFacilityUser = '';
        // 事業所が種別33を利用していた場合
        if (count($serviceIdList) > 0) {
            $conditionsInService = 'service_id IN (' . substr(str_repeat(', ?', count($serviceIdList)), 1) . ') AND';
            $conditionsInFacilityUser = 'facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIdList)), 1) . ') AND';

          // 種別33を利用している利用者idを取得する
            $iUserFacilityServiceInformationsSql = <<<SQL
select facility_user_id
from i_user_facility_service_informations
where {$conditionsInService}
{$conditionsInFacilityUser}
use_start <= ?
and use_end >= ?
SQL;
            $values = array_merge($serviceIdList, $facilityUserIdList, [$outputMonth, $endOfMonth]);
            $serviceInfo = \DB::select($iUserFacilityServiceInformationsSql, $values);

            $facilityUserIds = array_column($serviceInfo, 'facility_user_id');
            $conditionsInFacilityUser = '';
            if (count($facilityUserIds) > 0) {
                $conditionsInFacilityUser = 'facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIds)), 1) . ') AND';
            }

          // ↓↓個別の利用者請求書・領収書出力時に下記の保険請求超過分の記述がされてしまうためコメントアウト↓↓

          // 保険請求超過分の取得及び計算
//           $iServiceResultsSql = <<<SQL
// select
//   '保険請求超過分' as name,
//   facility_user_id,
//   FLOOR(classification_support_limit_over * unit_price) AS limit_over
// from i_service_results
// where {$conditionsInFacilityUser}
// facility_id = ?
// and target_date = ?
// and calc_kind = 5
// and approval = 1
// SQL;

          // $values = array_merge($facilityUserIds,[$facilityId,$outputMonth]);
          // $insuranceExcess = collect(\DB::select($iServiceResultsSql,$values));
        }

        // 利用内訳(保険対象外総額)
        $useDetailsTotalAmount = self::getBreakdownOfNonInsuranceTotal($inClauseUr, $outputMonth, $facilityUserIdList);

        // 保険対象自己負担額　内訳
        $insuredCostDetails = self::getInsuredCostDetails($inClauseSr, $facilityUserIdList, $facilityId, $outputMonth);

        //保険対象請求分
        $calc_kind_5 = ServiceResult::CALC_KIND_TOTAL;
        $approval = Consts::VALID;

        $sql = <<<SQL
SELECT
  sr.facility_user_id,
  sr.service_unit_amount,
  sr.total_cost,
  sr.part_payment,
  TRUNCATE(((100 - sr.benefit_rate) / 10), 0) AS own_payment_rate,
  sr.public_payment,
  sr.part_payment + COALESCE(sr.public_payment, 0) AS insurance_self_pay,
  sr.public_spending_amount,
  sr.insurance_benefit
FROM
  i_service_results sr
WHERE
  {$inClauseSr}
  sr.facility_id = ?
  AND sr.target_date = ?
  AND sr.calc_kind = {$calc_kind_5}
  AND sr.approval = {$approval}
SQL;

        $values = array_merge($facilityUserIdList, [$facilityId, $outputMonth]);
        $insuredClaims = collect(\DB::select($sql, $values));

        // jpg統一前は定数の参照先が異なるので注意
        return $bases->reduce(function ($collection, $base) use ($useDetails, $insuredCostDetails, $insuredClaims, $useDetailsTotalAmount, $insuranceExcess) {
            $facilityUserId = $base->facility_user_id;

            $userUseDetails = $useDetails->where('facility_user_id', $facilityUserId);
            $userInsuredCostDetails = $insuredCostDetails->where('facility_user_id', $facilityUserId);
            $userInsuredClaims = $insuredClaims->where('facility_user_id', $facilityUserId)->first();
            $userUseDetailsTotalAmount = $useDetailsTotalAmount->where('facility_user_id', $facilityUserId)->first();

            $useDetailsSplice = $userUseDetails->splice(self::COUNT_PER_PAGE);
            $useDetailsChunk = $useDetailsSplice->chunk(self::COUNT_FULL_PAGE);

            // 公費対象の場合
            if (isset($userInsuredClaims->public_payment) && $userInsuredClaims->public_payment){
              $userUseDetails->prepend($userInsuredClaims);
            }

            if ($useDetailsChunk->isNotEmpty()) {
                $insuredCostDetailsCount = self::COUNT_PER_PAGE - $useDetailsChunk->last()->count();
            } else {
                if ($userUseDetails->count() < self::COUNT_USE_FULL_MIN) {
                    $insuredCostDetailsCount = self::COUNT_PER_PAGE - $userUseDetails->count() - 2;
                } else {
                    $insuredCostDetailsCount = self::COUNT_INSURED_FIRST;
                }
            }

            $limit = $insuranceExcess->where('facility_user_id', $facilityUserId);

            $insuredCostDetailsSplice = $userInsuredCostDetails->splice($insuredCostDetailsCount);
            $insuredCostDetailsChunk = $insuredCostDetailsSplice->chunk(self::COUNT_FULL_PAGE);

            // 保険請求超過分がある場合
            $totalAmountSumLimit = null;
            if (!$limit->isEmpty() && ( $limit->pluck('limit_over')->first() != null && $limit->pluck('limit_over')->first() != 0 )) {
                $useDetailsMerge = $useDetailsChunk->prepend($limit->merge($userUseDetails));
                $limitOver = $limit->pluck('limit_over')->first();
                $totalAmount = $useDetailsTotalAmount->where('facility_user_id', $facilityUserId)->pluck('total_amount')->first();
                $totalAmountSumLimit['total_amount_sum_limit'] = $limitOver + $totalAmount;
            } else {
                $useDetailsMerge = $useDetailsChunk->prepend($userUseDetails);
            }

            $insuredCostDetailsMerge = $insuredCostDetailsChunk->prepend($userInsuredCostDetails->prepend('＜保険対象自己負担額　内訳＞'));

            if ($insuredCostDetailsCount == self::COUNT_INSURED_FIRST) {
                $detailsList = $useDetailsMerge->merge($insuredCostDetailsMerge);
            } else {
                $detailsList = $useDetailsMerge->last()->merge($insuredCostDetailsMerge->first());
                $useDetailsMerge->pop();
                $insuredCostDetailsMerge->shift();
                $detailsList = $useDetailsMerge->merge([$detailsList])->merge($insuredCostDetailsMerge);
            }

            // ご請求金額 = 保険対象自己負担額 + 自費請求分/自己負担
            $billingAmount = optional($userInsuredClaims)->insurance_self_pay + optional($userUseDetailsTotalAmount)->total_amount;

            $data = collect([
                collect($base),
                $detailsList,
                $insuredClaims->where('facility_user_id', $facilityUserId),
                $userUseDetailsTotalAmount,
                $billingAmount
            ]);

            $collection->push($data);
            return $collection;
        }, collect());
    }

    /**
     * 利用者の中に種類55を利用しているユーザーがいるかどうか
     * @param str $facilty_user_id
     * @param str $targetMonth
     */
    public function getServiceType($facilityUserIds, $targetMonth)
    {
      $userFacilityServiceInfo = new UserFacilityServiceInformation();
      $year = (new Carbon($targetMonth))->year;
      $month = (new Carbon($targetMonth))->month;
      $params = explode(",", $facilityUserIds);
      foreach ($params as $key => $id) {
        $faciltyInfo[] = $userFacilityServiceInfo->getTargetYmLatest($id, $year, $month);
      }
      $serviceIds = array_column($faciltyInfo, 'service_id');

      $serviceTypeCodeIds = Service::
        whereIn('id', $serviceIds)
        ->select('service_type_code_id')
        ->get()
        ->toArray();

      $type55Have = in_array(self::TYPE_ID, array_column($serviceTypeCodeIds, 'service_type_code_id'), true);

      return $type55Have;
    }

    /**
     * 種類55用の連票の情報を取得
     */
    public function getLedgerSheets55($facilityId, $outputMonth, $facilityUserIds, $endOfMonth)
    {
        $type55Service = new UsageFeesForType55CalcService();
        $dbName = config('database.connections.confidential.database');
        $params = explode(",", $facilityUserIds);
        $bindings = trim(str_repeat('?,', count($params)), ',');

        $bases = self::getUserAndFacilityInformation($facilityId, $outputMonth, $endOfMonth, $dbName, $params, $bindings);

        $facilityUserIdList = $bases->pluck('facility_user_id')->toArray();

        $inClauseUr = '';
        $inClauseSr = '';
        if (count($facilityUserIdList) > 0) {
            $inClauseUr = 'ur.facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIdList)), 1) . ') AND';
            $inClauseSr = 'sr.facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIdList)), 1) . ') AND';
        }

        //サービス対象期間
        foreach ($bases as $base) {
            $startDateSubstr = mb_substr($base->start_date, 0, 7);
            $startDateReplace = str_replace('-', '', $startDateSubstr);

            $endDateSubstr = mb_substr($base->end_date, 0, 7);
            $endDateReplace = str_replace('-', '', $endDateSubstr);
            // パラメータの対象月と入所月・退所月が一致しない場合
            if ($outputMonth !== $startDateReplace || $outputMonth !== $endDateReplace) {
                // パラメータの月初と月末を和暦表示
                $base->start_date = JapaneseImperialYear::get(date('Y-m-d', strtotime("first day of" . $outputMonth)));
                $base->end_date = JapaneseImperialYear::get(date('Y-m-d', strtotime("last day of" . $outputMonth)));

                // 国保連の請求対象かのフラグを設定する。
                // TODO: 介護度情報が存在しない場合は暫定的に国保連請求の表示をする。異常系のメッセージ表示が実装されたら換装する。
                $base->can_be_billed = $base->care_level_id == null ? true : NationalHealthBilling::canBeBilled($base->care_level_id);
            }
        }

        $endOfMonth = (new Carbon($outputMonth))->format('Y-m-t');

        // 利用内訳(保険対象外)
        $useDetails = self::getBreakdownOfNonInsurance($inClauseUr, $outputMonth, $endOfMonth, $facilityUserIdList);

        // 利用内訳(保険対象外総額)
        $useDetailsTotalAmount = self::getBreakdownOfNonInsuranceTotal($inClauseUr, $outputMonth, $facilityUserIdList);

        // 保険対象自己負担額　内訳
        $sql = $type55Service->getInsuredCostDetails($inClauseSr);
        $values = array_merge($facilityUserIdList, [$facilityId, $outputMonth]);
        $insuredCostDetails = collect(\DB::select($sql, $values));

        // 特別診療向け special_medical_nameがnullでなかった場合、service_item_nameをspecial_medical_nameで上書きする
        foreach ($insuredCostDetails as $key => $value) {
          if (!is_null($value->special_medical_name)) {
            $insuredCostDetails[$key]->service_item_name = $value->special_medical_name;
          }
        }

        // 負担割合
        $sql = $type55Service->getOwnPaymentRate($inClauseSr);
        $values = array_merge($facilityUserIdList, [$facilityId, $outputMonth]);
        $ownPaymentRate = \DB::select($sql, $values);
        // 保険対象請求分 サービス種類と特別診療費の合計
        $sql = $type55Service->getTotalInsuredClaims($inClauseSr);
        $values = array_merge($facilityUserIdList, [$facilityId, $outputMonth]);
        $type55Total = \DB::select($sql, $values);
        // 負担割合とサービス種類と特別診療費の合計をマージする
        $insuredClaims = collect();
        if ($type55Total && $ownPaymentRate) {
          for ($i=0; $i < count($ownPaymentRate) ; $i++) {
            $type55Total[$i]->own_payment_rate = $ownPaymentRate[$i]->own_payment_rate;
            $insuredClaims[$i] = $type55Total[$i];
          }
        }

        // 保険請求超過分
        $insuranceExcess = collect();

        // レスポンスデータを整形して返す
        return self::createResponseData($bases, $useDetails, $insuredCostDetails, $insuredClaims, $useDetailsTotalAmount, $insuranceExcess);
    }

    /**
     *
     */
    public function createResponseData(
      $bases,
      $useDetails,
      $insuredCostDetails,
      $insuredClaims,
      $useDetailsTotalAmount,
      $insuranceExcess
    ) {
        return $bases->reduce(function ($collection, $base) use ($useDetails, $insuredCostDetails, $insuredClaims, $useDetailsTotalAmount, $insuranceExcess) {
          $facilityUserId = $base->facility_user_id;

          $userUseDetails = $useDetails->where('facility_user_id', $facilityUserId);
          $userInsuredCostDetails = $insuredCostDetails->where('facility_user_id', $facilityUserId);
          $userInsuredClaims = $insuredClaims->where('facility_user_id', $facilityUserId)->first();
          $userUseDetailsTotalAmount = $useDetailsTotalAmount->where('facility_user_id', $facilityUserId)->first();

          $useDetailsSplice = $userUseDetails->splice(self::COUNT_PER_PAGE);
          $useDetailsChunk = $useDetailsSplice->chunk(self::COUNT_FULL_PAGE);

          // 公費対象の場合
          if (isset($userInsuredClaims->public_payment) && $userInsuredClaims->public_payment){
            $userUseDetails->prepend($userInsuredClaims);
          }

          if ($useDetailsChunk->isNotEmpty()) {
              $insuredCostDetailsCount = self::COUNT_PER_PAGE - $useDetailsChunk->last()->count();
          } else {
              if ($userUseDetails->count() < self::COUNT_USE_FULL_MIN) {
                  $insuredCostDetailsCount = self::COUNT_PER_PAGE - $userUseDetails->count() - 2;
              } else {
                  $insuredCostDetailsCount = self::COUNT_INSURED_FIRST;
              }
          }

          $limit = $insuranceExcess->where('facility_user_id', $facilityUserId);

          $insuredCostDetailsSplice = $userInsuredCostDetails->splice($insuredCostDetailsCount);
          $insuredCostDetailsChunk = $insuredCostDetailsSplice->chunk(self::COUNT_FULL_PAGE);

          // 保険請求超過分がある場合
          $totalAmountSumLimit = null;
          if (!$limit->isEmpty() && ( $limit->pluck('limit_over')->first() != null && $limit->pluck('limit_over')->first() != 0 )) {
              $useDetailsMerge = $useDetailsChunk->prepend($limit->merge($userUseDetails));
              $limitOver = $limit->pluck('limit_over')->first();
              $totalAmount = $useDetailsTotalAmount->where('facility_user_id', $facilityUserId)->pluck('total_amount')->first();
              $totalAmountSumLimit['total_amount_sum_limit'] = $limitOver + $totalAmount;
          } else {
              $useDetailsMerge = $useDetailsChunk->prepend($userUseDetails);
          }

          $insuredCostDetailsMerge = $insuredCostDetailsChunk->prepend($userInsuredCostDetails->prepend('＜保険対象自己負担額　内訳＞'));

          if ($insuredCostDetailsCount == self::COUNT_INSURED_FIRST) {
              $detailsList = $useDetailsMerge->merge($insuredCostDetailsMerge);
          } else {
              $detailsList = $useDetailsMerge->last()->merge($insuredCostDetailsMerge->first());
              $useDetailsMerge->pop();
              $insuredCostDetailsMerge->shift();
              $detailsList = $useDetailsMerge->merge([$detailsList])->merge($insuredCostDetailsMerge);
          }

          // ご請求金額 = 保険対象自己負担額 + 自費請求分/自己負担
          $billingAmount = optional($userInsuredClaims)->insurance_self_pay + optional($userUseDetailsTotalAmount)->total_amount;

          $data = collect([
              collect($base),
              $detailsList,
              $insuredClaims->where('facility_user_id', $facilityUserId),
              $userUseDetailsTotalAmount,
              $billingAmount
          ]);

          $collection->push($data);
          return $collection;
      }, collect());
    }

    /**
     * 帳票に表示する利用者情報と事業所情報を取得する
     */
    public function getUserAndFacilityInformation(
      $facilityId,
      $outputMonth,
      $endOfMonth,
      $dbName,
      $params,
      $bindings
    ) {
      $sql = <<<SQL
SELECT
  fu.facility_user_id,
  fu.last_name,
  fu.first_name,
  fu.last_name_kana,
  fu.first_name_kana,
  fu.start_date,
  fu.end_date,
  fu.insurer_no,
  fu.insured_no,
  f.facility_name_kanji,
  f.facility_number,
  f.postal_code AS facility_postal_code,
  f.location AS facility_location,
  f.phone_number,
  f.fax_number,
  uf.contractor_number,
  uba.name,
  uba.postal_code AS user_postal_code,
  uba.location1 AS user_location1,
  uba.location2 AS user_location2,
  uba.remarks_for_bill,
  uba.remarks_for_receipt,
  uci.care_level_id,
  ap.remarks AS account_payable_remarks,
  CONCAT(IFNULL(ap.bank,'') , '  ' , IFNULL(ap.branch,'')) AS account_payable_bank_info,
  CASE WHEN ap.type_of_account=1 THEN CONCAT('普通  ',ap.bank_account, '  ', ap.depositor)
      ELSE CONCAT('当座  ',ap.bank_account, '  ', ap.depositor)
  END AS account_payable_info
FROM
  {$dbName}.i_facility_users fu
INNER JOIN
  i_user_facility_informations uf ON uf.facility_user_id = fu.facility_user_id
INNER JOIN
  i_facilities f ON f.facility_id = uf.facility_id
LEFT JOIN
  {$dbName}.i_uninsured_billing_addresses uba
  ON uf.facility_user_id = uba.facility_user_id
  AND  f.facility_id = uba.facility_id
LEFT JOIN
  (
    SELECT distinct apg.facility_id, ap_.*
    FROM i_account_payables ap_
    LEFT JOIN i_account_payable_groupes apg
    ON ap_.id = apg.account_payable_id
  ) ap
  ON uf.facility_id = ap.facility_id
-- 最新の介護情報を結合する。
LEFT JOIN
  (
    SELECT
      uci_.facility_user_id,
      uci_.care_level_id
    FROM
      (
        SELECT distinct
          uci_.facility_user_id,
          uci_.care_level_id,
          uci_.care_period_start
        FROM
          i_user_care_informations uci_
        WHERE
          uci_.care_period_start <= ?
          AND
          uci_.care_period_end >= ?
      ) uci_
    INNER JOIN
      (
        SELECT distinct
          uci_latest_.facility_user_id,
          max(uci_latest_.care_period_start) as care_period_start
        FROM
          i_user_care_informations uci_latest_
        WHERE
          uci_latest_.care_period_start <= ?
          AND
          uci_latest_.care_period_end >= ?
        GROUP BY
          uci_latest_.facility_user_id
      ) uci_latest_
    ON
      uci_.facility_user_id = uci_latest_.facility_user_id
    WHERE
      uci_.care_period_start = uci_latest_.care_period_start
  ) uci
  ON uf.facility_user_id = uci.facility_user_id
WHERE
  f.facility_id = ? and fu.facility_user_id IN ( {$bindings} )
AND
  fu.start_date <= ? and (fu.end_date >= ? or fu.end_date is null)
ORDER BY
  fu.facility_user_id
SQL;

        array_unshift($params, $endOfMonth, $outputMonth, $endOfMonth, $outputMonth, $facilityId);
        array_push($params, $endOfMonth, $outputMonth);

        $bases = collect(\DB::select($sql, $params));
        return $bases;
    }

    /**
     * 利用内訳/保険対象外を取得する
     */
    public function getBreakdownOfNonInsurance($inClauseUr, $outputMonth, $endOfMonth, $facilityUserIdList)
    {
      $sql = <<<SQL
SELECT
  ur.facility_user_id,
  ur.unit_cost,
  ur.name,
  uih.item,
  urd.quantity,
  ur.unit_cost * urd.quantity AS total_cost
FROM
  i_uninsured_requests ur
LEFT OUTER JOIN
  i_uninsured_item_histories uih ON uih.id = ur.uninsured_item_history_id
INNER JOIN
  (
    SELECT
      uninsured_request_id,
      SUM(quantity) AS quantity
    FROM
      i_uninsured_request_details
    WHERE
      date_of_use BETWEEN ? AND ?
    GROUP BY
      uninsured_request_id
  ) urd ON urd.uninsured_request_id = ur.id
INNER JOIN
  (
    SELECT
      facility_user_id,
      approval_flag
    FROM
      i_approvals
    WHERE
      month = ?
      AND approval_type = 1
  ) ap ON ap.facility_user_id = ur.facility_user_id
WHERE
  {$inClauseUr}
  ur.month = ?
  AND ap.approval_flag = 1
ORDER BY
      ur.sort ASC
SQL;

        $values = array_merge([$outputMonth, $endOfMonth], [$outputMonth], $facilityUserIdList, [$outputMonth]);
        $useDetails = collect(\DB::select($sql, $values));
        return $useDetails;
    }

    /**
     * 利用内訳/保険対象外総額を取得する
     */
    public function getBreakdownOfNonInsuranceTotal($inClauseUr, $outputMonth, $facilityUserIdList)
    {
      $sql = <<<SQL
SELECT
  ur.facility_user_id,
  SUM(ur.unit_cost * urd.quantity) AS total_amount
FROM
  i_uninsured_requests ur
INNER JOIN
  i_uninsured_request_details urd ON urd.uninsured_request_id = ur.id
INNER JOIN
  (
    SELECT
      facility_user_id,
      approval_flag
    FROM
      i_approvals
    WHERE
      month = ?
      AND approval_type = 1
  ) ap ON ap.facility_user_id = ur.facility_user_id
WHERE
  {$inClauseUr}
  ur.month = ?
  AND ap.approval_flag = 1
GROUP BY
  ur.facility_user_id
SQL;

        $values = array_merge([$outputMonth], $facilityUserIdList, [$outputMonth]);
        $useDetailsTotalAmount = collect(\DB::select($sql, $values));
        return $useDetailsTotalAmount;
    }

    /**
     * 保険対象自己負担額　内訳を取得する
     */
    public function getInsuredCostDetails($inClauseSr, $facilityUserIdList, $facilityId, $outputMonth)
    {
      $sql = <<<SQL
SELECT
  sr.facility_user_id,
  sr.service_count_date,
  sr.unit_price,
  sc.service_item_name,
  sr.unit_number,
  sr.service_result_id
FROM
  i_service_results sr
INNER JOIN
  m_service_codes sc ON sc.service_item_code_id = sr.service_item_code_id
WHERE
  {$inClauseSr}
  sr.facility_id = ?
  AND sr.target_date = ?
  AND sr.calc_kind IN (1, 2, 4)
  AND sr.approval = 1
SQL;

        $values = array_merge($facilityUserIdList, [$facilityId, $outputMonth]);
        $insuredCostDetails = collect(\DB::select($sql, $values));
        return $insuredCostDetails;
    }

    /**
     * 保険外品目関連情報を取得する
     */
    public function getUninsuredItems($facilityUserIds, $targetMonth)
    {
        $arrFacilityUserIds = explode(',', $facilityUserIds);
        $bindFacilityUserIds = trim(str_repeat('?,', count($arrFacilityUserIds)), ',');

        $sql = <<<SQL
select *
from (
  select
    iur.id,
    iur.facility_user_id,
    iur.month,
    iur.unit_cost,
    iuih.id as uninsured_item_histories_id,
    IFNULL(iur.name, iuih.item) as uninsured_item_name
  from i_uninsured_requests iur
  left join i_uninsured_item_histories iuih on
    iur.uninsured_item_history_id = iuih.id
) iur
join (
  select
    uninsured_request_id,
    SUM(quantity) as quantity
  from i_uninsured_request_details iurd
  where quantity >= 1
  group by uninsured_request_id
) iurd on
  iur.id = iurd.uninsured_request_id
where iur.month = ?
and iur.facility_user_id in ( {$bindFacilityUserIds} )
SQL;

        $params = array_merge([$targetMonth], $arrFacilityUserIds);
        $uninsuredItems = collect(\DB::select($sql, $params));
        return $uninsuredItems;
    }
}
