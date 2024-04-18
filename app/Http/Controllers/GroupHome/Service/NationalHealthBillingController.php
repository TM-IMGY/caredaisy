<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\NationalHealthBillingDownloadCsvRequest;
use App\Http\Requests\GroupHome\Service\NhbDownloadCsvWithFacilityUserIdsRequest;
use App\Models\Facility;
use App\Service\GroupHome\NationalHealthBillingService;

class NationalHealthBillingController extends Controller
{
    /**
     * 事業所IDから国保連請求csvデータを返す
     * @param NationalHealthBillingDownloadCsvRequest key: facility_id, month, year
     */
    public function downloadCsv(NationalHealthBillingDownloadCsvRequest $request)
    {
        $param = [
            'facility_id' => $request->facility_id,
            'month' => $request->month,
            'year' => $request->year
        ];

        // csvのデータを取得
        $csvData = '';
        try {
            $nhBillingService = new NationalHealthBillingService();
            $csvData = $nhBillingService->getCsvDataWithFacilityId($param);
        } catch (\Exception $th) {
            report($th);
            return redirect()->route('group_home.result_info');
        }

        // ファイル名
        $fileNameYm = (new \DateTimeImmutable($param['year'] .'-'. $param['month']))->format('Ym');
        $fileName = "CD${fileNameYm}";

        // responseヘルパはResponseFactoryを返す
        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"'
        ]);
    }

    /**
     * 施設利用者IDのリストから国保連請求csvデータを返す
     * @param NhbDownloadCsvWithFacilityUserIdsRequest $request
     */
    public function downloadCsvWithFacilityUserIds(NhbDownloadCsvWithFacilityUserIdsRequest $request)
    {
        // csvデータを取得する。
        $nhBillingService = new NationalHealthBillingService();
        $csvData = $nhBillingService->getCsvDataWithFacilityUserIds(
            $request->facility_user_ids,
            0,
            $request->year,
            $request->month
        );

        // ファイル名
        $fileNameYm = (new \DateTimeImmutable($request->year .'-'. $request->month))->format('Ym');
        $fileName = "CD${fileNameYm}";

        // responseヘルパはResponseFactoryを返す
        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"'
        ]);
    }
}
