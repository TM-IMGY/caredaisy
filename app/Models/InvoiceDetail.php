<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table = 'i_invoice_details';
    protected $connection = 'mysql';

    protected $primaryKey = 'id';

    protected $guarded = [
      'id',
    ];
}
