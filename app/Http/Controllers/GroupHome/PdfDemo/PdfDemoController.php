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
use App\Models\ServiceCode;
use App\Models\ServiceResult;
use App\Models\ServiceType;
use App\Utility\JapaneseImperialYear;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use PDF;

/**
 * @author yhamada
 */
class PdfDemoController extends Controller
{
  /**
   * todo フォームリクエストに換装
   * @param Request $request key:facility_id,facility_user_id,month,year
   * @return Response
   */
    public function index(OutputPdfRequest $request)
    {
        $month = $request['month'];
        $year = $request['year'];
        $facilityID = $request->facility_id;
        $facilityUserID = $request->facility_user_id;

      // 必要なパラメーターを作成する
        $param = [
        'benefit' => [
        'facility_user_id' => [$facilityUserID],
        'month' => $month,
        'year' => $year,
        ],
        'benefit_status' => [
        'facility_user_id' => [$facilityUserID],
        'month' => $month,
        'year' => $year,
        ],
        'facility' => [
        'clm_list' => ['insurer_no','location','facility_name_kanji','facility_number','phone_number','postal_code'],
        'facility_id_list' => [$facilityID],
        ],
        'facility_user' => [
        'clm' => ['after_out_status_id','before_in_status_id','birthday','end_date','first_name','first_name_kana',
          'gender','insurer_no','insured_no','last_name','last_name_kana','start_date','facility_user_id'
        ],
        'facility_user_id_list' => [$facilityUserID],
        ],
        'user_care_information' => [
        'clm_list' => ['care_level_id','care_period_start','care_period_end','facility_user_id'], // todo care_levelは変数名が変わる
        'facility_user_id_list' => [$facilityUserID],
        'month' => $month,
        'year' => $year,
        ],
        'user_public_expense_information' => [
        'clm_list' => ['bearer_number','recipient_number'],
        'facility_user_id' => $facilityUserID,
        'month' => $month,
        'year' => $year,
        ],
        'stay_out_managements' => [
        'facility_user_id' => [$facilityUserID],
        'month' => $month,
        'year' => $year,
        ],
        'public_spending' => [
        'facility_user_id' => $facilityUserID,
        'month' => $month,
        'year' => $year,
        ],
        ];

        $upeiData = $this->getPublicNumber($param['user_public_expense_information']);

        $fuService = new FacilityUserService();
        $fuData = $fuService->getData($param['facility_user']);
        $fuData = $fuData[0];

        $uciService = new UserCareInformationService();
        $uciData = $uciService->get($param['user_care_information']);
        if ($uciData) {
            $uciData = $uciData[0];
        } else {
            $uciData['care_level_id'] = "";
            $uciData['facility_user_id'] = "";
        }

        $bsData = $this->getBenefitStatus($param['benefit_status']);
      //実績登録が無い場合の仮の処理
      // 未承認の場合は白紙ページを表示する。
        if (!$bsData) {
            $bsData[0]['service_item_name'] = "";
            $bsData[0]['service_code'] = "";
            $bsData[0]['unit_number'] = "";
            $bsData[0]['service_count_date'] = "";
            $bsData[0]['service_unit_amount'] = "";
            $bsData[0]['service_type_code'] = "";
            return;
        }

        foreach ($bsData as $value) {
            $serviceTypeCodes[] = $value['service_type_code'];
        }
        $serviceTypeCode = implode(',', array_unique($serviceTypeCodes));

        $bbData = $this->getBenefitBilling($param['benefit'], $serviceTypeCode, $uciData['care_level_id']);
      //実績登録が無い場合の仮の処理
        if ($bbData) {
            $bbData = $bbData[0];
        } else {
            $bbData['service_unit_amount'] = "";
            $bbData['service_unit_amount'] = "";
            $bbData['unit_price'] = "";
            $bbData['benefit_rate'] = "";
            $bbData['insurance_benefit'] = "";
            $bbData['part_payment'] = "";
            $bbData['classification_support_limit_in_range'] = "";
            $bbData['classification_support_benefit_unit'] = "";
        }

        $fService = new FacilityService();
        $fData = $fService->getData($param['facility']);
        $fData = $fData[0];
        $fData['facility_number'] = sprintf('%010d', $fData['facility_number']);

        $sioData = $this->getStayInOut($param['stay_out_managements']);

        $bspData = $this->getBenefitStatusPublic($param['benefit_status']);
      //実績登録が無い場合の仮の処理
        if (!$bspData) {
            $bspData[0]['public_spending_count'] = "";
            $bspData[0]['public_spending_unit_number'] = "";
        }

        $psData = $this->getPublicSpending($param['public_spending']);
      //実績登録が無い場合の仮の処理
        if ($psData) {
            $psData = $psData[0];
        } else {
            $psData['public_expenditure_unit'] = "";
            $psData['public_benefit_rate'] = "";
            $psData['public_spending_amount'] = "";
            $psData['public_payment'] = "";
            $psData['public_spending_count'] = "";
        }

      // 和暦に
        $fuData['birthday'] = JapaneseImperialYear::get($fuData['birthday']);
        if (isset($fuData['end_date'])) {
            $fuData['end_date'] = JapaneseImperialYear::get($fuData['end_date']);
        } else {
            $fuData['end_date']['year'] = '';
            $fuData['end_date']['month'] = '';
            $fuData['end_date']['day'] = '';
        };
        $fuData['start_date'] = JapaneseImperialYear::get($fuData['start_date']);
        if ($uciData['care_level_id']) {
            $uciData['care_period_end'] = JapaneseImperialYear::get($uciData['care_period_end']);
            $uciData['care_period_start'] = JapaneseImperialYear::get($uciData['care_period_start']);
        } else {
            $uciData['care_period_start']['name'] = "";
            $uciData['care_period_start']['year'] = "";
            $uciData['care_period_start']['month'] = "";
            $uciData['care_period_start']['day'] = "";
            $uciData['care_period_end']['name'] = "";
            $uciData['care_period_end']['year'] = "";
            $uciData['care_period_end']['month'] = "";
            $uciData['care_period_end']['day'] = "";
        }

        $bschunk = array_chunk($bsData, 11);
        $bspchunk = array_chunk($bspData, 11);

        $data = [
        'data' => [
        'benefit_billing' => $bbData,
        'benefit_status' => $bschunk,
        'facility' => $fData,
        'facility_user' => $fuData,
        'japanese_imperial_year' => JapaneseImperialYear::get("${year}-${month}-01"),
        'user_care_information' => $uciData,
        'user_public_expense_information' => $upeiData,
        'stayInOutDays' => $sioData,
        'public_spending' => $psData,
        'benefit_status_public' => $bspchunk,
        'year_month' => $year.$month,
        'service_type_code' => $serviceTypeCode,
        ]
        ];

        // // TODO: 種別55一時実装
        // if(true){
        //     $data['data']['benefit_status'] = array_chunk($data['data']['benefit_status'][0], 4);
        //     $data['data']['benefit_status_public'] = array_chunk($data['data']['benefit_status_public'][0], 4);
        //     return view('group_home.pdf_demo.format_9_2', $data);
        // }
        
        // ダウンロードボタンで保存されるファイル名
        $fileName = 'download.pdf';
        
        if ($serviceTypeCode == ServiceType::SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE
            || $serviceTypeCode == ServiceType::SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTION_SPECIFIED_FACILITY_RESIDENT_CARE
            || $serviceTypeCode == ServiceType::SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE
        ) {
            // return view('group_home.pdf_demo.pdf_yoshiki_6-x', $data);
            return PDF::loadView('group_home.pdf_demo.pdf_yoshiki_6-x', $data)
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path(). '/css/group_home/pdf_demo/pdf_demo.css')
            ->setPaper('A4')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->inline($fileName);
        }
        
