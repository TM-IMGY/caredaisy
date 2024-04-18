<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UninsuredItemHistory extends Model
{

    /* 単位 */
    public const UNIT_ONCE  = 1; // 1回
    public const UNIT_DAY   = 2; // 1日
    public const UNIT_SET   = 3; // 1セット
    public const UNIT_MONTH = 4; // 1ヶ月

    protected $table = 'i_uninsured_item_histories';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
      ];

    public function uninsuredRequest()
    {
        return $this->hasOne('App\Models\UninsuredRequest', 'uninsured_item_history_id');
    }

    public function scopeGetHistories($query,$id){
      return $query->where('uninsured_item_id',$id)
        ->select(
          'id',
          'item',
          'unit_cost',
          'unit',
          'set_one',
          'fixed_cost',
          'variable_cost',
          'welfare_equipment',
          'meal',
          'daily_necessary',
          'hobby',
          'escort',
          'billing_reflect_flg',
          'sort'
        )
        ->orderByRaw('sort asc, id asc')
        ->get();
    }
}
