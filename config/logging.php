<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

if (!defined('DEFAULT_APP_LOG')) {
    define('DEFAULT_APP_LOG', '/var/log/caredaisy-web/app.log');
}
if (!defined('DEFAULT_API_LOG')) {
    define('DEFAULT_API_LOG', '/var/log/caredaisy-web/app.log');
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['app'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => env('APP_LOG', DEFAULT_APP_LOG),
            'level' => 'debug',
            'permission' => 0666,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => env('APP_LOG', DEFAULT_APP_LOG),
            'level' => 'debug',
            'days' => 14,
            'permission' => 0666,
        ],

        // デイリーのカスタムエラーロガー
        'daily_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/error.log'),
            'tap' => [App\Logging\DailyErrorFormatter::class],
            'level' => 'warning',
            'days' => 14,
            'permission' => 0666,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => env('APP_LOG', DEFAULT_APP_LOG),
            'permission' => 0666,
        ],

        'api' => [
            'driver' => 'custom',
            'level' => 'debug',
            'via' => App\Logging\ApiLogger::class,
            'path' => env('APP_LOG', DEFAULT_API_LOG),
            'permission' => 0666,
        ],

        'app' => [
            'driver' => 'custom',
            'level' => 'debug',
            'via' => App\Logging\AppLogger::class,
            'path' => env('APP_LOG', DEFAULT_APP_LOG),
            'permission' => 0666,
        ],
    ],

];
