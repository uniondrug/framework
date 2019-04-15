<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-04-10
 */
namespace Uniondrug\Framework\Events\Listeners;

use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Uniondrug\Framework\Injectable;
use Uniondrug\Framework\Mysql as Connection;
use Uniondrug\Framework\Mysql;

/**
 * DB查询过程
 * @package Uniondrug\Framework\Events\Listeners
 */
class DatabaseListener extends Injectable
{
    /**
     * @var Profiler
     */
    protected $profiler;
    /**
     * SQL慢查询
     * 当SQL执行时长超时此值(0.5秒)时, 加入WARN级Logger
     * @var float
     */
    protected $durationWarning = 0.5;
    /**
     * SQL报警
     * 当SQL执行时长超时此值(1.0秒)时, 加入ERROR级Logger, 并发送报警
     * @var float
     */
    protected $durationError = 1.0;

    /**
     * DatabaseListener constructor.
     * @param int|null $error
     * @param int|null $warning
     */
    public function __construct(int $error = null, int $warning = null)
    {
        $this->profiler = new Profiler();
        $error === null || $this->durationError = (double) ($error / 1000);
        $warning === null || $this->durationWarning = (double) ($warning / 1000);
    }

    /**
     * SQL完成执行后
     * @param Event      $event
     * @param Connection $connection
     */
    public function afterQuery(Event $event, $connection)
    {
        /**
         * @var Profiler\Item $profile
         */
        $this->profiler->stopProfile();
        $profile = $this->profiler->getLastProfile();
        $duration = (double) $profile->getTotalElapsedSeconds();
        // 1. logger内容
        if ($connection instanceof Mysql) {
            $sql = $connection->getListenerSQLStatment();
            $msg = sprintf("[d=%.06f][db=%s]", $duration, $connection->getSharedName());
        } else {
            $sql = $connection->getSQLStatement();
            $msg = sprintf("[d=%.06f]", $duration);
        }
        // 2. 慢查询
        if ($duration >= $this->durationWarning) {
            // 2.1 太慢了
            if ($duration >= $this->durationError) {
                $this->logger->error($msg."SQL太慢 - ".$sql);
                return;
            }
            // 2.2 比较慢了
            //     需引起重视了
            $this->logger->warning($msg."SQL较慢 - ".$sql);
            return;
        }
        // 3. 普通查询
        //    速度可以接受
        $this->logger->info($msg."SQL完成 - ".$sql);
    }

    /**
     * SQL开始执行前
     * @param Event      $event
     * @param Connection $connection
     */
    public function beforeQuery(Event $event, $connection)
    {
        $this->profiler->startProfile($connection->getSQLStatement());
    }
}
