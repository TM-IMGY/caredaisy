<?php

namespace App\Utility;

use Illuminate\Support\Facades\Storage;
use Exception;

class S3
{
    // S3に保存されているファイル名を定数に入れる
    const OPERATION_MANUAL_PATH = 'manual.pdf';
    const TRANSMISSION_MANUAL_PATH = 'transmission_manual.pdf';

    /**
     * 介護計画書PDFのPATHを取得する
     *
     * @param  string $servicePlanId
     * @return string
     */
    private static function getFilenameOfServicePlanPdf($servicePlanId)
    {
        $path = "care-plan-pdf/${servicePlanId}_service_plan.pdf";
        return $path;
    }

    /**
     * S3の介護計画書の内容を取得する
     *
     * @param string $servicePlanId
     * @return array
     */
    public static function getRawDataOfServicePlanPdf($servicePlanId)
    {
        $filename = self::getFilenameOfServicePlanPdf($servicePlanId);
        return self::getRawData($filename);
    }

    /**
     * S3から操作マニュアル・ダウンロードしてくる
     *
     * */
    public static function getOperationManualPdf()
    {
        return self::getRawData(self::OPERATION_MANUAL_PATH);
    }

    /**
     * S3から伝送マニュアル・ダウンロードしてくる
     *
     * */
    public static function getTransmissionManualPdf()
    {
        return self::getRawData(self::TRANSMISSION_MANUAL_PATH);
    }

    /**
     * S3から指定のファイルもらってくる
     *
     * */
    public static function getRawData($filename)
    {
        return Storage::disk('s3')->get($filename);
    }
    /**
     * S3から指定のファイルが存在するか確認する
     *
     * */
    public static function existData($filename)
    {
        return Storage::disk('s3')->exists($filename);
    }
    /**
     * S3から指定のファイルをダウンロード
     *
     * */
    private static function getDownloadData($filename)
    {
        return Storage::disk('s3')->download($filename);
    }
    /**
     * S3にファイルを保存する
     */
    public static function putFile($path, $data)
    {
        Storage::disk('s3')->put($path, $data);
    }

    /**
     * S3の介護計画書連票を保存する
     * @param string $servicePlanId
     * @param string $pdfData
     * @return string 保存したファイルのパス
     */
    public static function saveServicePlanPdf($servicePlanId, $pdfData): string
    {
        // ファイルのパスを取得する
        $pdfPath = self::getFilenameOfServicePlanPdf($servicePlanId);

        // ファイルを保存する
        \Storage::disk('s3')->put($pdfPath, $pdfData);
        return $pdfPath;
    }

    /**
     * S3に国保連請求データのCSVを保存する
     * @param string $filePath
     * @param string $csvData
     * @return string 保存したファイルのパス
     */
    public static function saveInvoiceCsv($filePath, $csvData): string
    {
        // S3にCSVファイルを保存する（ファイルが既に存在していたら上書き保存）
        \Storage::disk('s3')->put($filePath, $csvData);
        return $filePath;
    }

    /**
     * S3に保存されたデータをダウンロード
     * @param string $filePath
     * @return  ファイルの内容
     */
    public static function getFile($filePath)
    {
        if (self::existData($filePath)) {
            return self::getDownloadData($filePath);
        } else {
            throw new Exception('File Not Found.');
        }
    }
}
