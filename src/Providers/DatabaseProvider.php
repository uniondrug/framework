<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-27
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Events\ManagerInterface;
use Uniondrug\Framework\Container;
use Uniondrug\Framework\Events\Listeners\DatabaseListener;

/**
 * 注册数据库连接服务
 * @package Uniondrug\Framework\Providers
 */
class DatabaseProvider implements ServiceProviderInterface
{
    /**
     * 注册数据库连接
     */
    public function register(\Phalcon\DiInterface $di)
    {
        // 1. 读取数据库配置信息
        //    config/database.php
        $database = \config()->path('database');
        if (!($database instanceof Config)) {
            throw new \Exception("can not load database configuration from config/database.php.");
        }
        // 2. Required配置
        if (!isset($database->connection) || !($database->connection instanceof Config)) {
            throw new \Exception("can not load connection for master");
        }
        /**
         * 3. Master & Slave
         * @var Config $master
         * @var Config $slave
         */
        $master = $database->connection;
        if (isset($database->slaveConnection) && ($database->slaveConnection instanceof Config)) {
            $slave = $database->slaveConnection;
        } else {
            $slave = $master;
        }
        /**
         * 4. 注册服务
         * @var Container $di
         */
        $debug = isset($database->debug) && $database->debug === true;
        $manager = \app()->getEventsManager();
        $this->registerService('db', $di, $manager, $master, $debug);
        $this->registerService('dbSlave', $di, $manager, $slave, $debug);
    }

    /**
     * 注册连接
     * @param string           $name
     * @param Container        $container
     * @param ManagerInterface $manager
     * @param Config           $config
     */
    private function registerService(string $name, $container, $manager, $config, $debug = false)
    {
        echo "{$name}"; print_r ($config->toArray());


        // 1. 注册依赖服务
        $container->set($name, function() use ($manager, $config){
            $db = new Mysql($config->toArray());
            $db->setEventsManager($manager);
            return $db;
        });
        // 2. 加入调试监听
        $debug && $manager->attach($name, new DatabaseListener());
    }
}
