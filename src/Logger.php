<?php
/**
 * 封装一下日志处理，在Swoole下，避免常驻后日志不滚动。
 *
 * @author XueronNi
 * @date   2018-01-18
 */

namespace Uniondrug\Framework;

use Phalcon\Logger\Adapter\File;

class Logger extends File
{
    protected $_lastDate;

    protected $_logCategory = 'app';

    /**
     * 改写logInternal，写入前检查是否需要rotate
     *
     * @param string $message
     * @param int    $type
     * @param int    $time
     * @param array  $context
     */
    public function logInternal($message, $type, $time, array $context = [])
    {
        $this->rotate();

        parent::logInternal($message, $type, $time, $context);
    }

    /**
     * @param $date
     *
     * @return $this
     */
    public function setLastDate($date)
    {
        $this->_lastDate = $date;

        return $this;
    }

    public function setLogCategory($category)
    {
        $this->_logCategory = $category;

        return $this;
    }

    /**
     * 滚动日志，重新打开
     */
    public function rotate()
    {
        $di = Container::getDefault();
        $month = date('Y-m');
        $date = date('Y-m-d');
        if ($date != $this->_lastDate) {
            $this->_lastDate = $date;
            if ($di->getConfig()->path('logger.splitDir', false)) {
                $logPath = $di->logPath() . DIRECTORY_SEPARATOR . $this->_logCategory . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR;
                $logFile = $logPath . $date . '.log';
            } else {
                $logPath = $di->logPath() . DIRECTORY_SEPARATOR . $this->_logCategory . DIRECTORY_SEPARATOR;
                $logFile = $logPath . $date . '.log';
            }
            if (!@file_exists($logPath)) {
                @mkdir($logPath, 0755, true);
            }

            if (!isset($this->_options['mode'])) {
                $mode = 'ab';
            } else {
                $mode = $this->_options['mode'];
            }

            fclose($this->_fileHandler);
            $this->_fileHandler = fopen($logFile, $mode);
        }
    }
}
