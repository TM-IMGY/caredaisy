<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $connection = 'mysql';
    protected $table = 'i_services';

    protected $guarded = ['id',];

    public function serviceType()
    {
        return $this->hasOne('App\Models\ServiceType', 'service_type_code_id', 'service_type_code_id');
    }

    public function getAreaAttribute(){
        switch ($this->attributes['area']) {
            case 1:
                return '１級地';
            case 2:
                return '２級地';
            case 3:
                return '３級地';
            case 4:
                return '４級地';
            case 5:
                return '５級地';
            case 6:
                return '６級地';
            case 7:
                return '７級地';
            case 8:
                return 'その他';
        }
    }
}
