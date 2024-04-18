<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class Approval extends Model
{
    protected $table = 'i_approvals';
    protected $primaryKey = "approval_id";
    protected $connection = 'mysql';

    protected $guarded = [
        'approval_id',
        'facility_user_id',
        'facility_id',
    ];

    const UNINSURED_APPROVAL_TYPE = 1;

    // 共通ロジックとしてSetter/Getter用意しておく

    // Setter
    //  レコードが存在しなかった場合、新規作成する
    public static function setApproval($facilityId, $facilityUserId, $month, $approvalType, $applovalFlag): bool
    {
        try {
            $model = self::where("facility_id", $facilityId)
                ->where("facility_user_id", $facilityUserId)
                ->where("approval_type", $approvalType)
                ->where("month", $month)->first();

            if ($model) {
                $model->update(["approval_flag" => $applovalFlag]);
            } else {
                $value = [];
                $value["facility_id"] = $facilityId;
                $value['facility_user_id'] = $facilityUserId;
                $value['month'] = $month;
                $value['approval_type'] = $approvalType;
                $value['approval_flag'] = $applovalFlag;
                self::insert($value);
            }
        } catch (Exception $e) {
            report($e);
            return false;
        }
        return true;
    }

    // Getter
    //  レコードが存在しなかった場合、falseを返す
    public static function getApproval($facilityId, $facilityUserId, $month, $approvalType): bool
    {
        $bool = false;
        try {
            $model = self::where("facility_id", $facilityId)
                ->where("facility_user_id", $facilityUserId)
                ->where("approval_type", $approvalType)
                ->where("month", $month)->first();

            if ($model) {
                $bool = (bool)$model->approval_flag;
            }
        } catch (Exception $e) {
            report($e);
            return false;
        }
        return $bool;
    }
}
