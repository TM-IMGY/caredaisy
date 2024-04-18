<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Staff extends Model
{
    protected $table = 'i_staffs';
    protected $connection = 'mysql';

    protected $guarded = ['id'];

    public function account()
    {
        return $this->hasOne('App\User');
    }

    public function history()
    {
        return $this->hasMany('App\Models\StaffHistory');
    }

    public function authextent()
    {
        return $this->hasMany('App\Models\AuthExtent');
    }
    // 各種復号化
    private static function getDecryptData($value)
    {
        return isset($value) ? Crypt::decrypt($value) : null;
    }

    public function getEmployeeNumberAttribute()
    {
        return self::getDecryptData($this->attributes['employee_number']);
    }


    // 暗号化
    private static function getEncryptData($value)
    {
        return isset($value) ? Crypt::encrypt($value) : null;
    }
    public static function encryptStaff($value)
    {
        $column = [
            'employee_number',
        ];
        foreach ($column as $v) {
            $value[$v] = self::getEncryptData($value[$v]);
        }
        return $value;
    }
}
