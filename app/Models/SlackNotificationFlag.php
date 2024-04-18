<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlackNotificationFlag extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'mysql';
    protected $table = 'slack_notification_flags';

    protected $guarded = ['id'];
}