        return PDF::loadView('group_home.pdf_demo.pdf_demo', $data)
        ->setOption('encoding', 'utf-8')
        ->setOption('user-style-sheet', public_path(). '/css/group_home/pdf_demo/pdf_demo.css')
        ->setPaper('A4')
        ->setOption('margin-top', 0)
        ->setOption('margin-bottom', 0)
        ->setOption('margin-left', 0)
        ->setOption('margin-right', 0)
        ->inline($fileName);
    }

  //公費番号
    public function getPublicNumber($param) : array {
        $year = $param['year'];
        $month = $param['month'];
        $targetDate = "${year}-${month}-01";
        $facilityUser = $param['facility_user_id'];
        $lastDate = date('Y-m-d', strtotime('last day of ' . "${year}-${month}"));

        $sql = <<<SQL
    SELECT
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
      AND upei.facility_user_id = ?
    ORDER BY mps.priority
    SQL;

        $getPublicNum = collect(\DB::select($sql, [$lastDate,$targetDate,$facilityUser]))->toArray();

        if ($getPublicNum) {
            $publicNum['bearer_number'] = $getPublicNum[0]->bearer_number;
            $publicNum['recipient_number'] = $getPublicNum[0]->recipient_number;
        } else {
            $publicNum['bearer_number'] = "";
            $publicNum['recipient_number'] = "";
        }
        return $publicNum;
    }

  //外泊
    public function getStayInOut($params){
        $year = $params['year'];
        $month = $params['month'];
        $targetDate = new CarbonImmutable("${year}-${month}-1");
        $targetYM = $targetDate->format('Ym');

        $facilityUsers = FacilityUser::where('facility_user_id', $params['facility_user_id'])
            ->select('death_date', 'end_date', 'start_date')
            ->get()
            ->toArray();

      // 外泊日数
        $actualDaysService = new ActualDaysService();
        $actualDays = $actualDaysService->get([
        'death_date' => $facilityUsers[0]['death_date'],
        'end_date' => $facilityUsers[0]['end_date'],
        'start_date' => $facilityUsers[0]['start_date'],
        'facility_user_id' => $params['facility_user_id'][0],
        'target_ym' => $targetYM
        ]);
        $stayInOutDays['stayOut'] = count($actualDays['stay_out_days']) ? sprintf('%02d', count($actualDays['stay_out_days'])) : "0";

      // 入居実日数
        $stayInOutDays['actualDays'] = sprintf('%02d', $actualDays['actual_day_cnt']);

        return $stayInOutDays;
    }

  //給付費明細の公費部分
    public function getBenefitStatusPublic($param) : array {
        $year = $param['year'];
        $month = $param['month'];

        return ServiceResult::date($year, $month)
            ->whereIn('calc_kind', [
                ServiceResult::CALC_KIND_INDIVIDUAL,
                ServiceResult::CALC_KIND_OFFICE,
                ServiceResult::CALC_KIND_SPECIAL
            ])
            ->where('approval', 1)
            ->whereIn('facility_user_id', $param['facility_user_id'])
            ->select('public_spending_count', 'public_expenditure_unit')
            ->get()
            ->toArray();
    }

  //請求額集計欄の合計
    public function getPublicSpending($param) : array {
        $year = $param['year'];
        $month = $param['month'];
        $facilityUserID = $param['facility_user_id'];

        $publicSpending = ServiceResult::
        date($year, $month)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->where('approval', Consts::VALID)
            ->where('facility_user_id', $facilityUserID)
            ->select(
                'public_expenditure_unit',
                'public_benefit_rate',
                'public_spending_amount',
                'public_payment',
                'public_spending_unit_number',
                'classification_support_limit_over',
            )
            ->get()
            ->toArray();

        // 種別33用カラム作成
        // 給付単位数(公費)
        $type33Items['public_benefit_unit'] = null; //初期化
        $type33Items['public_benefit_unit'] = $publicSpending[0]['public_expenditure_unit'] - $publicSpending[0]['classification_support_limit_over'];
        if ($type33Items['public_benefit_unit'] == 0) {
            $type33Items['public_benefit_unit'] = null;
        }
        $publicSpending[0] = array_merge($publicSpending[0], $type33Items);

        return $publicSpending;
    }

  /**
   * 請求額集計欄 保険分表示情報
   * 給付費明細欄 公費対象単位数(合計)
   */
    public function getBenefitBilling($param, $serviceTypeCode, $careLevel) : array {
        $benefitBilling = ServiceResult::date($param['year'], $param['month'])
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->where('approval', Consts::VALID)
            ->whereIn('facility_user_id', $param['facility_user_id'])
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
                'classification_support_limit_in_range',
                'classification_support_limit_over',
            )
            ->get()
            ->toArray();

        $pdfDemoService = new PdfDemoService();
        $type33Items = $pdfDemoService->getType33Units($param['facility_user_id'], $benefitBilling[0], $careLevel, $param['year'], $param['month'], $serviceTypeCode);

      // 種別33用カラム作成
      // 給付単位数
        $benefitUnit['benefit_unit'] = null;
        $benefitUnit['benefit_unit'] = $benefitBilling[0]['service_unit_amount'] - $benefitBilling[0]['classification_support_limit_over'];

        $benefitBilling[0] = array_merge($benefitBilling[0], $type33Items, $benefitUnit);

        return $benefitBilling;
    }

  /**
   * 給付費明細欄 表示情報
   */
    public function getBenefitStatus($param) : array
    {
        $year = $param['year'];
        $month = $param['month'];

        $serviceResult = ServiceResult::date($year, $month)
            ->whereIn('calc_kind', [
                ServiceResult::CALC_KIND_INDIVIDUAL,
                ServiceResult::CALC_KIND_OFFICE,
                ServiceResult::CALC_KIND_SPECIAL
            ])
            ->where('approval', Consts::VALID)
            ->whereIn('facility_user_id', $param['facility_user_id'])
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
        }
        return $serviceResult;
    }
}
