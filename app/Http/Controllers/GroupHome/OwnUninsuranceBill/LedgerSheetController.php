<?php

namespace App\Http\Controllers\GroupHome\OwnUninsuranceBill;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\OutputPdfRequest;
use App\Models\CareLevel;
use App\Service\GroupHome\OwnUninsuranceBillService;
use App\Utility\JapaneseImperialYear;
use Illuminate\Support\Facades\Crypt;
use PDF;

use Carbon\CarbonImmutable;

class LedgerSheetController extends Controller
{
    public function index(OutputPdfRequest $request)
    {
        $facilityUserIds = $request->get('facility_user_ids');
        $facilityId = $request->get('facility_id');
        $targetMonth = $request->get('target_month');
        $eventType = $request->get('event_type');
        $endOfMonth = $request->get('end_of_month');

        $service = new OwnUninsuranceBillService();
        $type55Have = $service->getServiceType($facilityUserIds, $targetMonth);

        // 種別55とそれ以外で処理内容が異なる
        // TODO: 帳票に関して種別55とそうでないものでjpg統一されたため、リファクタして処理を同じにして良いかを確認する。
        if ($type55Have) {
            $tmpCollect = $service->getLedgerSheets55($facilityId, $targetMonth, $facilityUserIds, $endOfMonth);
        } else {
            $tmpCollect = $service->getLedgerSheets($facilityId, $targetMonth, $facilityUserIds, $endOfMonth);
        }
        $imgBillPath = public_path('img/own_uninsurance_bill/bill.jpg');
        $imgReceiptPath = public_path('img/own_uninsurance_bill/receipt.jpg');
        $cssPath = '/css/own_uninsurance_bill/own_uninsurance_bill.css';

        // 一旦受けた内容からカナだけ復号化して、そこを軸にソートしなおす
        $tmps = [];
        $ledgerSheets = collect([]);
        foreach ($tmpCollect as $index => $tmp) {
            $fullnameKana = Crypt::decrypt($tmp[0]["last_name_kana"]) . " " . Crypt::decrypt($tmp[0]["first_name_kana"]);
            $tmps[$index] = $fullnameKana;
        }
        asort($tmps);
        foreach ($tmps as $index => $val) {
            $ledgerSheets->push($tmpCollect[$index]);
        }

        // 請求一覧PDF用処理
        if ($eventType == 'dep_usage_fee_invoice_list_pdf') {
            if ($type55Have) {
                // 介護医療院の場合はPDF出力しない
                return response('', 403);
            } else {
                return $this->outputInvoiceListPdf($request, $targetMonth, $ledgerSheets);
            }
        }

        $fileName = '自費保険外請求.pdf';
        if($eventType == 'dep_usage_fee_receipt_individual') {
            $fileName = '自費保険外領収.pdf';
        }

        //PDF発行日（和暦）
        $systemTimestamp = JapaneseImperialYear::get($request->query('issue_date', today()->format('Y/m/d')));
        //帳票対象年月
        $servicePeriod = JapaneseImperialYear::get($targetMonth);
        // $imgBillPath = public_path('img/own_uninsurance_bill/bill.jpg');
        $imgBill = base64_encode(file_get_contents($imgBillPath));
        // $imgReceiptPath = public_path('img/own_uninsurance_bill/receipt.jpg');
        $imgReceipt = base64_encode(file_get_contents($imgReceiptPath));
        // PDF出力せずにブラウザで確認するため PDF出力時はコメントアウトすること　
        // return view('group_home/own_uninsurance_bill/index', compact('targetMonth', 'ledgerSheets', 'servicePeriod', 'systemTimestamp', 'imgBill', 'imgReceipt', 'eventType'));

        if ($eventType == 'dep_usage_fee_invoice_list_csv') {
            // 請求一覧CSV出力処理
            if ($type55Have) {
                // 介護医療院の場合はCSV出力しない
                return response('', 403);
            } else {
                return $this->outputInvoiceListCsv($facilityUserIds, $targetMonth, $service, $ledgerSheets);
            }
        } else {
            return PDF::loadView('group_home/own_uninsurance_bill/index', compact('targetMonth', 'ledgerSheets', 'servicePeriod', 'systemTimestamp', 'imgBill', 'imgReceipt', 'eventType'))
                ->setOption('encoding', 'utf-8')
                ->setOption('user-style-sheet', public_path(). $cssPath)
                ->setPaper('A4')
                ->setOption('margin-top', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0)
                ->inline($fileName);
        }
    }

