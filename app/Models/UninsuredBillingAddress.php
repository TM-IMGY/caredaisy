<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class UninsuredBillingAddress extends Model
{
    protected $connection = 'confidential';
    protected $table = 'i_uninsured_billing_addresses';
    protected $primaryKey = 'facility_id, facility_user_id';

    protected $guarded = [
        'facility_id',
        'facility_user_id',
    ];

    // 各種復号化
    static function getDecryptData($value)
    {
        return isset($value) ? Crypt::decrypt($value) : null;
    }
    public function getNameAttribute()
    {
        return self::getDecryptData($this->attributes['name']);
    }
    public function getPhoneNumberAttribute()
    {
        return self::getDecryptData($this->attributes['phone_number']);
    }
    public function getFaxNumberAttribute()
    {
        return self::getDecryptData($this->attributes['fax_number']);
    }
    public function getPostalCodeAttribute()
    {
        return self::getDecryptData($this->attributes['postal_code']);
    }
    public function getLocation1Attribute()
    {
        return self::getDecryptData($this->attributes['location1']);
    }
    public function getLocation2Attribute()
    {
        return self::getDecryptData($this->attributes['location2']);
    }
    public function getBankNumberAttribute()
    {
        return self::getDecryptData($this->attributes['bank_number']);
    }
    public function getBankAttribute()
    {
        return self::getDecryptData($this->attributes['bank']);
    }
    public function getBranchNumberAttribute()
    {
        return self::getDecryptData($this->attributes['branch_number']);
    }
    public function getBranchAttribute()
    {
        return self::getDecryptData($this->attributes['branch']);
    }
    public function getBankAccountAttribute()
    {
        return self::getDecryptData($this->attributes['bank_account']);
    }
    public function getDepositorAttribute()
    {
        return self::getDecryptData($this->attributes['depositor']);
    }
    public function getRemarksForReceiptAttribute()
    {
        return self::getDecryptData($this->attributes['remarks_for_receipt']);
    }
    public function getRemarksForBillAttribute()
    {
        return self::getDecryptData($this->attributes['remarks_for_bill']);
    }

    // 暗号化
    static function getEncryptData($value)
    {
        return isset($value) ? Crypt::encrypt($value) : null;
    }
    public static function encryptUninsuredBillingAddresse($value)
    {
        $column = [
            'name',
            'phone_number',
            'fax_number',
            'postal_code',
            'location1',
            'location2',
            'bank_number',
            'bank',
            'branch_number',
            'branch',
            'bank_account',
            'depositor',
            'remarks_for_receipt',
            'remarks_for_bill',
        ];
        foreach ($column as $v) {
            $value[$v] = self::getEncryptData($value[$v]);
        }
        return $value;
    }
}
