<?php

namespace App\Http\Controllers\GroupHome\TransmitInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GroupHome\TransmitInfo\GetInvoiceRequest;
use App\Http\Requests\GroupHome\TransmitInfo\SetFilterRequest;
use App\Http\Requests\GroupHome\TransmitInfo\GetDocumentRequest;
use App\Http\Requests\GroupHome\TransmitInfo\DeleteInvoiceRequest;
use App\Http\Requests\GroupHome\TransmitInfo\GetFileRequest;
use App\Http\Requests\GroupHome\TransmitInfo\GetReturnDocumentListRequest;
use App\Http\Requests\GroupHome\TransmitInfo\GetReturnDocumentRequest;
use App\Http\Requests\GroupHome\TransmitInfo\SetInvoiceRequest;
use App\Service\GroupHome\TransmitService;
use App\Utility\S3;
use App\Utility\SlackUtility;
use App\Models\ReturnDocument;
use App\Models\ReturnAttachment;
use Carbon\Carbon;
use Exception;
use Log;

class TransmitController extends Controller
{
    const FILENAME_EXTENSION_CSV = 'csv'; // csv拡張子

    const RESULT_OK = '正常';
    const RESULT_NG = 'エラー';

    public function index(){
        return view('group_home.transmit_info.transmit_info');
    }

    /**
     * 請求情報を取得
     */
    public function getInvoice(GetInvoiceRequest $request)
    {
        $transmitService = new TransmitService();
        return $transmitService->getInvoiceData($request);
    }
    /**
     * 履歴データの絞り込み処理
     */
    public function setFilter(SetFilterRequest $request)
    {
        $transmitService = new TransmitService();
        return $transmitService->getFilterData($request);
    }
    /**
     * 通知文書を取得
     */
    public function getReturnDocumentList(GetReturnDocumentListRequest $request)
    {
        $transmitService = new TransmitService();
        return $transmitService->getDocumentList($request);
    }
    /**
     * 通知文書を取得
     */
    public function getDocument(GetDocumentRequest $request)
    {
        $transmitService = new TransmitService();
        return $transmitService->getDocumentData($request);
    }
    /**
     * 請求情報の送信
     */
    public function sentInvoice(SetInvoiceRequest $request)
    {
        $transmitService = new TransmitService();
        $result = $transmitService->sentInvoice($request);

        $resultMsg = $result ? self::RESULT_OK : self::RESULT_NG;
        $now = Carbon::now()->format('Y/m/d H:i');
        $targetDate = Carbon::parse($request->targetDate)->format('Y/m/d');
        $serviceDate = Carbon::parse($request->serviceDate)->format('Y/m/d');

        $msg = <<<MASSAGE
        操作種別：送信
        時刻：{$now}
        状態：{$resultMsg}
        請求情報ID：{$request->id}
        事業所番号：{$request->facilityNumber}
        処理対象年月：{$targetDate}
        サービス提供年月：{$serviceDate}
        MASSAGE;

        SlackUtility::notification($msg);

        return $result;
    }
    /**
     * S3からダウンロード
     */
    public function getFileFromS3(GetFileRequest $request)
    {
        return S3::getFile($request->file_path);
    }

    /**
     * 連絡文書を取得(通知文書用)
     */
    public function getReturnDocument(GetReturnDocumentRequest $request)
    {
        // コントローラー側にロジックを直書きしているので
        // サービス側に今後移動予定
        $documentType = ReturnDocument::select('document_type')
        ->where('id', $request->id)
            ->first();

        // HACK:比較対象に文字列を指定しているので今後リファクタ予定
        if ($documentType['document_type'] === ReturnDocument::DOCUMENT_TYPE_NEWS) {
            $code = ReturnDocument::select('document_code')
            ->where('id', $request->id)
                ->first();
            $document = ReturnAttachment::select('download_file')
            ->where('document_code', $code->document_code)
                ->where("index", $request->index)
                ->first();

            return S3::getFile($document->download_file);
        } else {
            // 通知文書
            $filePath = ReturnDocument::select('download_file')
            ->where('id', $request->id)
                ->first();

            $transmitService = new TransmitService();

            // S3からcsvファイル取得
            $csvData = S3::getRawData($filePath['download_file']);
            $encoding = mb_convert_encoding($csvData, 'UTF-8', 'sjis-win');

            // レコード取得
            $records = explode("\r\n", $encoding);

            // 値取得
            foreach ($records as $csvStr) {
                $csvAry[] = str_getcsv($csvStr);
            }

            // データレコードの値取得
            $keys = array_keys(array_column($csvAry, 0), '2');
            foreach ($keys as $key) {
                $dataRecordAry[] = $csvAry[$key];
            };

            $extension = (pathinfo($filePath['download_file'], PATHINFO_EXTENSION));

            // 確認日時更新
            ReturnDocument::updateCheckoutTime($request->id);
            // コントロールレコードのデータ種別ごとにパース
            // いずれのデータ種別にも当てはまらなかった場合はcsvのまま出力
            $parsed = null;
            if ($extension === self::FILENAME_EXTENSION_CSV) {
                switch ($csvAry[0][4]) {
                    case '721':
                        $parsed = $transmitService->parseType721($dataRecordAry);
                        // return $transmitService->makePDF($parsed);
                        break;
                    case '723':
                        $parsed = $transmitService->parseType723($dataRecordAry);
                        // return $transmitService->makePDF($parsed);
                        break;
                    case '741':
                        $parsed = $transmitService->parseType741($dataRecordAry);
                        // return $transmitService->makePDF($parsed);
                        break;
                    case '751':
                        $parsed = $transmitService->parseType751($dataRecordAry);
                        break;
                    case '752':
                    case '755':
                        $parsed = $transmitService->parseTypeItems($dataRecordAry);
                        // return $transmitService->makePDF($parsed);
                        break;
                }

                if (!is_null($parsed)) {
                    return  $transmitService->makePDF($parsed)->inline();
                }
            }

            return S3::getFile($filePath['download_file']);
        }
    }

    /**
     * 送信取消対象に更新
     */
    public function cancelTransmit(Request $request)
    {
        $transmitService = new TransmitService();
        $result = $transmitService->cancelTransmit($request);

        $resultMsg = $result ? self::RESULT_OK : self::RESULT_NG;
        $now = Carbon::now()->format('Y/m/d H:i');
        $targetDate = Carbon::parse($request->targetDate)->format('Y/m/d');
        $serviceDate = Carbon::parse($request->serviceDate)->format('Y/m/d');

        $msg = <<<MASSAGE
        操作種別：取消
        時刻：{$now}
        状態：{$resultMsg}
        請求情報ID：{$request->id}
        事業所番号：{$request->facilityNumber}
        処理対象年月：{$targetDate}
        サービス提供年月：{$serviceDate}
        MASSAGE;

        SlackUtility::notification($msg);

        return $result;
    }

    /**
     * 請求データ削除
     */
    public function deleteInvoice(DeleteInvoiceRequest $request)
    {
        $transmitService = new TransmitService();
        return $transmitService->deleteInvoice($request);
    }

    /**
     * 伝送期間判定
     */
    public function checkTransmitPeriod()
    {
        $transmitService = new TransmitService();
        return $transmitService->checkTransmitPeriod();
    }
}
