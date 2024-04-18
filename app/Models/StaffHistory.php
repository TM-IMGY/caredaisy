<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class StaffHistory extends Model
{
    protected $table = 'i_staff_histories';
    protected $connection = 'mysql';

    protected $guarded = ['id'];


    // 各種復号化
    static function getDecryptData($value)
    {
        return isset($value) ? Crypt::decrypt($value) : null;
    }

    public function getNameAttribute()
    {
        return self::getDecryptData($this->attributes['name']);
    }

    public function getNameKanaAttribute()
    {
        return self::getDecryptData($this->attributes['name_kana']);
    }

    public function getLocationAttribute()
    {
        return self::getDecryptData($this->attributes['location']);
    }

    public function getPhoneNumberAttribute()
    {
        return self::getDecryptData($this->attributes['phone_number']);
    }

    public function getEmergencyContactInformationAttribute()
    {
        return self::getDecryptData($this->attributes['emergency_contact_information']);
    }
    // 暗号化
    static function getEncryptData($value)
    {
        return isset($value) ? Crypt::encrypt($value) : null;
    }
    public static function encryptStaffHistory($value)
    {
        $column = [
            'name',
            'name_kana',
            'location',
            'phone_number',
            'emergency_contact_information',
        ];
        foreach ($column as $v) {
            $value[$v] = self::getEncryptData($value[$v]);
        }
        return $value;
    }
}
