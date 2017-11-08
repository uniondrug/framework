<?php
/**
 * 日志服务注册
 *
 */
namespace Pails\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger\Adapter\File as FileLogger;

class LoggerProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        // logger
        $di->set(
            'logger',
            function ($logCategory = 'app') {
                $month = date('Y-m');
                $date = date('Y-m-d');
                if ($this->getConfig()->path('logger.splitDir', false)) {
                    $logPath = $this->logPath() . DIRECTORY_SEPARATOR . $logCategory . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR;
                    $logFile = $logPath . $date . '.log';
                } else {
                    $logPath = $this->logPath() . DIRECTORY_SEPARATOR . $logCategory . DIRECTORY_SEPARATOR;
                    $logFile = $logPath . $date . '.log';
                }
                if (!@file_exists($logPath)) {
                    @mkdir($logPath, 0755);
                }
                return new FileLogger($logFile);
            }
        );
    }
}
