<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WeeklyServiceCategory extends Model
{
    public const COMMON_FACILITY_ID = 0; // 共通マスタ
    public const TYPE_GENERAL = 0; // 通常サービス
    public const TYPE_EVERYDAY = 8; // 日常生活上の活動
    public const TYPE_NOT_WEEKLY = 9; // 週単位以外のサービス

    protected $connection = 'mysql';
    protected $guarded = [
        'id',
    ];

    /**
     * relations
     */
    public function facility()
    {
        return $this->hasOne(Facility::class, 'facility_id', 'facility_id');
    }

    public function weeklyServices()
    {
        return $this->hasMany(WeeklyService::class);
    }

    public function scopeIsGeneral($query)
    {
        return $query->where('type', self::TYPE_GENERAL);
    }

    public function scopeIsEveryday($query)
    {
        return $query->where('type', self::TYPE_EVERYDAY);
    }

    public function scopeIsNotWeekly($query)
    {
        return $query->where('type', self::TYPE_NOT_WEEKLY);
    }

    public function scopecommonOrFacilityIs($query, $facility_id)
    {
        return $query->where(function ($q) use ($facility_id) {
            $q->where('facility_id', self::COMMON_FACILITY_ID)
                ->orWhere('facility_id', $facility_id);
        });
    }

    // seederにて用意されている前提
    public static function retrieveOtherCategory()
    {
        return self::where('facility_id', self::COMMON_FACILITY_ID)
            ->where('description', 'その他')
            ->isGeneral()
            ->firstOrFail();
    }
}
