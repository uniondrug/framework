<?php
namespace Pails\Providers;

use Pails\Events\Listeners\DatabaseListener;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\ServiceProviderInterface;

class DatabaseProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'db',
            function () {
                $config = $this->getConfig()->path('database');
                if ($config) {
                    $db = new Mysql($config->connection->toArray());
                    return $db;
                } else {
                    throw new \RuntimeException('No database config found. please check config file exists or APP_ENV is configed');
                }
            }
        );

        // 打开数据库调试日志
        if ($di->getConfig()->path('database.debug', false)) {
            $di->getEventsManager()->attach('db', new DatabaseListener());
        }

        /**
         * set readonly connection
         */
        if ((true === $di->getConfig()->path('database.useSlave', false)) && $di->getConfig()->path('database.slaveConnection', false)) {
            $di->set(
                'dbSlave',
                function () {
                    $config = $this->getConfig()->get('database');
                    if ($config) {
                        $db = new Mysql($config->slaveConnection->toArray());
                        return $db;
                    } else {
                        throw new \RuntimeException('No readonly database config found. please check config file exists or APP_ENV is configed');
                    }
                }
            );
        }
    }
}
