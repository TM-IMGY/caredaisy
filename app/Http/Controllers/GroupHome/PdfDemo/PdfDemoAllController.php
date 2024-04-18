<?php

namespace App\Http\Controllers\GroupHome\PdfDemo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\OutputPdfRequest;
use App\Lib\Common\Consts;
use App\Service\GroupHome\ActualDaysService;
use App\Service\GroupHome\FacilityService;
use App\Service\GroupHome\FacilityUserService;
use App\Service\GroupHome\PdfDemoService;
use App\Service\GroupHome\UserCareInformationService;
use App\Models\FacilityUser;
use App\Models\PublicSpending;
use App\Models\ServiceCode;
use App\Models\ServiceResult;
use App\Models\ServiceType;
use App\Utility\JapaneseImperialYear;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use PDF;

/**
 * @author eikeda
 */
class PdfDemoAllController extends Controller
{
  /**
   * @param Request $request key:facility_id,month,year
   * @return Response
   */
    public function index(OutputPdfRequest $request)
    {
        $month = $request['month'];
        $year = $request['year'];
        $facilityID = $request->facility_id;
        $facilityUserIDs = $request->facility_user_ids;

        $param = [
        'facilityid_month_year' => ['facility_user_id' => $facilityUserIDs, 'facility_id' => $facilityID, 'month' => $month, 'year' => $year],
        'facility' => [
        'clm_list' => ['insurer_no','facility_number','facility_name_kanji','location','phone_number','postal_code'],
        'facility_id_list' => [$facilityID]
        ],

        ////明細
        'user_public_expense_information' => [
        'clm_list' => ['bearer_number','recipient_number','facility_user_id'],
        'facility_user_id' => $facilityUserIDs,
        'month' => $month,
        'year' => $year,
        ],
        'facility_user' => [
        'clm' => ['after_out_status_id','before_in_status_id','birthday','end_date','first_name','first_name_kana',
          'gender','insurer_no','insured_no','last_name','last_name_kana','start_date','facility_user_id'
        ],
        'facility_user_id_list' => $facilityUserIDs,
        ],
        'user_care_information' => [
        'clm_list' => ['care_level_id','care_period_start','care_period_end','facility_user_id'],
        'facility_user_id_list' => $facilityUserIDs,
        'month' => $month,
        'year' => $year,
        ],
        'user_month_year' => ['facility_user_id' => $facilityUserIDs, 'month' => $month, 'year' => $year],
        ];

      /////////請求書////////
        $fService = new FacilityService();
        $fData = $fService->getData($param['facility']);
        $fData = $fData[0];
        $fData['facility_number'] = sprintf('%010d', $fData['facility_number']);
        $ibData = $this->getInsuranceBilling($param['facilityid_month_year']);
        $psData = $this->getPublicSpending($param['facilityid_month_year']);

      // システム時刻
        $systemTimestamp = JapaneseImperialYear::get(date("Y/m/d"));

      ////////明細書////////
      //公費番号number
        $upeiData = $this->getPublicNumber($param['user_public_expense_information']);
      //利用者情報
        $fuService = new FacilityUserService();
        $fuData = $fuService->getData($param['facility_user']);
      //介護度
        $uciService = new UserCareInformationService();
        $uciData = $uciService->get($param['user_care_information']);
      //実績登録が無い場合の仮の処理
        $uciUser = array_column($uciData, 'facility_user_id');
        $s = 0;
        for ($i = 0; $i < count($fuData); ++$i) {
            if (in_array($fuData[$i]['facility_user_id'], $uciUser)) {
                $uciAllData[$i] = $uciData[$s];
                ++$s;
            } else {
                $uciAllData[$i]['care_level_id'] = "";
                $uciAllData[$i]['facility_user_id'] = "";
            }
        }

      //外泊入居日数
        $siodData = $this->getStayInOutDays($param['user_month_year']);

      //給付費明細欄
        $bsData = $this->getBenefitStatus($param['user_month_year']);
      //請求額集計
        $btData = $this->getBillingTotal($param['user_month_year'], $uciAllData);

      // 和暦に
        for ($i = 0; $i < count($fuData); ++$i) {
            $fuData[$i]['birthday'] = JapaneseImperialYear::get($fuData[$i]['birthday']);

            if (isset($fuData[$i]['end_date'])) {
                $fuData[$i]['end_date'] = JapaneseImperialYear::get($fuData[$i]['end_date']);
            } else {
                $fuData[$i]['end_date']['year'] = '';
                $fuData[$i]['end_date']['month'] = '';
                $fuData[$i]['end_date']['day'] = '';
            };
            $fuData[$i]['start_date'] = JapaneseImperialYear::get($fuData[$i]['start_date']);
            if ($uciAllData[$i]['care_level_id'] != "") {
                $uciAllData[$i]['care_period_end'] = JapaneseImperialYear::get($uciAllData[$i]['care_period_end']);
                $uciAllData[$i]['care_period_start'] = JapaneseImperialYear::get($uciAllData[$i]['care_period_start']);
            } else {
                $uciAllData[$i]['care_period_start']['name'] = "";
                $uciAllData[$i]['care_period_start']['year'] = "";
                $uciAllData[$i]['care_period_start']['month'] = "";
                $uciAllData[$i]['care_period_start']['day'] = "";
                $uciAllData[$i]['care_period_end']['name'] = "";
                $uciAllData[$i]['care_period_end']['year'] = "";
                $uciAllData[$i]['care_period_end']['month'] = "";
                $uciAllData[$i]['care_period_end']['day'] = "";
            }
        }

      //並び替え
        $lastName = array_column($fuData, 'last_name_kana');
        $firstName = array_column($fuData, 'first_name_kana');
        array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $fuData);

