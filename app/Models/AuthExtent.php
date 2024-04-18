<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthExtent extends Model
{
    protected $table = 'i_auth_extents';
    protected $connection = 'mysql';

    protected $guarded = ['id'];

    public function auth()
    {
        return $this->hasOne('App\Models\Auth', 'auth_id', 'auth_id');
    }
    public function corporation()
    {
        return $this->hasOne('App\Models\Corporation', 'id', 'corporation_id');
    }
    public function institution()
    {
        return $this->hasOne('App\Models\Institution', 'id', 'institution_id');
    }
    public function facility()
    {
        return $this->hasOne('App\Models\Facility', 'facility_id', 'facility_id');
    }
}
