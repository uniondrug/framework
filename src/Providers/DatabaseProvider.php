<?php

namespace Uniondrug\Framework\Providers;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\ServiceProviderInterface;
use Uniondrug\Framework\Events\Listeners\DatabaseListener;

class DatabaseProvider implements ServiceProviderInterface
{
    /**
     * @param \Phalcon\DiInterface|\Uniondrug\Framework\Container $di
     */
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'db',
            function () {
                $config = \config()->path('database');
                if ($config) {
                    $db = new Mysql($config->connection->toArray());
                    $db->setEventsManager(\app()->getEventsManager());

                    return $db;
                } else {
                    throw new \RuntimeException('No database config found. please check config file exists or APP_ENV is configed');
                }
            }
        );

        /**
         * set readonly connection
         */
        if ((true === \config()->path('database.useSlave', false)) && \config()->path('database.slaveConnection', false)) {
            $di->set(
                'dbSlave',
                function () {
                    $config = \config()->get('database');
                    if ($config) {
                        $db = new Mysql($config->slaveConnection->toArray());
                        $db->setEventsManager(\app()->getEventsManager());

                        return $db;
                    } else {
                        throw new \RuntimeException('No readonly database config found. please check config file exists or APP_ENV is configed');
                    }
                }
            );
        }

        // 打开数据库调试日志
        if (\config()->path('database.debug', false)) {
            \app()->getEventsManager()->attach('db', new DatabaseListener());
        }
    }
}
