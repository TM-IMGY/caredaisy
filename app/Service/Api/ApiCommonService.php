<?php

namespace App\Service\Api;

use DB;
use Crypt;
use Exception;
use Log;

class ApiCommonService
{
    /**
     * DBのタイムスタンプを取得する
     *
     * @return string
     */
    protected function getDbTimestamp()
    {
        $result = DB::selectOne('SELECT NOW() AS timestamp');
        return $result->timestamp;
    }


    /**
     * 値がNULLの場合は空文字列に変換する
     *
     * @param   ?string $value
     * @return  string
     */
    protected function convert($value)
    {
        if (is_null($value)) {
            return '';
        }
        return $value;
    }


    /**
     * confidential情報の復号
     *
     * @param   string  $value
     * @return  string
     */
    protected function decrypt($value)
    {
        try {
            if (empty($value)) {
                return '';
            }
            $value = Crypt::decrypt($value);

            return $value;
        } catch (Exception $e) {
            report($e);
            return $e->getMessage();
        }
    }

    /**
     * 社員番号(employee_number)と事務所番号(facility_number)から事務所ID(facility_id)を取得する。
     * facility が存在しなかったり、社員の管理下にない場合はnullを返す
     *
     * @param   string $employeeNumber
     * @param   string $facilityNumber
     * @return ?integer
     */
    public function getFacilityId($employeeNumber, $facilityNumber)
    {
        try {
            $facility = DB::connection('mysql')
                ->table('i_accounts AS a')
                ->join('corporation_account AS ca', 'a.account_id', '=', 'ca.account_id')
                ->join('i_institutions AS iis', 'ca.corporation_id', '=', 'iis.corporation_id')
                ->join('i_facilities AS ifa', 'iis.id', '=', 'ifa.institution_id')
                ->select([
                    'a.account_id',
                    'a.employee_number',
                    'ca.corporation_id',
                    'iis.id',
                    'iis.name',
                    'ifa.facility_id',
                    'ifa.facility_name_kanji',
                ])
                ->where('a.employee_number', $employeeNumber)
                ->where('ifa.facility_number', $facilityNumber)
                ->first();

            if (empty($facility)) {
                // faclitiy not found
                return null;
            }

            return $facility->facility_id;
        } catch (Exception $e) {
            Log::channel('api')->error('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:'.$e->getMessage());
            throw $e;
        }

        return null;
    }
}
