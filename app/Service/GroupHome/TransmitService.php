<?php

namespace App\Service\GroupHome;

use App\Lib\Common\Consts;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\ReturnDocument;
use App\Models\ReturnAttachment;
use App\Models\Facility;
use App\Models\TransmissionPeriod;
use Carbon\Carbon;
use App\Utility\JapaneseImperialYear;
use App\Utility\S3;

class TransmitService
{
    /**
     * 請求情報を取得
     * @return array
     */
    public function getInvoiceData($param)
    {
        $day = self::getDay();
        $invoice = self::getInvoice($param->facility_id, $day);
        $history = self::getFilteredHistory($param->facility_id, $day, $param->from_date, $param->to_date);

        return ['invoice' => $invoice, 'history' => $history, 'day' => $day];
    }
    /**
     * 基準日を返却する
     */
    private function getDay()
    {
        $now = Carbon::now();
        $day;
        if ($now->day > 10) {
            $day = Carbon::now()->firstOfMonth()->format('Y-m-d');
        } else {
            $day = Carbon::now()->firstOfMonth()->subDays(1)->format('Y-m-d');
        }
        return $day;
    }
    /**
     * 請求データのインスタンスを返す
     */
    private function getInvoiceInstance($facility_id)
    {
        $facility = Facility::where('facility_id', $facility_id);
        return Invoice::joinSub($facility, 'facility', function ($join) {
            $join->on('i_invoices.facility_number', '=', 'facility.facility_number');
        })->select(
            'id',
            'target_date',
            'service_date',
            'facility_user_count',
            'csv',
            'download_details',
            'download_invoices',
            'status',
            'basic_status',
            'sub_status',
            'sent_at'
        );
    }
    /**
     * 請求データ一覧を返却する
     * 表示対象は、処理対象年月が当月または状態が未送信
     * (未送信の請求情報は常に請求データ一覧に表示する)
     */
    private function getInvoice($facility_id, $day)
    {
        $invoice = self::getInvoiceInstance($facility_id);
        return $invoice
            ->where(function ($query) use ($day) {
                $query->where('target_date', '>=', $day)->orWhere('status', Consts::INVALID);
            })
            ->orderBy('i_invoices.created_at', 'desc')
            ->get();
    }

    /**
     * 請求履歴一覧を返却する(絞り込み)
     * 表示対象は、処理対象年月が前月以前かつ状態が未送信以外
     * (未送信の請求情報は履歴には表示しない)
     */
    private function getFilteredHistory($facility_id, $day, $from_day, $to_day)
    {
        $invoice = self::getInvoiceInstance($facility_id);
        $invoice->where('status', '!=', Consts::INVALID);
        $invoice->where('target_date', '<', $day);

        if ($from_day) {
            $invoice->where('target_date', '>=', $from_day);
        }
        if ($to_day) {
            $invoice->where('target_date', '<=', $to_day);
        }

        return $invoice
            ->orderByRaw('target_date desc, service_date desc, i_invoices.created_at desc')->get();
    }
    /**
     * 履歴データの絞り込みしたデータを返却する
     */
    public function getFilterData($param)
    {
        $day = self::getDay();
        return self::getFilteredHistory($param->facility_id, $day, $param->from_date, $param->to_date);
    }

    public function getDocumentList($param)
    {
        if ($param->id) {
            ReturnDocument::updateCheckoutTime($param->id);
        }

        $document = ReturnDocument::find($param->id);
        $list = [];
        $list["content"] = nl2br($document->content);
        $list["title"] = $document->title;
        $list["published_at"] = $document->published_at;
        foreach ($document->returnAttachment as $attachment) {
            $tmp = [];
            $index = $attachment->index;
            $documentName = $attachment->document_name;
            $downloadFile = $attachment->download_file;

            if (S3::existData($downloadFile)) {
                $tmp["index"] = $index;
                $tmp["documentName"] = $documentName;
                // まぁJS側でURL組み立ててもいい気はするんだけど
                $tmp["downloadUrl"] = "/group_home/transmit_info/transmit/get_retrundocument?id=". $param->id. "&index=". $index;
                $list["attachments"][] = $tmp;
            }
        }
        return $list;
    }

