<?php

namespace App\Service\GroupHome;

use App\Lib\Common\Consts;
use App\Models\InvoiceDetail;
use App\Models\Invoice;
use App\Models\ServiceResult;
use App\Models\Facility;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    const RECORD_LIMIT = 10000000;
    // 請求情報ステータス
    // todo App\Service\Api\Invoice\InvoiceService 内の内容と同一のため
    // app\Lib\Common\Const.php にまとめる
    const INVOICE_STATUS_PREPARE         = 0; // 未送信
    const INVOICE_STATUS_SENDING         = 1; // 伝送中
    const INVOICE_STATUS_READY           = 2; // 受付中
    const INVOICE_STATUS_IN_PROGRESS     = 3; // 処理中
    const INVOICE_STATUS_FINISHED        = 4; // 完了
    const INVOICE_STATUS_FORMAT_ERROR    = 5; // 様式エラー
    const INVOICE_STATUS_ERROR           = 6; // 外部エラー
    const INVOICE_STATUS_CANCEL_REQUEST  = 7; // 取消依頼中
    const INVOICE_STATUS_IN_CANCELING    = 8; // 取消中
    const INVOICE_STATUS_CANCELED        = 9; // 取消完了
    /**
     * 伝送請求情報をDBに登録する
     * @return Array
     */
    public function getBenefitBilling($param) : array {
        try {
            // 事業所番号を取得する
            $facilityNumber = $this->getFacilityNumber($param['facility_id']);

            $year = $param['year'];
            $month = $param['month'];

            $serviceDate = Carbon::create($year, $month)->startOfMonth(); // サービス提供年月
            $billingTargetDate = BillingCalc::getBillingTargetYM(CarbonImmutable::parse('now'));
            $targetDate = Carbon::create(substr($billingTargetDate, 0, 4), substr($billingTargetDate, -2))->startOfMonth(); // 処理対象年月

            $param['facility_number'] = $facilityNumber;
            $param['service_date'] = $serviceDate;
            $param['target_date'] = $targetDate;

            // 同一の事業者、同一の処理対象年月、同一のサービス提供年月の請求情報が送信済みかどうかを取得する
            $invoice = $this->invoiceCheck($param);

            // 請求情報がある場合はエラーとする
            if (
                !empty($invoice)
                && $invoice[0]['status']['id'] != self::INVOICE_STATUS_PREPARE
                && $invoice[0]['status']['id'] != self::INVOICE_STATUS_ERROR
                && $invoice[0]['status']['id'] != self::INVOICE_STATUS_CANCELED
            ) {
                // エラーになっているため例外をthrowする
                throw new Exception("請求済みのデータが存在します。請求データの作成はできません。");
            }

            $billingDatas = $this->getServiceResult($param);

            // 承認済みの請求データが存在しない場合はエラーとする
            if (empty($billingDatas)) {
                // エラーになっているため例外をthrowする
                throw new Exception("請求対象の利用者が登録されていません。");
            }

            $billingParams = [];
            $facilityUserCount = 0;
            $billingAmount = 0;

            // 対象利用者数と請求額の合計を取得する
            $facilityUserIdOld = "";
            $billingParams['facilityUserId'] = [];

            foreach ($billingDatas as $billingVal) {
                // 同一の利用者が別のサービスを利用している場合は利用者数は増やさない
                if ($facilityUserIdOld != $billingVal['facility_user_id']) {
                    $facilityUserCount++;
                    $billingParams['facilityUserId'][] = $billingVal['facility_user_id'];
                }

                $publicSpendingAmount = 0;
                if (!is_null($billingVal['public_spending_amount'])) {
                    $publicSpendingAmount = $billingVal['public_spending_amount'];
                }
                $billingAmount += $billingVal['insurance_benefit'] + $publicSpendingAmount;
                $facilityUserIdOld = $billingVal['facility_user_id'];
            }

            $billingParams['facilityUserCount'] = $facilityUserCount;
            $billingParams['billingAmount'] = $billingAmount;
            $billingParams['facilityNumber'] = $facilityNumber;

            $billingParams['targetDate'] = $targetDate->format('Y-m-d');
            $billingParams['serviceDate'] = $serviceDate->format('Y-m-d');
        } catch (Exception $e) {
            report($e);
            return [
                "notice" => -1,
                "error" => $e->getMessage()
            ];
        }

        if (!empty($invoice) && $invoice[0]['status']['id'] == self::INVOICE_STATUS_PREPARE) {
            // 対象の請求情報が存在かつ未送信の場合は上書き
            $billingParams['id'] = $invoice[0]['id'];
            return $this->updateInvoices($billingParams, $billingDatas);
        } else {
            // 対象の請求情報が存在しない場合は新規追加
            return $this->insertInvoices($billingParams, $billingDatas);
        }
    }

    /**
     * DBから同一の事業所で同一の処理対象年月で同一のサービス提供年月の請求情報を取得する
     * @return Array
     */
    public function invoiceCheck($param) : array {
        return Invoice::
            where('facility_number', $param['facility_number'])
            ->where('target_date', $param['target_date'])
            ->where('service_date', $param['service_date'])
            ->select('id', 'status', 'csv', 'download_details', 'download_invoices', 'basic_status', 'sub_status')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * DBから請求データを取得する
     * @return Array
     */
    public function getServiceResult($param) : array {
        return ServiceResult::
            date($param['year'], $param['month'])
            ->where('approval', Consts::VALID)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->whereIn('facility_user_id', $param['facility_user_id'])
            ->select('facility_id', 'facility_user_id', 'insurance_benefit', 'public_spending_amount', 'target_date', 'service_use_date')
            ->orderBy('facility_user_id', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 事業所IDをキーにして事業所番号を取得する
     * @return String
     */
    public function getFacilityNumber($facility_id) {
        return Facility::find($facility_id)->facility_number;
    }

    /**
     * 伝送請求情報と詳細をDBに登録する
     * @return Array
     */
    public function insertInvoices($param, $billingDatas) : array {
        DB::beginTransaction();
        try {
            // 行ロックを掛けて既存レコードをカウントする
            $sequenceNo = Invoice::
                lockForUpdate()
                ->where('target_date', $param['targetDate'])
                ->where('service_date', $param['serviceDate'])
                ->where('facility_number', $param['facilityNumber'])
                ->count();

            // ここで取得した件数を+1にして10000000以上だったらエラーとする
            $NextSequence = $sequenceNo + 1;
            if ($NextSequence >= self::RECORD_LIMIT) {
                // エラーになっているため例外をthrowする
                throw new Exception("これ以上請求データを作成できません");
            }

            // CSVファイル名とS3保存先パスを生成する
            $fileName = "I". str_pad($NextSequence, 7, '0', STR_PAD_LEFT);
            $targetDateYm = (new CarbonImmutable(substr($param['targetDate'], 0, -3)))->format('Ym');
            $serviceDateYm = (new CarbonImmutable(substr($param['serviceDate'], 0, -3)))->format('Ym');
            $csvFileName = $fileName .".csv"; // CSV
            $csvPath = $param['facilityNumber'] ."/invoices/". $targetDateYm ."/".$serviceDateYm."/".$csvFileName;

            // 現在日時
            $now = Carbon::now();
            $now_date = $now->format('Y-m-d H:i:s');

            // 伝送請求情報 新規登録
            // ↓↓　i_inviocesに登録する内容　↓↓
            $invioces = new Invoice;
            $invioces->facility_number = $param['facilityNumber']; // 事業所番号
            $invioces->facility_user_count = $param['facilityUserCount']; // 対象利用者数→請求情報に含まれる利用者の人数
            $invioces->billing_amount = $param['billingAmount']; // 請求額（合計）
            $invioces->csv = $csvPath; // CSVパス
            $invioces->target_date = $param['targetDate']; // 処理対象年月の1日
            $invioces->service_date = $param['serviceDate']; // サービス提供年月の1日
            $invioces->status = 0;
            $invioces->save();

            // 請求情報IDを取得する
            $lastInsertId = $invioces->id;

            // 伝送請求詳細をDBに登録
            foreach ($param['facilityUserId'] as $facilityUserId) {
                // ↓↓　i_invioce_detailに登録する内容　↓↓
                $invoiceDetail = new InvoiceDetail;
                $invoiceDetail->invoice_id = $lastInsertId;
                $invoiceDetail->facility_user_id = $facilityUserId;
                $invoiceDetail->status = 1;
                $invoiceDetail->save();
            }

            // 伝送請求情報と伝送請求詳細の両方がDBに登録された時点でコミット
            DB::commit();
        } catch (Exception $e) {
            // 伝送請求情報、伝送請求詳細いずれかでDB登録に失敗したらロールバック
            DB::rollback();
            report($e);
            return [
                "notice" => -1,
                "error" => $e->getMessage()
            ];
        }

        return [
            "csvPath" => $csvPath
        ];
    }

    /**
     * 伝送請求情報と詳細をDBに更新する
     * @return Array
     */
    public function updateInvoices($param, $billingDatas) : array {
        DB::beginTransaction();
        try {
            // 行ロックを掛けて既存レコードをカウントする
            $sequenceNo = Invoice::
                lockForUpdate()
                ->where('target_date', $param['targetDate'])
                ->where('service_date', $param['serviceDate'])
                ->where('facility_number', $param['facilityNumber'])
                ->count();

            // CSVファイル名とS3保存先パスを生成する
            $fileName = "I". str_pad($sequenceNo, 7, '0', STR_PAD_LEFT);
            $targetDateYm = (new CarbonImmutable(substr($param['targetDate'], 0, -3)))->format('Ym');
            $serviceDateYm = (new CarbonImmutable(substr($param['serviceDate'], 0, -3)))->format('Ym');
            $csvFileName = $fileName .".csv"; // CSV
            $csvPath = $param['facilityNumber'] ."/invoices/". $targetDateYm ."/".$serviceDateYm."/".$csvFileName;

            // 伝送請求情報を更新
            $invioces = new Invoice;
            $invioces->where('id', '=', $param['id'])
                ->update([
                    'facility_number' => $param['facilityNumber'], // 事業所番号
                    'facility_user_count' => $param['facilityUserCount'],// 対象利用者数→請求情報に含まれる利用者の人数
                    'billing_amount' => $param['billingAmount'], // 請求額（合計）
                    'csv' => $csvPath, // CSVパス
                    'target_date' => $param['targetDate'], // 処理対象年月の1日
                    'service_date' => $param['serviceDate'], // サービス提供年月の1日
                    'status' => 0
                ]);

            // 請求情報IDをキーにして元の伝送請求詳細をDBから削除
            $invoiceDetailDelete = new InvoiceDetail;
            $invoiceDetailDelete->where('invoice_id', '=', $param['id'])->delete();

            // 伝送請求詳細をDBに再度登録
            foreach ($param['facilityUserId'] as $facilityUserId) {
                $invoiceDetail = new InvoiceDetail;
                $invoiceDetail->invoice_id = $param['id'];
                $invoiceDetail->facility_user_id = $facilityUserId;
                $invoiceDetail->status = 1;
                $invoiceDetail->save();
            }

            // 伝送請求情報と伝送請求詳細の両方がDBに更新された時点でコミット
            DB::commit();
        } catch (Exception $e) {
            // 伝送請求情報、伝送請求詳細いずれかでDB登録に失敗したらロールバック
            DB::rollback();
            report($e);
            return [
                "notice" => -1,
                "error" => $e->getMessage()
            ];
        }

        return [
            "csvPath" => $csvPath
        ];
    }
}
