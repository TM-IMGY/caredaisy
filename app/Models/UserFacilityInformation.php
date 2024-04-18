<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 施設利用者と事業所のリレーション情報の操作に責任を持つクラス。
 * Userとはログインユーザーではなく施設利用者なので注意する。
 */
class UserFacilityInformation extends Model
{
    protected $connection = 'mysql';
    protected $guarded = ['user_facility_information_id'];
    protected $primaryKey = 'user_facility_information_id';
    protected $table = 'i_user_facility_informations';

    public function i_facility_users()
    {
        return $this->belongsTo('App\Models\FacilityUser', 'facility_user_id', 'facility_user_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Models\Facility', 'facility_id', 'facility_id');
    }
}