    /**
     * 通知文書一覧を返却する
     */
    public function getDocumentData($param)
    {
        $facilityNumber = facility::select('facility_number')
            ->where('facility_id', $param['facility_id'])
            ->first();

        // NOTE: 各事業所向けとは別に、同都道府県に属する事業所全てに同じお知らせが送られる仕様のため
        // 地域全体宛てのお知らせ(各事業所番号の上二桁+'********'で登録されているレコード)も取得
        $documents = ReturnDocument::select('facility_number', 'target_date', 'document_type', 'title', 'published_at', 'checked_at', 'download_file', 'id')
            ->whereIn('facility_number', [
                $facilityNumber['facility_number'],                             // 自事業所
                substr($facilityNumber['facility_number'], 0, 2) . '********'   // 地域全体宛て
            ]);

        if ($param['from_date']) {
            $documents->whereDate('published_at', '>=', $param['from_date']);
        }
        if ($param['to_date']) {
            $documents->whereDate('published_at', '<=', $param['to_date']);
        }

        // 保守対応中の文書を表示させないため
        $documents->where('status', Consts::INVALID);

        return $documents->orderByRaw('published_at desc, id desc')->get();
    }

    /**
     * 請求情報の送信
     */
    public function sentInvoice($param)
    {
        $now = Carbon::now()->format('Y/m/d H:i:s');

        $updated = [
            'status' => Consts::VALID,
            'sent_at' => $now
        ];
        $invoice = Invoice::where('id', $param->id)->first()->update($updated);
        return ['invoice' => $invoice];
    }

    /**
     * 送信取消対象に更新
     */
    public function cancelTransmit($param)
    {
        $updated = [
            'status' => 7
        ];
        $updateResult = Invoice::where('id', $param->id)->first()->update($updated);

        return ['update_result' => $updateResult];
    }

    /**
     * 請求データ削除
     */
    public function deleteInvoice($param)
    {
        InvoiceDetail::where('invoice_id', $param->id)->delete();
        $deleteResult = Invoice::find($param->id)->delete();

        return ['delete_result' => $deleteResult];
    }


