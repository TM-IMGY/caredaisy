<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnAttachment extends Model
{
    protected $table = 'i_return_attachments';
    protected $connection = 'mysql';
    protected $guarded = ['id'];
}
