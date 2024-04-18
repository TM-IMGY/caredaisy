<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * 施設利用者情報の操作に責任を持つクラス。
 */
class FacilityUser extends Model
{
    protected $connection = 'confidential';
    protected $guarded = ['facility_user_id'];
    protected $table = 'i_facility_users';
    protected $primaryKey = "facility_user_id";

    public function userFacilityServiceInformation()
    {
        return $this->hasMany('App\Models\UserFacilityServiceInformation', 'facility_user_id', 'facility_user_id');
    }

    public function i_user_benefit_informations()
    {
        return $this->hasMany('App\Models\UserBenefitInformation');
    }

    public function i_user_facility_informations()
    {
        return $this->hasMany('App\Models\UserFacilityInformation', 'facility_user_id', 'facility_user_id');
    }

    /**
     * 認定情報を取得する
     */
    public function careInformations()
    {
        return $this->hasMany('App\Models\UserCareInformation', 'facility_user_id', 'facility_user_id');
    }

    // 各種復号化
    public function getInsurerNoAttribute()
    {
        return isset($this->attributes['insurer_no']) ? Crypt::decrypt($this->attributes['insurer_no']) : null;
    }

    public function getInsuredNoAttribute()
    {
        return isset($this->attributes['insured_no']) ? Crypt::decrypt($this->attributes['insured_no']) : null;
    }

    public function getLastNameAttribute()
    {
        return isset($this->attributes['last_name']) ? Crypt::decrypt($this->attributes['last_name']) : null;
    }

    public function getFirstNameAttribute()
    {
        return isset($this->attributes['first_name']) ? Crypt::decrypt($this->attributes['first_name']) : null;
    }

    public function getLastNameKanaAttribute()
    {
        return isset($this->attributes['last_name_kana']) ? Crypt::decrypt($this->attributes['last_name_kana']) : null;
    }

    public function getFirstNameKanaAttribute()
    {
        return isset($this->attributes['first_name_kana']) ? Crypt::decrypt($this->attributes['first_name_kana']) : null;
    }

    public function getPostalCodeAttribute()
    {
        return isset($this->attributes['postal_code']) ? Crypt::decrypt($this->attributes['postal_code']) : null;
    }

    public function getLocation1Attribute()
    {
        return isset($this->attributes['location1']) ? Crypt::decrypt($this->attributes['location1']) : null;
    }

    public function getLocation2Attribute()
    {
        return isset($this->attributes['location2']) ? Crypt::decrypt($this->attributes['location2']) : null;
    }

    public function getPhoneNumberAttribute()
    {
        return isset($this->attributes['phone_number']) ? Crypt::decrypt($this->attributes['phone_number']) : null;
    }

    public function getCellPhoneNumberAttribute()
    {
        return isset($this->attributes['cell_phone_number']) ? Crypt::decrypt($this->attributes['cell_phone_number']) : null;
    }

    public function getDiagnosticianAttribute()
    {
        return isset($this->attributes['diagnostician']) ? Crypt::decrypt($this->attributes['diagnostician']) : null;
    }

    public function getConsenterAttribute()
    {
        return isset($this->attributes['consenter']) ? Crypt::decrypt($this->attributes['consenter']) : null;
    }

    public function getConsenterPhoneNumberAttribute()
    {
        return isset($this->attributes['consenter_phone_number']) ? Crypt::decrypt($this->attributes['consenter_phone_number']) : null;
    }

    // 暗号化
    public static function decryptFacilityUserInfo($value)
    {
        $value['insurer_no'] = $value['insurer_no'] !== null ? Crypt::encrypt($value['insurer_no']) : null;
        $value['insured_no'] = $value['insured_no'] !== null ? Crypt::encrypt($value['insured_no']) : null;
        $value['last_name'] = $value['last_name'] !== null ? Crypt::encrypt($value['last_name']) : null;
        $value['first_name'] = $value['first_name'] !== null ? Crypt::encrypt($value['first_name']) : null;
        $value['last_name_kana'] = $value['last_name_kana'] !== null ? Crypt::encrypt($value['last_name_kana']) : null;
        $value['first_name_kana'] = $value['first_name_kana'] !== null ? Crypt::encrypt($value['first_name_kana']) : null;
        $value['postal_code'] = (isset($value['postal_code']) && $value['postal_code'] !== null) ? Crypt::encrypt($value['postal_code']) : null;
        $value['location1'] = (isset($value['location1']) && $value['location1'] !== null) ? Crypt::encrypt($value['location1']) : null;
        $value['location2'] = (isset($value['location2']) && $value['location2'] !== null) ? Crypt::encrypt($value['location2']) : null;
        $value['phone_number'] = (isset($value['phone_number']) && $value['phone_number'] !== null) ? Crypt::encrypt($value['phone_number']) : null;
        $value['cell_phone_number'] = (isset($value['cell_phone_number']) && $value['cell_phone_number'] !== null) ? Crypt::encrypt($value['cell_phone_number']) : null;
        $value['diagnostician'] = (isset($value['diagnostician']) && $value['diagnostician'] !== null) ? Crypt::encrypt($value['diagnostician']) : null;
        $value['consenter'] = (isset($value['consenter']) && $value['consenter'] !== null) ? Crypt::encrypt($value['consenter']) : null;
        $value['consenter_phone_number'] = (isset($value['consenter_phone_number']) && $value['consenter_phone_number'] !== null) ? Crypt::encrypt($value['consenter_phone_number']) : null;
        return $value;
    }

    /**
     * 請求対象の施設利用者を全て返す。
     * @param int[] $facilityUserIds
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function listBillingTarget(array $facilityUserIds, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $facilityUsers = self::whereIn('facility_user_id', $facilityUserIds)
            ->whereRaw('( start_date is not null AND start_date <= ? )', [$targetMonthEndDate])
            ->whereRaw('( end_date is null OR end_date >= ? )', [$targetMonthStartDate])
            ->whereRaw('( death_date is null OR death_date >= ? )', [$targetMonthStartDate])
            ->get()
            ->toArray();

        return $facilityUsers;
    }
}