    /**
     * 数値に3桁カンマを付与して返す
     */
    private function numberFormat($params, $key)
    {
        if (isset($params[$key]) && is_numeric($params[$key])) {
            $params[$key] = number_format($params[$key]);
        }
        return $params;
    }
    private function recNumberFormat($params)
    {
        $numberKey = [
            'numberOfService',
            'daysOfService',
            'numberOfUnits',
            'amount',
            'longTermCareBenefits',
            'numberOfServiceForSpecial',
            'daysOfServiceForSpecial',
            'numberOfUnitsForSpecial',
            'longTermCareBenefitsForSpecial',
            // 7211 追加分
            'nursingCareReturns',
            'numberOfReturnsOfSpecifiedResidents',
            'nursingCareReturnUnits',
            'returnSpecificResidentServiceFee',
            'numberOfExaminationsForNursingCare',
            'numberOfScreeningsForSpecifiedResidents',
            'nursingCareExaminationCredits',
            'examinationSpecificResidentServiceFee',
            'numberOfPendingNursingCare',
            'numberOfPendingCasesForSpecificResidents',
            'nursingCarePendingUnits',
            'retainedSpecificResidentServiceFee',
            'nursingCarePutOnHoldAndReinstated',
            'numberOfPendingReinstatementCasesForSpecificResidents',
            'nursingCareSuspensionReinstatementUnits',
            'specificResidentServiceFeeForPendingReinstatement',
            // 7411 追加分
            'unit',
            // 7231 追加分
            'returnCount',
            'returnUnits',
            'assessmentCount',
            'assessmentUnits',
            'pendingCount',
            'pendingUnits',
            'putOnHoldAndRevivalCount',
            'putOnHoldAndRevivalUnits',
        ];

        if (isset($params['rec'])) {
            $i = 0;
            foreach ($params['rec'] as $index => $r) {
                foreach ($numberKey as $key) {
                    if (isset($r[$key]) && is_numeric($r[$key])) {
                        $r[$key] = number_format($r[$key]);
                    }
                }
                $params['rec'][$i] = $r;
                $i++;
            }
            if ($i > 10) {
                \Log::warning('['.__CLASS__.':' . __FUNCTION__.':'.__LINE__.']'.'明細レコード数が10より大きい。 明細レコード数:'.$i);
            }
        }
        return $params;
    }
    /**
     * すべての数値に3桁カンマを付与
     */
    private function numberFormatAll($params)
    {
        $numberKey = [
            'transferAmountOfMoney',
            'longTermCareBenefits',
            'doctorsOpinion',
            'doctorsOpinionConsumptionTax',
            'accreditedSurveyFee',
            'accreditedSurveyFeeConsumptionTax',
            'ComprehensiveBusinessFee',
            'digitalCertificateFee',
            'totalAmount',
            't1LongTermCareBenefits',
            't1NumberOfServiceForSpecial',
            't1DaysOfServiceForSpecial',
            't1NumberOfUnitsForSpecial',
            't1LongTermCareBenefitsForSpecial',
            't1NumberOfService',
            't1DaysOfService',
            't1NumberOfUnits',
            't1Amount',
            't1LongTermCareBenefits',
            't1NumberOfServiceForSpecial',
            't1DaysOfServiceForSpecial',
            't1NumberOfUnitsForSpecial',
            't1LongTermCareBenefitsForSpecial',
            't2NumberOfService',
            't2DaysOfService',
            't2NumberOfUnits',
            't2Amount',
            't2LongTermCareBenefits',
            't2NumberOfServiceForSpecial',
            't2DaysOfServiceForSpecial',
            't2NumberOfUnitsForSpecial',
            't2LongTermCareBenefitsForSpecial',
            't3NumberOfService',
            't3DaysOfService',
            't3NumberOfUnits',
            't3Amount',
            't3LongTermCareBenefits',
            't3NumberOfServiceForSpecial',
            't3DaysOfServiceForSpecial',
            't3NumberOfUnitsForSpecial',
            't3LongTermCareBenefitsForSpecial',
            // 7211 追加分
            't1NursingCareBillDifferenceNumber',
            't1NumberOfBillingDiscrepanciesForSpecifiedResidents',
            't1NursingCareBillDifferentialUnits',
            't1BillingDifferenceSpecificResidentServiceFee',
            't1NursingCareReturns',
            't1NumberOfReturnsOfSpecifiedResidents',
            't1NursingCareReturnUnits',
            't1ReturnSpecificResidentServiceFee',
            't1NumberOfExaminationsForNursingCare',
            't1NumberOfScreeningsForSpecifiedResidents',
            't1NursingCareExaminationCredits',
            't1ExaminationSpecificResidentServiceFee',
            't1NumberOfPendingNursingCare',
            't1NumberOfPendingCasesForSpecificResidents',
            't1NursingCarePendingUnits',
            't1RetainedSpecificResidentServiceFee',
            't1NursingCarePutOnHoldAndReinstated',
            't1NumberOfPendingReinstatementCasesForSpecificResidents',
            't1NursingCareSuspensionReinstatementUnits',
            't1SpecificResidentServiceFeeForPendingReinstatement',
            // 7231 追加分
            't1BillDifferenceCount',
            't1BillDifferenceAmounts',
            't1ReturnCount',
            't1ReturnUnits',
            't1AssessmentCount',
            't1AssessmentUnits',
            't1PendingCount',
            't1PendingUnits',
            't1PutOnHoldAndRevivalCount',
            't1PutOnHoldAndRevivalUnits',
        ];
        foreach ($numberKey as $key) {
            $params = self::numberFormat($params, $key);
        }
        $params = self::recNumberFormat($params);

        return $params;
    }
    /**
     * 斜線画像のbase64変換
     */
    private function transformJPGtoBase64()
    {
        $filePath = public_path() . "/img/diagonal.jpg";
        $diagonal_base64 = "";
        if (file_exists($filePath)) {
            $img = base64_encode(file_get_contents($filePath));
            $diagonal_base64 = 'data:'.mime_content_type($filePath).';base64,'.$img;
        }
        return $diagonal_base64;
    }
    /**
     * 通知文書の生成
     */
    public function makePDF($params)
    {
        $params = self::numberFormatAll($params);
        $params['diagonal_base64'] = self::transformJPGtoBase64();

        switch ($params['identification']) {
            case '7211':
                // 1ページのmaxレコード数で配列を分割する
                $params['rec'] = self::arrChunk($params['rec'], 14);
                // return view('group_home/transmit_info/7211pdf', $params);
                return self::returnCreatePdf('7211pdf', $params, 'landscape');
                break;
            case '7231':
                $params['rec'] = self::arrChunk($params['rec'], 14);
                // return view('group_home/transmit_info/7231pdf', $params);
                return self::returnCreatePdf('7231pdf', $params, 'landscape');
                break;
            case '7411':
                $params['rec'] = self::arrChunk($params['rec'], 12);
                // return view('group_home/transmit_info/7411pdf',$params);
                return self::returnCreatePdf('7411pdf', $params, 'landscape');
            case '7513':
                // return view('group_home/transmit_info/7513pdf',$params);
                return self::returnCreatePdf('7513pdf', $params, 'portrait');
                break;
            case '7521':
                $params['rec'] = self::arrChunk($params['rec'], 9);
                // return view('group_home/transmit_info/7521pdf',$params);
                return self::returnCreatePdf('7521pdf', $params, 'landscape');
                break;
            case '7551':
                $params['rec'] = self::arrChunk($params['rec'], 10);
                // return view('group_home/transmit_info/7551pdf',$params);
                return self::returnCreatePdf('7551pdf', $params, 'landscape');
                break;
            default:
                return 0;
        }
    }

