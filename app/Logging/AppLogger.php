<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppLogger
{
    const NAME = 'caredaisy-app';

    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $level = Logger::toMonologLevel($config['level']);
        $logger = new Logger(self::NAME);

        // プロセスIDを取得する
        $pid = getmypid();
        $format = "[%datetime%][%level_name%][${pid}]%message%\n";

        $formatter = new LineFormatter($format, 'Y-m-d H:i:s', true, true);
        $stream = new StreamHandler($config['path'], $level, true, $config['permission']);
        $stream->setFormatter($formatter);
        $logger->pushHandler($stream);

        return $logger;
    }
}
