<?php

namespace App\Service\Api\Invoice;

use App\Lib\Common\Consts;
use App\Models\Invoice;
use App\Models\SlackNotificationFlag;
use App\Utility\SlackUtility;
use DB;
use Exception;
use Log;

class InvoiceService
{
    // 請求情報処理結果
    public const RESULT_OK                      = 1;
    public const RESULT_NG                      = 2;

    // 請求情報ステータス
    public const INVOICE_STATUS_PREPARE         = 0; // 未送信
    public const INVOICE_STATUS_SENDING         = 1; // 伝送中
    public const INVOICE_STATUS_READY           = 2; // 受付中
    public const INVOICE_STATUS_IN_PROGRESS     = 3; // 処理中
    public const INVOICE_STATUS_FINISHED        = 4; // 完了
    public const INVOICE_STATUS_FORMAT_ERROR    = 5; // 様式エラー
    public const INVOICE_STATUS_ERROR           = 6; // 外部エラー
    public const INVOICE_STATUS_CANCEL_REQUEST  = 7; // 取消依頼中
    public const INVOICE_STATUS_IN_CANCELING    = 8; // 取消中
    public const INVOICE_STATUS_CANCELED        = 9; // 取消完了

    // 連絡文書種別
    public const DOCUMENT_TYPE_DOCUMENT         = 1; // 通知文書
    public const DOCUMENT_TYPE_NOTIFICATION     = 2; // お知らせ

    // 請求情報一覧取得モード
    public const INVOICE_LIST_MODE_FOR_TRANSFER = 1; // 伝送待ち
    public const INVOICE_LIST_MODE_IN_PROGRESS  = 2; // 処理中
    public const INVOICE_LIST_MODE_FOR_CANCEL   = 3; // 削除依頼中

    // 署名送信の基本ステータス
    public const BASIC_STATUS_ARRIVAL      = '5C01'; // 到達完了
    public const BASIC_STATUS_UNION_ARRIVAL= '5C02'; // 連合会到達
    public const BASIC_STATUS_ACCEPTING    = '5C03'; // 受付中
    public const BASIC_STATUS_FORMAT_ERROR = '5C04'; // 様式エラー
    public const BASIC_STATUS_READY        = '5C05'; // 受付完了
    public const BASIC_STATUS_SENT         = '5C06'; // 送信完了
    public const BASIC_STATUS_RETURNED     = '5C07'; // 払戻通知処理完了
    public const BASIC_STATUS_PAID         = '5C08'; // 支払通知処理完了
    public const BASIC_STATUS_FINISHED     = '5C09'; // 完了

    public const BASIC_STATUS_ERROR_A      = '5C10'; // 到達エラー ※通常は、送信時にしか起こらないのでcaredaisy的には送信時に外部エラーになって終了なので伝送ステータス取得では起こりえない
    public const BASIC_STATUS_ERROR_N      = '5C11'; // 伝送(N系)エラー
    public const BASIC_STATUS_ERROR_G      = '5C12'; // 外部(G系)エラー

    // 連絡文書ステータス
    public const DOCUMENT_STATUS_ENABLED  = 0;
    public const DOCUMENT_STATUS_DISABLED = 1;

    // 署名送信サブステータス
    public const SUB_STATUS_CANCELING = 2; // 取消中
    public const SUB_STATUS_CANCELED  = 9; // 取消完了

    // 署名送信の基本ステータス名
    public const BASIC_STATUS_NAME_FORMAT_ERROR = '様式エラー有';
    public const BASIC_STATUS_NAME_READY        = '受付完了';