    /**
     * 配列を分割して返す
     * @param array $param
     * @param int $length
     */
    public function arrChunk($param, $length)
    {
        $arrchunk = array_chunk($param, $length);
        return $arrchunk;
    }

    /**
     * PDFを作成して返す
     * @param string $pdfType
     * @param array $params
     * @param string $paperType
     */
    private function returnCreatePdf($pdfType, $params, $paperType)
    {
        return \PDF::loadView('group_home/transmit_info/'.$pdfType, $params)
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path() . '/css/group_home/transmit_info/print.css')
            ->setPaper('A4', $paperType)
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);
    }

    // 介護給付費等支払決定額通知書情報
    public static function parseType751($data)
    {
        $params = [
            'identification' => $data[0][2],
            'examinationDate' => self::formatWareki($data[0][4] . '01', false),
            'postal1' => $data[0][5],
            'postal2' => $data[0][6],
            'location' => $data[0][7],
            'facilityName' => $data[0][8],
            'name' => $data[0][9],
            'facilityNumber' => $data[0][10],
            'transferAmountOfMoney' => $data[0][11],
            'longTermCareBenefits' => $data[0][12],
            'doctorsOpinion' => $data[0][13],
            'doctorsOpinionConsumptionTax' => $data[0][14],
            'accreditedSurveyFee' => $data[0][15],
            'accreditedSurveyFeeConsumptionTax' => $data[0][16],
            'ComprehensiveBusinessFee' => $data[0][17],
            'digitalCertificateFee' => $data[0][18],
            'totalAmount' => $data[0][19],
            'bank' => $data[0][20],
            'branch' => $data[0][21],
            'dateOfCreation' => self::formatWareki($data[0][22]),
            'kokuhoren' => $data[0][23]
        ];
        return $params;
    }
    public static function formatWareki($datestring, $outputDay=true, $ryaku=false)
    {
        $wareki = JapaneseImperialYear::get($datestring);
        $wareki_name = '';

        if ($outputDay) {
            if ($ryaku) {
                $output = $wareki['ryaku'] . ' ' . intval($wareki['year']) . '.' . ' '  . intval($wareki['month']) . '.' . intval($wareki['day']) . '日';
            } else {
                $output = $wareki['name'] . ' ' . intval($wareki['year']) . '年' . ' '  . intval($wareki['month']) . '月' . ' '  . intval($wareki['day']) . '日';
            }
        } else {
            if ($ryaku) {
                $output = $wareki['ryaku'] . ' ' . intval($wareki['year']) . '.' . ' '  . intval($wareki['month']);
            } else {
                $output = $wareki['name'] . ' ' . intval($wareki['year']) . '年' . ' '  . intval($wareki['month']) . '月';
            }
        }
        return $output;
    }
    // 介護給付費等支払決定額内訳書情報
    // 介護予防・日常生活支援総合事業費支払決定額内訳書情報
    public static function parseTypeItems($data)
    {
        $params = [];
        foreach ($data as $row) {
            switch ($row[3]) {
                case 'H1':
                    $params['identification'] = $row[2];
                    $params['facilityNumber'] = $row[4];
                    $params['facilityName'] = $row[5];
                    $params['examinationDate'] = self::formatWareki($row[6] . '01', false);
                    $params['dateOfCreation'] = self::formatWareki($row[7]);
                    $params['page'] = $row[8];
                    $params['kokuhoren'] = $row[9];
                    break;
                case 'D1':
                    $rec[] = [
                        'insurerNo' => $row[4],
                        'dateOfProvision' => self::formatWareki($row[5] . '01', false, true),
                        'kindCode' => $row[6],
                        'kindName' => $row[7],
                        'numberOfService' => $row[8],
                        'daysOfService' => $row[9],
                        'numberOfUnits' => $row[10],
                        'amount' => $row[11],
                        'longTermCareBenefits' => $row[12],
                        'numberOfServiceForSpecial' => $row[13],
                        'daysOfServiceForSpecial' => $row[14],
                        'numberOfUnitsForSpecial' => $row[15],
                        'longTermCareBenefitsForSpecial' => $row[16],
                    ];
                    $params['rec'] = $rec;
                    break;
                case 'T1':
                    $params['t1NumberOfService'] = $row[4];
                    $params['t1DaysOfService'] = $row[5];
                    $params['t1NumberOfUnits'] = $row[6];
                    $params['t1Amount'] = $row[7];
                    $params['t1LongTermCareBenefits'] = $row[8];
                    $params['t1NumberOfServiceForSpecial'] = $row[9];
                    $params['t1DaysOfServiceForSpecial'] = $row[10];
                    $params['t1NumberOfUnitsForSpecial'] = $row[11];
                    $params['t1LongTermCareBenefitsForSpecial'] = $row[12];
                    break;
                case 'T2':
                    $params['t2NumberOfService'] = $row[4];
                    $params['t2DaysOfService'] = $row[5];
                    $params['t2NumberOfUnits'] = $row[6];
                    $params['t2Amount'] = $row[7];
                    $params['t2LongTermCareBenefits'] = $row[8];
                    $params['t2NumberOfServiceForSpecial'] = $row[9];
                    $params['t2DaysOfServiceForSpecial'] = $row[10];
                    $params['t2NumberOfUnitsForSpecial'] = $row[11];
                    $params['t2LongTermCareBenefitsForSpecial'] = $row[12];
                    break;
                case 'T3':
                    $params['t3NumberOfService'] = $row[4];
                    $params['t3DaysOfService'] = $row[5];
                    $params['t3NumberOfUnits'] = $row[6];
                    $params['t3Amount'] = $row[7];
                    $params['t3LongTermCareBenefits'] = $row[8];
                    $params['t3NumberOfServiceForSpecial'] = $row[9];
                    $params['t3DaysOfServiceForSpecial'] = $row[10];
                    $params['t3NumberOfUnitsForSpecial'] = $row[11];
                    $params['t3LongTermCareBenefitsForSpecial'] = $row[12];
                    break;
            }
        }
        return $params;
    }

    // 介護保険審査決定増減表
    public static function parseType721($data)
    {
        // dd($data);
        $params = [];
        foreach ($data as $row) {
            switch ($row[3]) {
                case 'H1':
                    $params['identification'] = $row[2];
                    $params['facilityNumber'] = $row[4];
                    $params['facilityName'] = $row[5];
                    $params['examinationDate'] = self::formatWareki($row[6] . '01', false);
                    $params['dateOfCreation'] = self::formatWareki($row[7]);
                    $params['page'] = $row[8];
                    $params['kokuhoren'] = $row[9];
                    break;
                case 'D1':
                    $rec[] = [
                        // 保険者番号
                        'insurerNo' => $row[4],
                        // サービス提供年月
                        'dateOfProvision' => self::formatWareki($row[5] . '01', false, true),
                        // 介護の返戻件数
                        'nursingCareReturns' => $row[6],
                        // 特定入所者介護等の返戻件数
                        'numberOfReturnsOfSpecifiedResidents' => $row[7],
                        // 介護の返戻単位数
                        'nursingCareReturnUnits' => $row[8],
                        // 返戻の特定入所者介護サービス費等
                        'returnSpecificResidentServiceFee' => $row[9],
                        // 介護の審査増減件数
                        'numberOfExaminationsForNursingCare' => $row[10],
                        // 特定入所者介護等の審査増減件数
                        'numberOfScreeningsForSpecifiedResidents' => $row[11],
                        // 介護の審査増減単位数
                        'nursingCareExaminationCredits' => $row[12],
                        // 審査増減の特定入所者介護サービス費等
                        'examinationSpecificResidentServiceFee' => $row[13],
                        // 介護の保留分件数
                        'numberOfPendingNursingCare' => $row[14],
                        // 特定入所者介護等の保留分件数
                        'numberOfPendingCasesForSpecificResidents' => $row[15],
                        // 介護の保留分単位数
                        'nursingCarePendingUnits' => $row[16],
                        // 保留分の特定入所者介護サービス費等
                        'retainedSpecificResidentServiceFee' => $row[17],
                        // 介護の保留復活分件数
                        'nursingCarePutOnHoldAndReinstated' => $row[18],
                        // 特定入所者介護等の保留復活分件数
                        'numberOfPendingReinstatementCasesForSpecificResidents' => $row[19],
                        // 介護の保留復活分単位数
                        'nursingCareSuspensionReinstatementUnits' => $row[20],
                        // 保留復活分の特定入所者介護サービス費等
                        'specificResidentServiceFeeForPendingReinstatement' => $row[21],
                    ];
                    $params['rec'] = $rec;
                    break;
                case 'T1':
                    // 介護の請求差件数
                    $params['t1NursingCareBillDifferenceNumber'] = $row[4];
                    // 特定入所者介護等の請求差件数
                    $params['t1NumberOfBillingDiscrepanciesForSpecifiedResidents'] = $row[5];
                    // 介護の請求差単位数
                    $params['t1NursingCareBillDifferentialUnits'] = $row[6];
                    // 請求差の特定入所者介護サービス費等
                    $params['t1BillingDifferenceSpecificResidentServiceFee'] = $row[7];
                    // 介護の返戻件数(合計)
                    $params['t1NursingCareReturns'] = $row[8];
                    // 特定入所者介護等の返戻件数(合計)
                    $params['t1NumberOfReturnsOfSpecifiedResidents'] = $row[9];
                    // 介護の返戻単位数(合計)
                    $params['t1NursingCareReturnUnits'] = $row[10];
                    // 返戻の特定入所者介護サービス費等(合計)
                    $params['t1ReturnSpecificResidentServiceFee'] = $row[11];
                    // 介護の審査増減件数(合計)
                    $params['t1NumberOfExaminationsForNursingCare'] = $row[12];
                    // 特定入所者介護等の審査増減件数(合計)
                    $params['t1NumberOfScreeningsForSpecifiedResidents'] = $row[13];
                    // 介護の審査増減単位数(合計)
                    $params['t1NursingCareExaminationCredits'] = $row[14];
                    // 審査増減の特定入所者介護サービス費等(合計)
                    $params['t1ExaminationSpecificResidentServiceFee'] = $row[15];
                    // 介護の保留分件数
                    $params['t1NumberOfPendingNursingCare'] = $row[16];
                    // 特定入所者介護等の保留分件数
                    $params['t1NumberOfPendingCasesForSpecificResidents'] = $row[17];
                    // 介護の保留分単位数
                    $params['t1NursingCarePendingUnits'] = $row[18];
                    // 保留分の特定入所者介護サービス費等
                    $params['t1RetainedSpecificResidentServiceFee'] = $row[19];
                    // 介護の保留復活分件数
                    $params['t1NursingCarePutOnHoldAndReinstated'] = $row[20];
                    // 特定入所者介護等の保留復活分件数
                    $params['t1NumberOfPendingReinstatementCasesForSpecificResidents'] = $row[21];
                    // 介護の保留復活分単位数
                    $params['t1NursingCareSuspensionReinstatementUnits'] = $row[22];
                    // 保留復活分の特定入所者介護サービス費等
                    $params['t1SpecificResidentServiceFeeForPendingReinstatement'] = $row[23];
                    break;
            }
        }
        return $params;
    }

    /**
     * 介護予防・日常生活支援総合事業審査決定増減表
     */
    public static function parseType723($data)
    {
        $params = [];

        foreach ($data as $row) {
            switch ($row[3]) {
                case 'H1':
                    $params['identification']  = $row[2]; // 交換情報識別番号
                    $params['facilityNumber']  = $row[4]; // 事業所番号
                    $params['facilityName']    = $row[5]; // 事業所名
                    $params['examinationDate'] = self::formatWareki($row[6] . '01', false); // 審査年月
                    $params['dateOfCreation']  = self::formatWareki($row[7]); // 作成年月日
                    $params['page']            = $row[8]; // ページ
                    $params['kokuhoren']       = $row[9]; // 国保連合会名
                    break;
                case 'D1':
                    $rec[] = [
                        'insurerNo'                => $row[4], // 保険者番号
                        'dateOfProvision'          => self::formatWareki($row[5] . '01', false, true), // サービス提供年月
                        'returnCount'              => $row[6], // 返戻件数
                        'returnUnits'              => $row[8], // 返戻単位数
                        'assessmentCount'          => $row[10], // 査定増減件数
                        'assessmentUnits'          => $row[12], // 査定増減単位数
                        'pendingCount'             => $row[14], // 保留分件数
                        'pendingUnits'             => $row[16], // 保留分単位数
                        'putOnHoldAndRevivalCount' => $row[18], // 保留復活分件数
                        'putOnHoldAndRevivalUnits' => $row[20], // 保留復活分単位数
                    ];
                    $params['rec'] = $rec;
                    break;
                case 'T1':
                    $params['t1BillDifferenceCount']        = $row[4]; // 請求差件数
                    $params['t1BillDifferenceAmounts']      = $row[6]; // 請求差金額
                    $params['t1ReturnCount']                = $row[8]; // 返戻件数
                    $params['t1ReturnUnits']                = $row[10]; // 返戻単位数
                    $params['t1AssessmentCount']            = $row[12]; // 査定増減件数
                    $params['t1AssessmentUnits']            = $row[14]; // 査定増減単位数
                    $params['t1PendingCount']               = $row[16]; // 保留分件数
                    $params['t1PendingUnits']               = $row[18]; // 保留分単位数
                    $params['t1PutOnHoldAndRevivalCount']   = $row[20]; // 保留復活分件数
                    $params['t1PutOnHoldAndRevivalUnits']   = $row[22]; // 保留復活分単位数
                    break;
            }
        }

        return $params;
    }

    // 請求明細書・給付管理票返戻（保留）一覧表
    public static function parseType741($data)
    {
        $params = [];
        foreach ($data as $row) {
            switch ($row[3]) {
                case 'H1':
                    $params['identification'] = $row[2];
                    $params['facilityNumber'] = $row[4];
                    $params['facilityName'] = $row[5];
                    $params['examinationDate'] = self::formatWareki($row[6] . '01', false);
                    $params['dateOfCreation'] = self::formatWareki($row[7]);
                    $params['page'] = $row[8];
                    $params['kokuhoren'] = $row[9];
                    break;
                case 'D1':
                    $rec[] = [
                        // 保険者（事業所）番号
                        'insurerNo' => $row[4],
                        // 保険者（事業所）名
                        'insurerName' => mb_substr($row[5], 0, 10),
                        // 被保険者番号
                        'insuredNo' => $row[6],
                        // 被保険者カナ氏名
                        'insuredNameKana' => mb_substr($row[7], 0, 20),
                        // 種別
                        'type' => $row[8],
                        // サービス提供年月
                        'dateOfProvision' => self::formatWareki($row[9] . '01', false, true),
                        // サービス種類コード
                        'kindCode' => $row[10],
                        // 単位数
                        'unit' => $row[11],
                        // 事由
                        'reason' => $row[12],
                        // 内容
                        'content' => $row[13],
                        // 備考
                        'remark' => $row[14],
                        // サービス項目コード等
                        'serviceItemCode' => $row[15],
                    ];
                    $params['rec'] = $rec;
                    break;
            }
        }
        return $params;
    }

    /**
     * 伝送期間判定
     */
    public function checkTransmitPeriod()
    {
        $now = Carbon::now();
        $nowDay = $now->day;
        $nowTime = $now->format('H:i');

        $result = TransmissionPeriod::where('start_day', '<=', $nowDay)
            ->where('end_day', '>=', $nowDay)
            ->where('start_time', '<=', $nowTime)
            ->where('end_time', '>=', $nowTime)
            ->exists();

        return ['result' => $result];
    }
}
