<?php
/**
 * @date 2018-06-15
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Uniondrug\Framework\Container;

/**
 * 日志注入Provider
 * @package Uniondrug\Framework\Providers
 */
class LoggerProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set('logger', function($logCategory = 'app'){
            $month = date('Y-m');
            $date = date('Y-m-d');
            /**
             * @var Container $this
             */
            if ($this->getConfig()->path('logger.splitDir', false)) {
                $logPath = $this->logPath().'/'.$logCategory.'/'.$month;
                $logFile = $logPath.'/'.$date.'.log';
            } else {
                $logPath = $this->logPath().'/'.$logCategory;
                $logFile = $logPath.'/'.$date.'.log';
            }
            try {
                if (!file_exists($logPath)) {
                    mkdir($logPath, 0755, true);
                }
            } catch(\Throwable $e) {
                // skip. multi process may try to make dir at the same time. just skip errors.
            }
            $logLevel = $this->getConfig()->path('logger.level', Logger::DEBUG);
            $formatter = new LoggerFormatter();
            $logger = new File($logFile);
            $logger->setLogLevel($logLevel);
            $logger->setFormatter($formatter);
            return $logger;
        });
    }
}