        for ($i = 0; $i < count($bsData); ++$i) {
            $bsUserList[] = $bsData[$i][0]['facility_user_id'];
        }

        for ($i = 0; $i < count($fuData); ++$i) {
            $userId = $fuData[$i]['facility_user_id'];

            $upeiKey = array_search($userId, array_column($upeiData, 'facility_user_id'));
            $uciKey = array_search($userId, array_column($uciAllData, 'facility_user_id'));
            $siodKey = array_search($userId, array_column($siodData, 'facility_user_id'));
            $bsKey = array_search($userId, $bsUserList);
            $btKey = array_search($userId, array_column($btData, 'facility_user_id'));

            $upeiDataSort[$i] = $upeiData[$upeiKey];
            $uciDataSort[$i] = $uciAllData[$uciKey];
            $siodDataSort[$i] = $siodData[$siodKey];
            $bsDataSort[$i] = array_chunk($bsData[$bsKey], 11);
            $btDataSort[$i] = $btData[$btKey];
        }

        // ダウンロードボタンで保存されるファイル名
        $fileName = 'download.pdf';

        return PDF::loadView('group_home.pdf_demo.pdf_demo_all', [
            'data' => [
            //請求書
            'facility' => $fData,
            'insurance_billing' => $ibData,
            'japanese_imperial_year' => JapaneseImperialYear::get("${year}-${month}-01"),
            'system_timestamp' => $systemTimestamp,
            'public_spending' => $psData,
            'year_month' => $year.$month,
            //明細書
            'user_public_expense_information' => $upeiDataSort,
            'facility_user' => $fuData,
            'user_care_information' => $uciDataSort,
            'stayInOutDays' => $siodDataSort,
            'benefit_status' => $bsDataSort,
            'billing_total' => $btDataSort
            ]])
        ->setOption('encoding', 'utf-8')
        ->setOption('user-style-sheet', public_path(). '/css/group_home/pdf_demo/pdf_demo_all.css')
        ->setPaper('A4')
        ->setOption('margin-top', 0)
        ->setOption('margin-bottom', 0)
        ->setOption('margin-left', 0)
        ->setOption('margin-right', 0)
        ->inline($fileName);
    }

  ////請求書////
    public function getInsuranceBilling($param) : array {
        $year = $param['year'];
        $month = $param['month'];
        $facilityID = $param['facility_id'];
        $facilityUserID = $param['facility_user_id'];

        $serviceResultList = ServiceResult::
        date($year, $month)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->where('approval', Consts::VALID)
            ->where('facility_id', $facilityID)
            ->whereIn('facility_user_id', $facilityUserID)
            ->select(
                'insurance_benefit',
                'part_payment',
                'service_unit_amount',
                'total_cost',
                'public_spending_amount',
                'public_payment'
            )
            ->get()
            ->toArray();

      // 合計値を算出
        $insuranceBenefit = array_sum(array_column($serviceResultList, 'insurance_benefit'));
        $partPayment = array_sum(array_column($serviceResultList, 'part_payment'));
        $publicPayment = array_sum(array_column($serviceResultList, 'public_payment'));
        $totalPayment = $partPayment + $publicPayment;
        $serviceUnitAmount = array_sum(array_column($serviceResultList, 'service_unit_amount'));
        $totalCost = array_sum(array_column($serviceResultList, 'total_cost'));
        $publicSpendingAmount = array_sum(array_column($serviceResultList, 'public_spending_amount'));
      // 件数を算出
        $cnt = count($serviceResultList);

        return [
        'cnt' => $cnt ? $cnt : "",
        'part_payment' => $cnt ? number_format($totalPayment) : "",
        'service_unit_amount' => $cnt ? number_format($serviceUnitAmount) : "",
        'total_cost' => $cnt ? number_format($totalCost) : "",
        'insurance_benefit' => $cnt ? number_format($insuranceBenefit) : "",
        'public_spending_amount' => $cnt ? number_format($publicSpendingAmount) : "",
        'particular_cnt' => $cnt ? 0 : "",
        'particular_total_cost' => $cnt ? 0 : "",
        'particular_part_payment' => $cnt ? 0 : "",
        'particular_public_spending_amount' => $cnt ? 0 : "",
        'particular_insurance_benefit' => $cnt ? 0 : ""
        ];
    }

    public function getPublicSpending($param) : array {
        $year = $param['year'];
        $month = $param['month'];
        $targetDate = "${year}-${month}-01";
        $facilityID = $param['facility_id'];
        $facilityUserID = $param['facility_user_id'];
        $lastDate = date('Y-m-d', strtotime('last day of ' . "${year}-${month}"));
        $inClause = substr(str_repeat(', ?', count($facilityUserID)), 1);
        $calc_kind = ServiceResult::CALC_KIND_TOTAL;
        $approval = Consts::VALID;

        $sql = <<<SQL
    SELECT
        upei.facility_user_id,
        mps.legal_number,
        sr.public_expenditure_unit,
        sr.public_payment,
        sr.public_spending_amount,
        sr.total_cost
    FROM
        i_user_public_expense_informations upei
    JOIN
        m_public_spendings mps
      ON
        LEFT(upei.bearer_number, 2) = mps.legal_number
    JOIN
        i_service_results sr
      ON
        upei.facility_user_id = sr.facility_user_id
    WHERE upei.effective_start_date <= ?
      AND upei.expiry_date >= ?
      AND sr.calc_kind = {$calc_kind}
      AND sr.approval = {$approval}
      AND sr.facility_id = ?
      AND sr.target_date = ?
      AND sr.facility_user_id IN ({$inClause})
    ORDER BY mps.priority
    SQL;

        $values = array_merge([$lastDate], [$targetDate], [$facilityID], [$targetDate], $facilityUserID);
        $publicSpending = array_values(collect(\DB::select($sql, $values))->groupBy('facility_user_id')->toArray());

        $publicSpending12 = array();
        $publicSpending25 = array();
        $publicSpending81 = array();
        for ($i = 0; $i < count($publicSpending); ++$i) {
            if ($publicSpending[$i][0]->legal_number == PublicSpending::LEGAL_NUMBER_LIFE_ASSISTANCE) {
                $publicSpending12[] = $publicSpending[$i][0];
            } elseif ($publicSpending[$i][0]->legal_number == PublicSpending::LEGAL_NUMBER_JAPANESE_LEFT_IN_CHINA) {
                $publicSpending25[] = $publicSpending[$i][0];
            } elseif ($publicSpending[$i][0]->legal_number == PublicSpending::LEGAL_NUMBER_ATOMIC_BOMB_SUBSIDIZE) {
                $publicSpending81[] = $publicSpending[$i][0];
            }
        }

        $publicExpenditureUnit12 = array_sum(array_column($publicSpending12, 'public_expenditure_unit'));
        $publicPayment12 = array_sum(array_column($publicSpending12, 'total_cost'));
        $publicSpendingAmount12 = array_sum(array_column($publicSpending12, 'public_spending_amount'));
        $cnt12 = count($publicSpending12);

        $publicExpenditureUnit25 = array_sum(array_column($publicSpending25, 'public_expenditure_unit'));
        $publicPayment25 = array_sum(array_column($publicSpending25, 'total_cost'));
        $publicSpendingAmount25 = array_sum(array_column($publicSpending25, 'public_spending_amount'));
        $cnt25 = count($publicSpending25);

        $publicExpenditureUnit81 = array_sum(array_column($publicSpending81, 'public_expenditure_unit'));
        $publicPayment81 = array_sum(array_column($publicSpending81, 'total_cost'));
        $publicSpendingAmount81 = array_sum(array_column($publicSpending81, 'public_spending_amount'));
        $cnt81 = count($publicSpending81);

        $publicSpendingAmountTotal = $publicSpendingAmount12 + $publicSpendingAmount25 + $publicSpendingAmount81;

        return [
        'public_spending_cnt_12' => $cnt12 ? $cnt12 : "",
        'public_expenditure_unit_12' => $cnt12 ? number_format($publicExpenditureUnit12) : "",
        'public_payment_12' => $cnt12 ? number_format($publicPayment12) : "",
        'public_spending_amount_12' => $cnt12 ? number_format($publicSpendingAmount12) : "",
        'public_particular_cnt_12' => $cnt12 ? 0 : "",
        'public_particular_total_cost_12' => $cnt12 ? 0 : "",
        'public_particular_spending_amount_12' => $cnt12 ? 0 : "",
        'public_spending_cnt_25' => $cnt25 ? $cnt25 : "",
        'public_expenditure_unit_25' => $cnt25 ? number_format($publicExpenditureUnit25) : "",
        'public_payment_25' => $cnt25 ? number_format($publicPayment25) : "",
        'public_spending_amount_25' => $cnt25 ? number_format($publicSpendingAmount25) : "",
        'public_particular_cnt_25' => $cnt25 ? 0 : "",
        'public_particular_total_cost_25' => $cnt25 ? 0 : "",
        'public_particular_spending_amount_25' => $cnt25 ? 0 : "",
        'public_spending_cnt_81' => $cnt81 ? $cnt81 : "",
        'public_expenditure_unit_81' => $cnt81 ? number_format($publicExpenditureUnit81) : "",
        'public_payment_81' => $cnt81 ? number_format($publicPayment81) : "",
        'public_spending_amount_81' => $cnt81 ? number_format($publicSpendingAmount81) : "",
        'public_particular_cnt_81' => $cnt81 ? 0 : "",
        'public_particular_total_cost_81' => $cnt81 ? 0 : "",
        'public_particular_spending_amount_81' => $cnt81 ? 0 : "",
        'public_spending_amount_total' => $publicSpending ? number_format($publicSpendingAmountTotal) : "",
        'public_particular_spending_amount_total' => $publicSpending ? 0 : ""
        ];
    }

  ////明細書////
  //公費番号
    public function getPublicNumber($param) : array {
        $year = $param['year'];
        $month = $param['month'];
        $targetDate = "${year}-${month}-01";
        $facilityUserId = $param['facility_user_id'];
        $lastDate = date('Y-m-d', strtotime('last day of ' . "${year}-${month}"));

        $sql = <<<SQL
    SELECT
        upei.facility_user_id,
        upei.bearer_number,
        upei.recipient_number
    FROM
        i_user_public_expense_informations upei
    JOIN
        m_public_spendings mps
      ON
        LEFT(upei.bearer_number, 2) = mps.legal_number
    WHERE upei.effective_start_date <= ?
      AND upei.expiry_date >= ?
    ORDER BY mps.priority
    SQL;

        $publicNum = collect(\DB::select($sql, [$lastDate,$targetDate]));
        $group = $publicNum->groupBy('facility_user_id')->toArray();
        $userId = $publicNum->pluck('facility_user_id')->toArray();

        $publicNumList = array();
        for ($i = 0; $i < count($facilityUserId); ++$i) {
            if (in_array($facilityUserId[$i], $userId)) {
                $publicNumList[$i]['facility_user_id'] = $group[$facilityUserId[$i]][0]->facility_user_id;
                $publicNumList[$i]['bearer_number'] = $group[$facilityUserId[$i]][0]->bearer_number;
                $publicNumList[$i]['recipient_number'] = $group[$facilityUserId[$i]][0]->recipient_number;
            } else {
                $publicNumList[$i]['facility_user_id'] = $facilityUserId[$i];
                $publicNumList[$i]['bearer_number'] = "";
                $publicNumList[$i]['recipient_number'] = "";
            }
        }
        return $publicNumList;
    }

  //外泊
    public function getStayInOutDays($params){
        $year = $params['year'];
        $month = $params['month'];
        $targetDate = new CarbonImmutable("${year}-${month}-1");
        $targetYM = $targetDate->format('Ym');

        $facilityUsers = FacilityUser::whereIn('facility_user_id', $params['facility_user_id'])
            ->select('death_date', 'end_date', 'start_date', 'facility_user_id')
            ->get()
            ->toArray();

      // 外泊日数
        $actualDaysService = new ActualDaysService();
        for ($i = 0; $i < count($facilityUsers); ++$i) {
            $actualDays = $actualDaysService->get([
            'death_date' => $facilityUsers[$i]['death_date'],
            'end_date' => $facilityUsers[$i]['end_date'],
            'start_date' => $facilityUsers[$i]['start_date'],
            'facility_user_id' => $facilityUsers[$i]['facility_user_id'],
            'target_ym' => $targetYM
            ]);
            $stayInOutDays[$i]['stayOut'] = count($actualDays['stay_out_days']) ? sprintf('%02d', count($actualDays['stay_out_days'])) : " 0";
          // 入居実日数
            $stayInOutDays[$i]['actualDays'] = sprintf('%02d', $actualDays['actual_day_cnt']);
            $stayInOutDays[$i]['facility_user_id'] = $facilityUsers[$i]['facility_user_id'];
        }

        return $stayInOutDays;
    }

  //給付費明細欄
    public function getBenefitStatus($param) : array {
        $year = $param['year'];
        $month = $param['month'];
        $facilityUserID = $param['facility_user_id'];

        for ($s = 0; $s < count($facilityUserID); ++$s) {
            $serviceResult = ServiceResult::date($year, $month)
                ->whereIn('calc_kind', [
                    ServiceResult::CALC_KIND_INDIVIDUAL,
                    ServiceResult::CALC_KIND_OFFICE,
                    ServiceResult::CALC_KIND_SPECIAL
                ])
                ->where('approval', Consts::VALID)
                ->where('facility_user_id', $facilityUserID[$s])
                ->select(
                    'service_count_date',
                    'service_item_code_id',
                    'service_unit_amount',
                    'unit_number',
                    'public_spending_count',
                    'public_expenditure_unit',
                    'facility_user_id'
                )->with(['facilityUser' => function($q){
                    $q->select('facility_user_id')->selectRaw('DATE_FORMAT(death_date, "%Y%m%d") AS death_date      ');
                }, 'serviceCodeData' => function($q){
                    $q->select('service_type_code', 'service_item_code', 'service_item_code_id')->
                    where([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6140]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6142]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6143]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6144]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6120]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6125]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6126]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6127]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6137]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6138]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6139]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6140]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6124]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6125]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6126]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6127]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6137]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6138]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6139]
                    ])->
                    orWhere([
                        ['service_type_code', ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE],
                        ['service_item_code', ServiceCode::SERVICE_ITEM_CODE_6140]
                    ]);
                }])
                ->get()
                ->toArray();
            $serviceItemCodeIdList = array_column($serviceResult, 'service_item_code_id');

            $serviceCode = ServiceCode::date($year, $month)
                ->serviceCode($serviceItemCodeIdList)
                ->select('service_item_code_id', 'service_item_name', 'service_type_code', 'service_item_code')
                ->get()
                ->toArray();
            $serviceCode = array_column($serviceCode, null, 'service_item_code_id');

            for ($i = 0,$cnt = count($serviceResult); $i < $cnt; $i++) {
                $serviceItemCodeID = $serviceResult[$i]['service_item_code_id'];
                $serviceResult[$i]['service_item_name'] = $serviceCode[$serviceItemCodeID]['service_item_name'];
                $serviceResult[$i]['service_code'] = $serviceCode[$serviceItemCodeID]['service_type_code'].$serviceCode[$serviceItemCodeID]['service_item_code'];
                $serviceResult[$i]['service_type_code'] = $serviceCode[$serviceItemCodeID]['service_type_code'];
                $this->serviceTypeCode = $serviceCode[$serviceItemCodeID]['service_type_code'];
            }

        //実績登録が無い場合の仮の処理
            if (!$serviceResult) {
                $serviceResultList[$s][0]['service_item_name'] = "";
                $serviceResultList[$s][0]['service_code'] = "";
                $serviceResultList[$s][0]['unit_number'] = "";
                $serviceResultList[$s][0]['service_count_date'] = "";
                $serviceResultList[$s][0]['service_unit_amount'] = "";
                $serviceResultList[$s][0]['public_spending_count'] = "";
                $serviceResultList[$s][0]['public_spending_unit_number'] = "";
                $serviceResultList[$s][0]['facility_user_id'] = $facilityUserID[$s];
            } else {
                $serviceResultList[$s] = $serviceResult;
            }
        }
        return $serviceResultList;
    }

  //請求額集計欄
    public function getBillingTotal($param, $uciAllData) : array {
        $year = $param['year'];
        $month = $param['month'];
        $facilityUserID = $param['facility_user_id'];

        for ($i = 0; $i < count($facilityUserID); ++$i) {
            $billingTotal = ServiceResult::
            date($year, $month)
                ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
                ->where('approval', Consts::VALID)
                ->where('facility_user_id', $facilityUserID[$i])
                ->select(
                    'benefit_rate',
                    'insurance_benefit',
                    'part_payment',
                    'service_unit_amount',
                    'unit_price',
                    'public_expenditure_unit',
                    'public_benefit_rate',
                    'public_spending_amount',
                    'public_payment',
                    'facility_user_id',
                    'classification_support_limit_in_range',
                    'classification_support_limit_over',
                    'public_spending_unit_number',
                )
                ->get()
                ->toArray();

            if ($billingTotal) {
                  $pdfDemoService = new PdfDemoService();
                  $type33Items = $pdfDemoService->getType33Units($facilityUserID[$i], $billingTotal[0], $uciAllData, $year, $month);

                  // 給付単位数(保険分)
                  $type33Items['benefit_unit'] = null;
                  $type33Items['benefit_unit'] = $billingTotal[0]['service_unit_amount'] - $billingTotal[0]['classification_support_limit_over'];

                  // 給付単位数(公費分)
                  $type33Items['public_benefit_unit'] = null; //初期化
                  $type33Items['public_benefit_unit'] = $billingTotal[0]['public_expenditure_unit'] - $billingTotal[0]['classification_support_limit_over'];
                  if ($type33Items['public_benefit_unit'] == 0) {
                      $type33Items['public_benefit_unit'] = null;
                  }

                  $billingTotal[0] = array_merge($billingTotal[0], $type33Items);

                  $billingTotalList[$i] = $billingTotal[0];
            } else {
                $billingTotalList[$i]['benefit_rate'] = "";
                $billingTotalList[$i]['insurance_benefit'] = "";
                $billingTotalList[$i]['part_payment'] = "";
                $billingTotalList[$i]['service_unit_amount'] = "";
                $billingTotalList[$i]['unit_price'] = "";
                $billingTotalList[$i]['public_expenditure_unit'] = "";
                $billingTotalList[$i]['public_benefit_rate'] = "";
                $billingTotalList[$i]['public_spending_amount'] = "";
                $billingTotalList[$i]['public_payment'] = "";
                $billingTotalList[$i]['facility_user_id'] = $facilityUserID[$i];
                $billingTotalList[$i]['classification_support_limit_in_range'] = "";
                $billingTotalList[$i]['classification_support_benefit_unit'] = "";
                $billingTotalList[$i]['public_spending_count'] = "";
                $billingTotalList[$i]['benefit_unit'] = "";
                $billingTotalList[$i]['public_benefit_unit'] = "";
                $billingTotalList[$i]['classification_support_limit_units'] = "";
            }
        }
        return $billingTotalList;
    }
}
