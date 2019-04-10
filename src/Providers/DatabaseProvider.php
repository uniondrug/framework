<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-04-10
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Uniondrug\Framework\Mysql;
use Uniondrug\Framework\Container;
use Uniondrug\Framework\Events\Listeners\DatabaseListener;

/**
 * 注册DB连接
 * 本类可用于多连接注册
 * @package Uniondrug\Framework\Providers
 */
class DatabaseProvider implements ServiceProviderInterface
{
    private $_inited = false;
    private $listenerEnabled = false;
    private $listenerError = null;
    private $listenerWarning = null;
    private $slaveEnabled = false;

    /**
     * 初始化Datatabase选项
     * @param Container $di
     */
    private function initDatabaseOptions($di)
    {
        // 1. retry or not
        if ($this->_inited) {
            return;
        }
        // 2. updte status
        $this->_inited = true;
        // 3. listener
        $listener = $di->getConfig()->path('database.durationListener');
        $this->listenerEnabled = $listener === true || $listener === 'true';
        if ($this->listenerEnabled) {
            $listenerError = $di->getConfig()->path('database.durationError');
            // 4.1 error duration
            if (is_numeric($listenerError) && $listenerError > 0.0) {
                $this->listenerError = (double) $listenerError;
            }
            // 4.2 warning duration
            $listenerWarning = $di->getConfig()->path('database.durationWarning');
            if (is_numeric($listenerWarning) && $listenerWarning > 0.0) {
                $this->listenerWarning = (double) $listenerWarning;
            }
        }
        // 4. slave
        $slave = $di->getConfig()->path('database.useSlave');
        $this->slaveEnabled = $slave === true || $slave === 'true';
    }

    /**
     * 注册连接
     * @param DiInterface $di
     */
    public function register(DiInterface $di)
    {
        /**
         * @var Container $di
         * @var Config    $masterConnection
         * @var Config    $slaveConnection
         */
        $masterConnection = $di->getConfig()->path('database.connection');
        if ($masterConnection instanceof Config) {
            $this->setShared($di, $masterConnection, 'db');
            if ($this->slaveEnabled) {
                $slaveConnection = $di->getConfig()->path('database.slaveConnection');
                if ($slaveConnection instanceof Config) {
                    $this->setShared($di, $slaveConnection, 'dbSlave');
                }
            }
        }
    }

    /**
     * 设置DB依赖注入
     * @param Container $di
     * @param Config    $connection
     * @param string    $name
     * @throws \ErrorException
     */
    final public function setShared($di, $connection, string $name)
    {
        // 1. 前设置检查
        $this->initDatabaseOptions($di);
        // 2. 容器检查
        if (!($di instanceof Container)) {
            throw new \ErrorException("can not register {$name} of mysql to Container.");
        }
        // 3. 配置检查
        if (!($connection instanceof Config)) {
            throw new \ErrorException("can not register {$name} of mysql for Connection configuration.");
        }
        // 4. 加入容器
        $di->addSharedDatabase($name, isset($connection->dbname) ? $connection->dbname : 'unknown');
        // 5. 依赖注入
        $di->setShared($name, function() use ($di, $connection, $name){
            $di->getLogger()->info("[db={$name}]注入共享的{{$name}}连接");
            $db = new Mysql($connection->toArray());
            $db->setSharedName($name);
            $db->setEventsManager($di->getEventsManager());
            return $db;
        });
        // 6. 事件监听
        if ($this->listenerEnabled) {
            $di->getEventsManager()->attach($name, new DatabaseListener($this->listenerError, $this->listenerWarning));
        }
    }
}