    /**
     * 事業所一覧と通知文書(document_type=1)の最終取得文書番号を返す
     *
     * @return  array
     */
    public function generateAllFacilitiesWithDocumentCode()
    {
        $list = [];

        // 事業所毎の最終取得文書番号を取得するサブクエリ
        $sub = DB::table('i_return_documents')
            ->select([
                'facility_number',
                DB::raw('MAX(document_code) AS last_document_code'),
            ])
            ->where('document_type', self::DOCUMENT_TYPE_DOCUMENT)
            ->groupBy('facility_number');

        // 事業所一覧に最終取得文書番号を連結
        $result = DB::table('i_facilities AS if')
            ->leftJoinSub($sub, 'sub', 'if.facility_number', 'sub.facility_number')
            ->select([
                'if.facility_number',
                'sub.last_document_code',
            ])
            ->where('if.allow_transmission', Consts::VALID)
            ->orderBy('if.facility_number')
            ->get();

        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $list[] = $row;
            }
        }

        return $list;
    }

    /**
     * 事業所一覧と通知文書(document_type=1)の最終取得文書番号を返す(v1)
     * @param   integer $terminalNumber
     * @return  array
     */
    public function generateAllFacilitiesWithDocumentCodeV1($terminalNumber)
    {
        $list = [];

        // 事業所毎の最終取得文書番号を取得するサブクエリ
        $subIReturnDocuments = DB::table('i_return_documents')
            ->select([
                'facility_number',
                DB::raw('MAX(document_code) AS last_document_code'),
            ])
            ->where('document_type', self::DOCUMENT_TYPE_DOCUMENT)
            ->groupBy('facility_number');

        $subIFacilities = DB::table('i_facilities AS if')
            // 伝送利用事業所情報(tx_facilities)に事業所マスタのIDが登録されている
            ->join('tx_facilities AS tf', 'if.facility_id', '=', 'tf.facility_id')
            ->select([
                'if.facility_number',
            ])
            // 伝送利用事業所情報(tx_facilities)に入力パラメータの伝送端末番号が登録されている
            ->whereIn('tf.terminal_number', [$terminalNumber])
            ->groupBy('if.facility_number');

        // 事業所一覧に最終取得文書番号を連結
        $result = DB::table('i_facilities AS if')
            ->joinSub($subIFacilities, 'sif', 'if.facility_number', 'sif.facility_number')
            ->leftJoinSub($subIReturnDocuments, 'sird', 'if.facility_number', 'sird.facility_number')
            ->select([
                'if.facility_number',
                'sird.last_document_code',
            ])
            // 事業所マスタ(i_facilities)の伝送許可(allow_transmission)が1
            ->where('if.allow_transmission', Consts::VALID)
            ->orderBy('if.facility_number')
            ->get();

        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $list[] = $row;
            }
        }

        return $list;
    }

    /**
     * 事業所番号でグループ化した請求情報取得する
     * @param   integer $target
     * @param   string  $ym
     * @return  array
     */
    public function generateInvoices($target, $ym)
    {
        $list = [];

        if (preg_match('/^([0-9]{4})([0-9]{2})$/', $ym, $matches)) {
            $targetDate = "{$matches[1]}/{$matches[2]}/01";
        } else {
            // 処理対象年月省略時は全範囲のステータスを取得する
            $targetDate = null;
        }

        switch ($target) {
            case self::INVOICE_LIST_MODE_FOR_TRANSFER:
                $statuses = [
                    self::INVOICE_STATUS_SENDING,
                ];
                break;
            case self::INVOICE_LIST_MODE_IN_PROGRESS:
                $statuses = [
                    self::INVOICE_STATUS_READY,
                    self::INVOICE_STATUS_IN_PROGRESS,
                    self::INVOICE_STATUS_FORMAT_ERROR,
                    self::INVOICE_STATUS_IN_CANCELING,
                ];
                break;
            case self::INVOICE_LIST_MODE_FOR_CANCEL:
                $statuses = [
                    self::INVOICE_STATUS_CANCEL_REQUEST,
                ];
                break;

            default:
                throw new Exception("illegal target ({$target})");
        }

        $query = DB::table('i_invoices')
            ->select([
                'facility_number',
                'id',
                'accept_code',
                'cancel_code',
                'csv',
            ])
            ->whereIn('status', $statuses)
            ->orderBy('facility_number')
            ->orderBy('id');

        if (!is_null($targetDate)) {
            $query->where('target_date', $targetDate);
        }

        $result = $query->get();

        // レスポンスを生成する（JSONエンコードは呼び出し元で行う）
        $list = [];
        if (!$result->isEmpty()) {
            $currentFacilityNumber = null;
            $currentIndex = 0;
            foreach ($result as $row) {
                $facilityNumber = $row->facility_number;
                if ($facilityNumber !== $currentFacilityNumber) {
                    $list[] = [
                        'facility_number' => $facilityNumber,
                        'list' => [],
                    ];
                    $currentIndex = count($list) - 1; // 更新する事業所のindex
                    $currentFacilityNumber = $facilityNumber;
                }

                $list[$currentIndex]['list'][] = [
                    'id'          => $row->id,
                    'accept_code' => $row->accept_code,
                    'cancel_code' => $row->cancel_code,
                    'csv'         => $row->csv,
                ];
            }
        }

        return $list;
    }

    /**
     * 事業所番号でグループ化した請求情報取得する(v1)
     * @param   integer $terminalNumber
     * @param   integer $target
     * @param   string  $ym
     * @return  array
     */
    public function generateInvoicesV1($terminalNumber, $target, $ym)
    {
        $list = [];

        if ($ym) {
            $year = mb_substr($ym, 0, 4);
            $month = mb_substr($ym, 4, 2);
            $date = '01';
            $targetDate = $year . '/' . $month . '/' . $date;
        } else {
            // 処理対象年月省略時は全範囲のステータスを取得する
            $targetDate = null;
        }

        switch ($target) {
            case self::INVOICE_LIST_MODE_FOR_TRANSFER:
                $statuses = [
                    self::INVOICE_STATUS_SENDING,
                ];
                break;
            case self::INVOICE_LIST_MODE_IN_PROGRESS:
                $statuses = [
                    self::INVOICE_STATUS_READY,
                    self::INVOICE_STATUS_IN_PROGRESS,
                    self::INVOICE_STATUS_FORMAT_ERROR,
                    self::INVOICE_STATUS_IN_CANCELING,
                ];
                break;
            case self::INVOICE_LIST_MODE_FOR_CANCEL:
                $statuses = [
                    self::INVOICE_STATUS_CANCEL_REQUEST,
                ];
                break;

            default:
                throw new Exception("illegal target ({$target})");
        }

        $sub = DB::table('i_facilities AS if')
            // 伝送利用事業所情報(tx_facilities)に事業所マスタのIDが登録されている
            ->join('tx_facilities AS tf', 'if.facility_id', '=', 'tf.facility_id')
            ->select([
                'if.facility_number',
            ])
            // 伝送利用事業所情報(tx_facilities)に入力パラメータの伝送端末番号が登録されている
            ->whereIn('tf.terminal_number', [$terminalNumber])
            ->groupBy('if.facility_number');

        $query = DB::table('i_invoices AS ii')
            ->joinSub($sub, 'if', 'ii.facility_number', 'if.facility_number')
            ->select([
                'ii.facility_number',
                'ii.id',
                'ii.accept_code',
                'ii.cancel_code',
                'ii.csv',
            ])
            ->whereIn('status', $statuses)
            ->orderBy('facility_number')
            ->orderBy('id');

        if (!is_null($targetDate)) {
            $query->where('target_date', $targetDate);
        }

        $result = $query->get();

        // レスポンスを生成する（JSONエンコードは呼び出し元で行う）
        $list = [];
        if ($result->isEmpty()) {
            return $list;
        }

        $currentFacilityNumber = null;
        $currentIndex = 0;
        foreach ($result as $row) {
            $facilityNumber = $row->facility_number;
            if ($facilityNumber !== $currentFacilityNumber) {
                $list[] = [
                    'facility_number' => $facilityNumber,
                    'list' => [],
                ];
                $currentIndex = count($list) - 1; // 更新する事業所のindex
                $currentFacilityNumber = $facilityNumber;
            }

            $list[$currentIndex]['list'][] = [
                'id'          => $row->id,
                'accept_code' => $row->accept_code,
                'cancel_code' => $row->cancel_code,
                'csv'         => $row->csv,
            ];
        }
        return $list;
    }

    /**
     * 請求情報を更新する
     * @param   array   $invoices
     * @return  array
     */
    public function updateInvoices($invoices)
    {
        // 請求情報ステータス更新API実行日時
        $updateInvoicesTime = date("Y/m/d H:i");

        // 連携される請求情報の必須項目
        static $required = [
            'id',
            'result',
        ];

        // 請求情報テーブルから取得するカラム
        static $columns = [
            'id',
            'accept_code',
            'cancel_code',
            'basic_status',
            'sub_status',
            'message_id',
            'message',
            'status',
            'facility_number',
            'target_date',
            'service_date'
        ];

        if (!is_array($invoices)) {
            throw new Exception('illegal parameter');
        }

        if (empty($invoices)) {
            throw new Exception('empty list');
        }

        // 不適格なレコードは除外する
        $invoices = collect($invoices)->filter(function ($invoice) use ($required) {
            foreach ($required as $field) {
                if (empty($invoice[$field])) {
                    return false;
                }
            }
            return true;
        });

        // 連携された請求情報の内容を反映させる
        foreach ($invoices as $invoice) {
            $id = $invoice['id'];
            $record = Invoice::find($id, $columns);

            if (empty($record)) {
                continue;
            }

            $record->fill([
                'basic_status' => $invoice['basic_status'],
                'sub_status' => $invoice['sub_status'],
                'message_id' => $invoice['message_id'],
                'message' => $invoice['message'],
            ]);

            // 未設定の accept_code がセットされた場合は受付中に移行
            if (empty($record->accept_code) && !empty($invoice["accept_code"])) {
                $record->status = self::INVOICE_STATUS_READY;
                $record->accept_code = $invoice["accept_code"];
                if (!$record->basic_status) {
                    $record->basic_status = self::BASIC_STATUS_ARRIVAL;
                }
            }

            $isCanceled = false;
            // 未設定の cancel_code がセットされた場合は取消中に移行
            if (empty($record->cancel_code) && !empty($invoice["cancel_code"])) {
                $record->status = self::INVOICE_STATUS_IN_CANCELING;
                $record->cancel_code = $invoice["cancel_code"];
                $isCanceled = true;
            }


            if (!empty($invoice["sub_status"])) {
                // 署名送信のサブステータスに 9:取消完了 がセットされた場合（取消）
                if ($invoice['sub_status'] == self::SUB_STATUS_CANCELED) {
                    $record->status = self::INVOICE_STATUS_CANCELED;
                }
                $isCanceled = true;
            }

            $notificationFlag = SlackNotificationFlag::firstOrNew(['invoice_id' => $id]);

            $basicStatus = null;
            if (!$isCanceled) {
                // 署名送信の基本ステータスがセットされた場合の状態遷移
                if (!empty($invoice["basic_status"])) {
                    switch ($invoice['basic_status']) {
                        case self::BASIC_STATUS_FORMAT_ERROR:
                            $record->status = self::INVOICE_STATUS_FORMAT_ERROR;
                            $basicStatus = self::BASIC_STATUS_NAME_FORMAT_ERROR;
                            break;
                        case self::BASIC_STATUS_ARRIVAL:
                        case self::BASIC_STATUS_UNION_ARRIVAL:
                        case self::BASIC_STATUS_ACCEPTING:
                            $record->status = self::INVOICE_STATUS_READY;
                            break;
                        case self::BASIC_STATUS_READY:
                            $record->status = self::INVOICE_STATUS_READY;
                            $basicStatus = self::BASIC_STATUS_NAME_READY;
                            break;
                        case self::BASIC_STATUS_SENT:
                        case self::BASIC_STATUS_RETURNED:
                        case self::BASIC_STATUS_PAID:
                            $record->status = self::INVOICE_STATUS_IN_PROGRESS;
                            break;
                        case self::BASIC_STATUS_FINISHED:
                            $record->status = self::INVOICE_STATUS_FINISHED;
                            break;

                            // 国保連側のエラーはcaredaisyの外部エラーとして扱う
                        case self::BASIC_STATUS_ERROR_A:
                        case self::BASIC_STATUS_ERROR_N:
                        case self::BASIC_STATUS_ERROR_G:
                            $record->status = self::INVOICE_STATUS_ERROR;
                            break;
                    }
                }

                // 処理対象請求IDの基本ステータスが初めて「様式エラー」、「受付完了」になった場合の処理
                if (empty($notificationFlag->exists) && !empty($basicStatus)) {
                    $formatTargetDate = date('Y/m/d', strtotime($record->target_date));
                    $formatServiceDate = date('Y/m/d', strtotime($record->service_date));

                    $msg = <<<__MASSAGE__
                    操作種別：伝送ステータス取得
                    時刻：{$updateInvoicesTime}
                    状態：正常
                    基本ステータス：{$basicStatus}
                    サブステータス：{$invoice["sub_status"]}
                    請求情報ID：{$invoice["id"]}
                    事業所番号：{$record->facility_number}
                    処理対象年月：{$formatTargetDate}
                    サービス提供年月：{$formatServiceDate}
                    __MASSAGE__;

                    $notificationFlag->fill([
                        'invoice_status' => $record->status['id'],
                        'invoice_basic_status' => $invoice["basic_status"],
                        'invoice_sub_status' =>  $invoice["sub_status"]
                    ]);

                    SlackUtility::notification($msg);
                }
            }


            DB::beginTransaction();
            try {
                $record->save();
                if (!$notificationFlag->exists && !empty($basicStatus)) {
                    $notificationFlag->save();
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw new Exception($e);
            }
        }
    }


    /**
     * 連絡文書情報を更新する
     * @param   array   $documents
     * @return  array
     */
    public function updateDocuments($documents)
    {
        // 連携される連絡文書情報の必須項目
        static $required = [
            'target_date',
            'document_code',
            'document_type',
            'title',
            'published_at',
            'facility_number',
            'result',
        ];
        // 連携される連絡文書情報の項目
        static $names = [
            'target_date',
            'document_code',
            'document_type',
            'title',
            'content',
            'published_at',
            'download_file',
            'facility_number',
            'message_id',
            'message',
            'result',
        ];

        $error = null;
        if (!is_array($documents)) {
            throw new Exception('illegal parameter');
        }

        if (empty($documents)) {
            throw new Exception('empty list');
        }

        foreach ($documents as $document) {
            // 連絡文書毎の必須項目がセットされているか確認する
            foreach ($required as $name) {
                if (!array_key_exists($name, $document)) {
                    $error = "連絡文書情報に{$name}がセットされていません";
                    throw new Exception($error);
                }
            }
        }

        // CHANGED: 更新用ロジックを削除し、更新の役割を新規追加用ロジックと併用。
        // お知らせの時は文書番号が、通知文書の時は文書番号と通し番号が
        // 一致するレコードが存在する場合、エラーを返す
        foreach ($documents as $document) {
            $query = DB::table('i_return_documents')
                ->where('document_code', $document['document_code']);

            if ($document['document_type'] === self::DOCUMENT_TYPE_DOCUMENT) {
                $query->where('index', $document['index']);
            }

            $result = $query->first();

            if (!empty($result)) {
                $error = "文書番号:{$document['document_code']}は既に登録されています";
                throw new Exception($error);
            }
        }

        $new = [];
        foreach ($documents as $document) {
            // 新規追加および更新するための通知文書
            $data = [];

            if ($document['document_type'] === self::DOCUMENT_TYPE_DOCUMENT) {
                array_splice($names, 5, 0, 'index');
            }

            foreach ($names as $name) {
                if ($name == 'result') {
                    continue;
                }
                $data[$name] = $document[$name];
            }
            // 自動で取得した連絡文書をそのまま公開しても問題ない品質になったので
            // 非公開状態で取り込む処理を無効化する。
            $data['status'] = self::DOCUMENT_STATUS_ENABLED;
            $new[] = $data;
        }

        DB::table('i_return_documents')->insert($new);
    }

    /**
     * 添付ファイル情報を更新する
     * @param   array   $documents
     */
    public function updateAttachments($documents)
    {
        // 連携される添付ファイル情報の必須項目
        // 最初に呼ばれるときだけ初期化する
        static $required = [
            'document_code',
            'index',
            'document_name',
        ];

        $error = null;
        if (!is_array($documents)) {
            throw new Exception('パラメータの形式が正しくありません');
        }

        foreach ($documents as $document) {
            // 連絡文書毎の必須項目がセットされているか確認する
            foreach ($required as $name) {
                if (!array_key_exists($name, $document) || empty($document[$name])) {
                    $error = "連絡文書情報に{$name}がセットされていません";
                    throw new Exception($error);
                }
            }
        }

        foreach ($documents as $document) {
            $returnDocument = DB::table('i_return_documents')
                ->where('document_code', $document['document_code'])
                ->first();

            // i_return_documentsにdocument_codeがある場合
            if (!empty($returnDocument)) {
                $returnAttachment = DB::table('i_return_attachments')
                    ->where('document_code', $document['document_code'])
                    ->where('index', $document['index'])
                    ->first();

                if (!empty($returnAttachment)) {
                    throw new Exception("お知らせ番号:{$document['document_code']}は既に登録されています");
                }
            } else {
                throw new Exception('i_return_documentsに対象のdocument_codeがありません');
            }
        }

        DB::transaction(function () use ($documents) {
            DB::table('i_return_attachments')->insert($documents);
        });
    }
}
