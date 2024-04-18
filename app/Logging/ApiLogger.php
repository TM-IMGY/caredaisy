<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

use Log;

class ApiLogger
{
    const NAME = 'caredaisy-api';

    public function __invoke(array $config)
    {
        $level = Logger::toMonologLevel($config['level']);
        $logger = new Logger(self::NAME);

        $pid = getmypid();
        $format = "[%datetime%][%level_name%][{$pid}]%message%\n";

        $formatter = new LineFormatter($format, 'Y-m-d H:i:s', true, true);
        $stream = new StreamHandler($config['path'], $level, true, $config['permission']);
        $stream->setFormatter($formatter);
        $logger->pushHandler($stream);

        return $logger;
    }
}
