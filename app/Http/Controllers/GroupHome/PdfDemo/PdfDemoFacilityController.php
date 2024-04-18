<?php

namespace App\Http\Controllers\GroupHome\PdfDemo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\OutputPdfRequest;
use App\Lib\Common\Consts;
use App\Models\PublicSpending;
use App\Models\ServiceResult;
use App\Service\GroupHome\FacilityService;
use App\Utility\JapaneseImperialYear;
use Illuminate\Http\Request;
use PDF;

/**
 * @author yhamada
 */
class PdfDemoFacilityController extends Controller
{
  /**
   * todo フォームリクエストに換装
   * @param Request $request key:facility_id,month,year
   * @return Response
   */
    public function index(OutputPdfRequest $request)
    {
        $year = $request->year;
        $month = $request->month;
        $facilityID = $request->facility_id;
        $facilityUserID = explode(',', $request->facility_user_ids[0]);

        $param = [
        'facilityid_month_year' => ['facility_user_id' => $facilityUserID, 'facility_id' => $facilityID, 'month' => $month, 'year' => $year],
        'facility' => [
        'clm_list' => ['facility_number','facility_name_kanji','location','phone_number','postal_code'],
        'facility_id_list' => [$facilityID]
        ],
        ];

        $fService = new FacilityService();
        $fData = $fService->getData($param['facility']);
        $fData = $fData[0];
        $fData['facility_number'] = sprintf('%010d', $fData['facility_number']);
        $ibData = $this->getInsuranceBilling($param['facilityid_month_year']);
        $psData = $this->getPublicSpending($param['facilityid_month_year']);

        // システム時刻
        $systemTimestamp = JapaneseImperialYear::get(date("Y/m/d"));
        // ダウンロードボタンで保存されるファイル名
        $fileName = 'download.pdf';

        return PDF::loadView('group_home.pdf_demo.pdf_demo_facility', [
            'data' => [
                'facility' => $fData,
                'insurance_billing' => $ibData,
                'japanese_imperial_year' => JapaneseImperialYear::get("${year}-${month}-01"),
                'system_timestamp' => $systemTimestamp,
                'public_spending' => $psData,
                'year_month' => $year.$month,
                ]
            ])
        ->setOption('encoding', 'utf-8')
        ->setOption('user-style-sheet', public_path(). '/css/group_home/pdf_demo/pdf_demo_facility.css')
        ->setPaper('A4')
        ->setOption('margin-top', 0)
        ->setOption('margin-bottom', 0)
        ->setOption('margin-left', 0)
        ->setOption('margin-right', 0)
        ->inline($fileName);
    }

    public function getInsuranceBilling($param) : array
    {
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
        'particular_insurance_benefit' => $cnt ? 0 : "",
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
        'public_particular_spending_amount_total' => $publicSpending ? 0 : "",
        ];
    }
}