    /**
     * 利用料請求書一覧PDF出力処理
     */
    private function outputInvoiceListPdf($request, $targetMonth, $ledgerSheets)
    {
        // 対象年月
        $targetYM = CarbonImmutable::parse($targetMonth)->format('Ym');
        // PDFファイル名
        $fileName = '利用料請求書一覧_'.$targetYM.'.pdf';

        // PDF用imgファイル
        $imgBillListPath = public_path('img/own_uninsurance_bill/bill_list1.jpg');
        $imgBillList2Path = public_path('img/own_uninsurance_bill/bill_list2.jpg');
        $imgBillList = base64_encode(file_get_contents($imgBillListPath));
        $imgBillList2 = base64_encode(file_get_contents($imgBillList2Path));

        // PDF用CSSファイル
        $cssPath = '/css/own_uninsurance_bill/own_uninsurance_bill_list.css';

        //PDF発行日（和暦）
        $systemTimestamp = JapaneseImperialYear::get($request->query('issue_date', today()->format('Y/m/d')));
        //帳票対象年月
        $servicePeriod = JapaneseImperialYear::get($targetMonth);

        // 出力用データを作成する
        // 事業所名を設定
        $facilityName = '';
        if (
            count($ledgerSheets) > 0 &&
            count($ledgerSheets[0]) > 0 &&
            !is_null($ledgerSheets[0][0]['facility_name_kanji'])
        ) {
            $facilityName = $ledgerSheets[0][0]['facility_name_kanji'];
        }

        // 集計項目を定義
        $aggregatedTotalUnit = 0; // 保険分 単位数合計
        $aggregatedTotalAmount = 0; // 保険分 費用合計
        $aggregatedBillingInsuranceBenefit = 0; // 保険分 保険分請求額
        $aggregatedBillingPartPayment = 0; // 保険分 利用者負担額
        $aggregatedBillingPublicInsuranceBenefit = 0; // 保険分（公費） 公費分請求額
        $aggregatedBillingPublicPartPayment = 0; // 保険分（公費） 本人支払額
        $aggregatedUninsuredSelfTotal = 0; // 保険外分 利用者負担額
        $aggregatedTotalAmountSelf = 0; // 利用者負担額合計

        $details = array();
        foreach($ledgerSheets as $sheet) {
            // 利用者氏名
            $fullname = decrypt($sheet[0]['last_name']) . ' ' . decrypt($sheet[0]['first_name']);
            // 実績登録テーブルより各項目の値を取得
            $dataFlg = false;
            foreach ($sheet[2] as $wk) {
                $dataFlg = true;
                // 負担割合
                if (is_null($wk->own_payment_rate)) {
                    $benefitRate = '-';
                } else {
                    $benefitRate = $wk->own_payment_rate . '割';
                }
                // 単位数合計
                if (is_null($wk->service_unit_amount)) {
                    $totalUnit = '-';
                } else {
                    $totalUnit = number_format($wk->service_unit_amount);
                    $aggregatedTotalUnit += $wk->service_unit_amount;
                }
                // 費用合計
                if (is_null($wk->total_cost)) {
                    $totalAmount = '-';
                } else {
                    $totalAmount = number_format($wk->total_cost);
                    $aggregatedTotalAmount += $wk->total_cost;
                }
                // 保険分請求額
                if (is_null($wk->insurance_benefit) || $wk->insurance_benefit === '') {
                    $billingInsuranceBenefit = '-';
                } else {
                    $billingInsuranceBenefit = number_format($wk->insurance_benefit);
                    $aggregatedBillingInsuranceBenefit += $wk->insurance_benefit;
                }
                // 保険分利用者負担額
                if (is_null($wk->part_payment) || $wk->part_payment === '') {
                    $billingPartPayment =  '-';
                } else {
                    $billingPartPayment = number_format($wk->part_payment);
                    $aggregatedBillingPartPayment += $wk->part_payment;
                }
                // 保険（公費）公費請求額
                if (is_null($wk->public_spending_amount) || $wk->public_spending_amount === '') {
                    $billingPublicInsuranceBenefit =  '-';
                } else {
                    $billingPublicInsuranceBenefit = number_format($wk->public_spending_amount);
                    $aggregatedBillingPublicInsuranceBenefit += $wk->public_spending_amount;
                }
                // 保険（公費）本人支払額
                if (is_null($wk->public_payment) || $wk->public_payment === '') {
                    $billingPublicPartPayment = '-';
                } else {
                    $billingPublicPartPayment = number_format($wk->public_payment);
                    $aggregatedBillingPublicPartPayment += $wk->public_payment;
                }
            }
            if (!$dataFlg) {
                // 負担割合
                $benefitRate = '-';
                // 単位数合計
                $totalUnit = '-';
                // 費用合計
                $totalAmount = '-';
                // 保険分請求額
                $billingInsuranceBenefit = '-';
                // 保険分利用者負担額
                $billingPartPayment = '-';
                // 保険（公費）公費請求額
                $billingPublicInsuranceBenefit = '-';
                // 保険（公費）本人支払額
                $billingPublicPartPayment = '-';
            }
            // 保険外分利用者負担額
            $uninsuredSelfTotal = '-';
            if (count($sheet) >= 3 && !is_null($sheet[3]) && !is_null($sheet[3]->total_amount)) {
                $uninsuredSelfTotal = number_format($sheet[3]->total_amount);
                $aggregatedUninsuredSelfTotal += $sheet[3]->total_amount;
            }
            // 利用者負担額合計
            $totalAmountSelf = number_format($sheet[4]);
            $aggregatedTotalAmountSelf += $sheet[4];

            // 1行分のデータを設定
            $data = array(
                'fullname' => $fullname, // 利用者氏名
                'benefit_rate' => $benefitRate, // 負担割合
                'total_unit' => $totalUnit, // 単位数合計
                'total_amount' => $totalAmount, // 費用合計
                'billing_insurance_benefit' => $billingInsuranceBenefit, // 保険分請求額
                'billing_part_payment' => $billingPartPayment, // 保険分利用者負担額
                'billing_public_insurance_benefit' => $billingPublicInsuranceBenefit, // 保険（公費）公費請求額
                'billing_public_part_payment' => $billingPublicPartPayment, // 保険（公費）本人支払額
                'uninsured_self_total' => $uninsuredSelfTotal, // 保険外分利用者負担額
                'total_amount_self' => $totalAmountSelf, // 利用者負担額合計
            );
            array_push($details, $data);
        }

        // 共通部分の内容を設定する
        $header = array(
            // 事業所名
            'facility_name' => $facilityName,
            // 発行日
            'system_timestamp' => $systemTimestamp,
            // サービス提供年月
            'service_period' => $servicePeriod,
            // 利用者数合計
            'total_user_count'=>count($details),
            // 保険分単位数合計
            'total_unit' => number_format($aggregatedTotalUnit),
            // 保険分費用合計
            'total_amount' => '￥' . number_format($aggregatedTotalAmount),
            // 保険分請求額
            'billing_insurance_benefit' => '￥' . number_format($aggregatedBillingInsuranceBenefit),
            // 保険分利用者負担額
            'billing_part_payment' => '￥' . number_format($aggregatedBillingPartPayment),
            // 保険分（公費）公費分請求額
            'billing_public_insurance_benefit' => '￥' . number_format($aggregatedBillingPublicInsuranceBenefit),
            // 保険分（公費）本人支払額
            'billing_public_part_payment' => '￥' . number_format($aggregatedBillingPublicPartPayment),
            // 保険外分利用者負担額
            'uninsured_self_total' => '￥' . number_format($aggregatedUninsuredSelfTotal),
            // 利用者負担額合計
            'total_amount_self' => '￥' . number_format($aggregatedTotalAmountSelf),
        );

        // 一覧データをページ単位に配列に設定する
        $page = [];
        $workList = [];
        foreach($details as $detail) {
            array_push($workList, $detail);
            // 1ページ目のデータが24件、または2ページ目以降のデータが28件になったら改ページする
            if((count($page) == 0 && count($workList) == 24) ||
                    (count($page) > 0 && count($workList) == 28)) {
                array_push($page, $workList);
                $workList = [];
            }
        }
        // 最終ページ分のデータを設定する
        if (count($workList) > 0) {
            array_push($page, $workList);
        }

        // PDFファイルの出力処理を行う
        return PDF::loadView('group_home/own_uninsurance_bill/list', compact('targetYM', 'header', 'page', 'imgBillList', 'imgBillList2'))
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path(). $cssPath)
            ->setPaper('A4', 'landscape')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->inline($fileName);
    }

    /**
     * 利用料請求書一覧CSV出力処理
     *
     * @param array $facilityUserIds
     * @param string $targetMonth
     * @param OwnUninsuranceBillService $service
     * @param array $ledgerSheets
     *
     * @return response
     */
    private function outputInvoiceListCsv($facilityUserIds, $targetMonth, $service, $ledgerSheets)
    {
        $fileName = '利用料請求書一覧_'.CarbonImmutable::parse($targetMonth)->format('Ym'); // ファイル名
        $lineFeedCode = "\n"; // 改行コード
        $csvData = '';

        // 保険外品目情報の取得
        $uninsuredItems = $service->getUninsuredItems($facilityUserIds, $targetMonth);
        $uninsuredIdNameList = $uninsuredItems->pluck('uninsured_item_name', 'uninsured_item_histories_id')->toArray();
        if (empty($uninsuredIdNameList)) {
            $uninsuredItemList = [];
        } else {
            $maxId = max(array_keys($uninsuredIdNameList));
            $arrUninsuredItems = json_decode(json_encode($uninsuredItems), true);
            foreach ($arrUninsuredItems as $index => $value) {
                if ($value['uninsured_item_histories_id'] == '') {
                    $maxId++;
                    $arrUninsuredItems[$index]['uninsured_item_histories_id'] = $maxId;
                }
            }
            $uninsuredItemList = collect($arrUninsuredItems)->pluck('uninsured_item_name', 'uninsured_item_histories_id')->toArray();
        }

        // ヘッダー行追加
        $csvHeader = ['事業所番号', '事業所名', 'サービス提供年月', '利用者氏名', '契約者番号', '保険者番号', '被保険者番号', '負担割合', '要介護度', '単位数合計', '保険分費用総額', '保険分請求額', '保険分利用者負担額', '公費分請求額', '公費本人支払額', '保険外分利用者負担額', '利用者負担額合計'];
        foreach ($uninsuredItemList as $itemName) {
            array_push($csvHeader, $itemName);
        }
        $csvHeader = array_map(function($value) {
            return "\"{$value}\"";
        }, $csvHeader);
        $csvData = implode(',', $csvHeader).$lineFeedCode;

        // レコードの追加
        foreach ($ledgerSheets as $row) {
            $paymentInfo = current(current($row[2]));

            // 要介護度の名称取得
            $careLevelName = CareLevel::where('care_level_id', '=', $row[0]['care_level_id'])->value('care_level_name');

            $dataSet = [];
            $dataSet[] = $row[0]['facility_number']; // 事業所番号
            $dataSet[] = $row[0]['facility_name_kanji']; // 事業所名
            $dataSet[] = CarbonImmutable::parse($targetMonth)->format('Y/m'); // サービス提供月
            $dataSet[] = Crypt::decrypt($row[0]['last_name']).' '.Crypt::decrypt($row[0]['first_name']); // 利用者名
            $dataSet[] = (is_null($row[0]['contractor_number']) || $row[0]['contractor_number'] === '') ? '' : $row[0]['contractor_number']; // 契約者番号
            $dataSet[] = Crypt::decrypt($row[0]['insurer_no']); // 保険者番号
            $dataSet[] = Crypt::decrypt($row[0]['insured_no']); // 被保険者番号
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->own_payment_rate) || $paymentInfo->own_payment_rate === '')) ? '' : $paymentInfo->own_payment_rate.'割'; // 負担割合
            $dataSet[] = $careLevelName; // 要介護度
            $dataSet = array_map(function($value) {
                return "\"{$value}\"";
            }, $dataSet);
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->service_unit_amount) || $paymentInfo->service_unit_amount === '')) ? '' : $paymentInfo->service_unit_amount; // 単位数合計
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->total_cost) || $paymentInfo->total_cost === '')) ? '' : $paymentInfo->total_cost; // 費用総額
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->insurance_benefit) || $paymentInfo->insurance_benefit === '')) ? '' : $paymentInfo->insurance_benefit; // 保険分請求額
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->part_payment) || $paymentInfo->part_payment === '')) ? '' : $paymentInfo->part_payment; // 利用者負担額(保険分)
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->public_spending_amount) || $paymentInfo->public_spending_amount === '')) ? '' : $paymentInfo->public_spending_amount; // 公費分請求額
            $dataSet[] = (empty($paymentInfo) || (is_null($paymentInfo->public_payment) || $paymentInfo->public_payment === '')) ? '' : $paymentInfo->public_payment; // 公費本人支払額
            $dataSet[] = (empty($row[3]) || (is_null($row[3]->total_amount) || $row[3]->total_amount === '')) ? '' : $row[3]->total_amount; // 利用者負担額(保険外分)
            $dataSet[] = $row[4]; // 利用者負担額合計

            // 保険外品目の項目を追加
            if (!empty($uninsuredItemList) && !((empty($row[3]) || (is_null($row[3]->total_amount) || $row[3]->total_amount === '')))) {
                $filterUninsuredItems = array_filter($arrUninsuredItems, function($values) use ($row) {
                    return $values['facility_user_id'] == $row[0]['facility_user_id'];
                }, ARRAY_FILTER_USE_BOTH);
                $targetUninsuredItemList = [];
                foreach ($filterUninsuredItems as $uninsuredItem) {
                    $targetUninsuredItemList[$uninsuredItem['uninsured_item_histories_id']] = $uninsuredItem;
                }
                foreach ($uninsuredItemList as $index => $value) {
                    if ( ! empty($targetUninsuredItemList) && array_key_exists($index, $targetUninsuredItemList)) {
                        array_push($dataSet, $targetUninsuredItemList[$index]['unit_cost'] * $targetUninsuredItemList[$index]['quantity']);
                    } else {
                        array_push($dataSet, '');
                    }
                }
            }

            $csvData .= implode(',', $dataSet).$lineFeedCode;
        }
        $csvData = mb_convert_encoding($csvData, 'SJIS', 'UTF-8');

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"'
        ]);
    }
}
