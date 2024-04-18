<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'i_invoices';
    protected $connection = 'mysql';

    protected $primaryKey = 'id';

    protected $guarded = [
      'id',
    ];

    // keyは定義されたステータス番号
    public const STATUS_NAME = [
        0 => '未送信',
        1 => '伝送中',
        2 => '基本コードの決定に従う',
        3 => '基本コードの決定に従う',
        4 => '完了',
        5 => '様式エラー有',
        6 => '基本コードの決定に従う',
        7 => '取消依頼中',
        8 => '取消中',
        9 => '取消完了',
    ];

    // 複数のステータスが存在するステータス番号
    public const DECISION_BY_BASIC_STATUS= [2, 3, 6];

    public const BASIC_STATUS_NAME = [
        '5C01' => '到達完了',
        '5C02' => '連合会到達',
        '5C03' => '受付中',
        //'5C04' => '',
        '5C05' => '受付完了',
        '5C06' => '送信完了',
        '5C07' => '返戻通知処理完了',
        '5C08' => '支払通知処理完了',
        //'5C09' => '',
        '5C10' => '到達エラー',
        '5C11' => '伝送エラー',
        '5C12' => '外部エラー',
    ];

    public function getStatusAttribute($value)
    {
        if (!empty(self::STATUS_NAME[$value])) {
            // ステータスが基本コード毎に区分されている場合
            // ステータス番号&基本コードで定義されているステータスを返す
            if (in_array($value, self::DECISION_BY_BASIC_STATUS) && (int)$this->sub_status === 0) {
                return ['id' => $value, 'status_name' => self::BASIC_STATUS_NAME[$this->basic_status]];
            }
            // ステータス番号のみで定義されているステータスを返す
            return ['id' => $value, 'status_name' => self::STATUS_NAME[$value]];
        }
        return ['id' => $value, 'status_name' => 'ステータスなし'];
    }
}
