<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UninsuredItem extends Model
{
    protected $table = 'i_uninsured_items';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
      ];

    public function uninsuredItemHistories()
    {
        return $this->hasMany('App\Models\UninsuredItemHistory', 'uninsured_item_id');
    }
}
