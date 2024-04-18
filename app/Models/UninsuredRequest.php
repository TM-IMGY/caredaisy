<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UninsuredRequest extends Model
{
    protected $table = 'i_uninsured_requests';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
    ];

    public function uninsuredItemHistory()
    {
        return $this->belongsTo('App\Models\UninsuredItemHistory', 'uninsured_item_history_id', 'id');
    }

    public function details()
    {
        return $this->hasMany('App\Models\UninsuredRequestDetail', 'uninsured_request_id');
    }
}
