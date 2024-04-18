<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\MakeInvoiceRequest;
use App\Service\GroupHome\InvoiceService;
use App\Service\GroupHome\NationalHealthBillingService;
use App\Utility\S3;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * 事業所IDから国保連請求データを作成する
     * @param Request key: facility_id, month, year
     */
    public function makeInvoice(MakeInvoiceRequest $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_ids,
            'facility_id' => $request->facility_id,
            'month' => $request->month,
            'year' => $request->year
        ];

        $invoiceService = new InvoiceService();

        // 請求データを作成し、伝送請求情報と伝送請求詳細をDBに登録、ファイル名も生成する
        $billingData = $invoiceService->getBenefitBilling($params);

        // 請求データを作成時にエラーハンドリングが出力された場合は処理を終了する
        if (isset($billingData['error'])) {
            return [
                "message" => $billingData['error']
            ];
        }

        // 請求データのcsvを作成（既存の処理を流用）
        $csvData = '';
        try {
            $nhBillingService = new NationalHealthBillingService();
            $csvData = $nhBillingService->getCsvDataWithFacilityUserIds(
                $request->facility_user_ids,
                1,
                $request->year,
                $request->month
            );
        } catch (\Exception $e) {
            report($e);
            return [
                "message" => $e->getMessage()
            ];
        }

        // 作成したCSVファイルをS3に保存
        try {
            $csvPath = $billingData['csvPath'];
            S3::saveInvoiceCsv($csvPath, $csvData);
        } catch (\Exception $e) {
            report($e);
            return [
                "message" => $e->getMessage()
            ];
        }

        // 接続確認のためレスポンスの値はテストのものとする
        return [
            "message" => "伝送用国保連請求データを作成しました。\n伝送情報画面より送信を完了させてください。"
        ];
    }
}
