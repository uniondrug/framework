<?php
/**
 * 日志服务注册
 *
 */

namespace Uniondrug\Framework\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger\Adapter\File;

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
                    $logPath = $this->logPath() . '/' . $logCategory . '/' . $month;
                    $logFile = $logPath . '/' . $date . '.log';
                } else {
                    $logPath = $this->logPath() . '/' . $logCategory;
                    $logFile = $logPath . '/' . $date . '.log';
                }
                try {
                    if (!file_exists($logPath)) {
                        mkdir($logPath, 0755, true);
                    }
                } catch (\Throwable $e) {
                    // skip. multi process may try to make dir at the same time. just skip errors.
                }
                $logLevel = $this->getConfig()->path('logger.level', \Phalcon\Logger::DEBUG);

                $logger = new File($logFile);
                $logger->setLogLevel($logLevel);

                return $logger;
            }
        );
    }
}
