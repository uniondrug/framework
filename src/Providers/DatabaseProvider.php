<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-04-10
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Uniondrug\Framework\Container;
use Uniondrug\Framework\Events\Listeners\DatabaseListener;
use Uniondrug\Framework\Mysql;

/**
 * 注册DB连接
 * 本类可用于多连接注册
 * @package Uniondrug\Framework\Providers
 */
class DatabaseProvider implements ServiceProviderInterface
{
    private $beforeSharedInitialized = false;
    /**
     * @var bool
     */
    private $listenerEnabled = false;
    /**
     * 慢查报警
     * 当SQL执行时长超时此值时, 发送Error级Logger
     * 单位: 毫秒
     * @var int
     */
    private $errorDuration = 1000;
    /**
     * 查询注意
     * 当SQL执行时长超时此值时, 发送Warning级Logger
     * 单位: 毫秒
     * @var int
     */
    private $warningDuration = 500;

    /**
     * 注册连接
     * @param DiInterface $di
     */
    public function register(DiInterface $di)
    {
        /**
         * 1. reset instance
         * @var Container $di
         */
        $master = $di->getConfig()->path('database.connection');
        if (!($master instanceof Config)) {
            return;
        }
        // 2. register master
        $this->setShared($di, $master, 'db');
        // 3. slave status
        $enable = $di->getConfig()->path('database.useSlave');
        if ($enable !== true) {
            return;
        }
        // 4. register slave
        $slave = $di->getConfig()->path('database.slaveConnection');
        $this->setShared($di, $slave, 'dbSlave');
    }

    /**
     * 设置注入
     * @param Container $di
     * @param Config    $config
     * @param string    $name
     * @throws \ErrorException
     */
    final protected function setShared($di, $config, $name)
    {
        // 1. container type
        if (!($di instanceof Container)) {
            throw new \ErrorException(sprintf("[db=%s]error container instance.", $name));
        }
        // 2. config type
        if (!($config instanceof Config)) {
            throw new \ErrorException(sprintf("[db=%s]error database config for connection.", $name));
        }
        // 3. initialize
        $this->beforeSetShared($di);
        // 4. set shared
        $di->setShared($name, function() use ($di, $config, $name){
            $di->getLogger()->info(sprintf("[db=%s]注入{%s}为共享的DB连接.", $name, $name));
            $dn = isset($config->dbname) ? $config->dbname : 'unknown';
            $di->addSharedDatabase($name, $dn);
            $db = new Mysql($config->toArray());
            $db->setSharedName($name);
            $db->setEventsManager($di->getEventsManager());
            return $db;
        });
        // 5. listener
        if ($this->listenerEnabled){
            $di->getEventsManager()->attach($name, new DatabaseListener($this->errorDuration, $this->warningDuration));
        }
    }

    /**
     * 前置Shared操作
     * @param Container $di
     */
    private function beforeSetShared($di)
    {
        // 1. recall
        if ($this->beforeSharedInitialized) {
            return;
        }
        // 2. status
        $this->beforeSharedInitialized = true;
        // 3. listener status
        $listenerEnabled = $di->getConfig()->path('database.listener');
        $this->listenerEnabled = $listenerEnabled === true;
        // 4. warning duration
        $warningDuration = $di->getConfig()->path('database.listenerWarningDuration');
        if (is_numeric($warningDuration) && $warningDuration > 0) {
            $this->warningDuration = (int) $warningDuration;
        }
        // 5. error duration
        $errorDuration = $di->getConfig()->path('database.listenerErrorDuration');
        if (is_numeric($errorDuration) && $errorDuration > 0) {
            $this->errorDuration = (int) $errorDuration;
        }
    }
}
