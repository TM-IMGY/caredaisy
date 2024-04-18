<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReturnDocument extends Model
{
    protected $table = 'i_return_documents';
    protected $connection = 'mysql';
    protected $guarded = ['id'];

    // getDocumentTypeAttributeの影響により文字列になってしまうため、
    // 一旦、定数を文字列に変換しております。
    public const DOCUMENT_TYPE_NOTIFICATION_DOCUMENT = '通知文書';
    public const DOCUMENT_TYPE_NEWS = 'お知らせ';

    public function facility()
    {
        return $this->hasOne('App\Models\Facility');
    }

    public function returnAttachment()
    {
        return $this->hasMany('App\Models\ReturnAttachment', 'document_code', 'document_code');
    }


    public function getDocumentTypeAttribute($value)
    {
        switch ($value) {
            case 1:
                return '通知文書';
            case 2:
                return 'お知らせ';
        }
    }

    public static function updateCheckoutTime($id)
    {
        $now = Carbon::now()->format('Y/m/d H:i:s');

        $updated = [
            'checked_at' => $now
        ];
        $updateResult = ReturnDocument::where('id', $id)->first()->update($updated);
        return true;
    }
}
