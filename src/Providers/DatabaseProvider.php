<?php
namespace Pails\Providers;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger\Adapter\File;

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
                    if ($config->debug) {
                        $logger = new File($this->logPath() . '/db_debug.log');
                        $this->getEventsManager()->attach(
                            'db',
                            function ($event, $connection) use ($logger) {
                                if ($event->getType() == 'beforeQuery') {
                                    /* @var \Phalcon\Db\AdapterInterface $connection */
                                    $sqlVariables = $connection->getSQLVariables();
                                    if (count($sqlVariables)) {
                                        $query = str_replace(array_map(function ($v) {
                                            return ':' . $v;
                                        }, array_keys($sqlVariables)), array_values($sqlVariables), $connection->getSQLStatement());
                                        $logger->log($query, \Phalcon\Logger::INFO);
                                    } else {
                                        $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                                    }
                                }
                            }
                        );
                    }

                    return $db;
                } else {
                    $this->getLogger()->error('No database config found. please check config file exists or APP_ENV is configed');
                    throw new \RuntimeException('No database config found. please check config file exists or APP_ENV is configed');
                }
            }
        );

        /**
         * set readonly connection
         */
        if ((true === $di->getConfig()->path('database.useSlave')) && $di->getConfig()->path('database.slaveConnection')) {
            $di->set(
                'dbSlave',
                function () {
                    $config = $this->getConfig()->get('database');
                    if ($config) {
                        $db = new Mysql($config->slaveConnection->toArray());
                        if ($config->debug) {
                            $logger = new File($this->logPath() . '/dbSlave_debug.log');
                            $this->getEventsManager()->attach(
                                'dbSlave',
                                function ($event, $connection) use ($logger) {
                                    if ($event->getType() == 'beforeQuery') {
                                        /* @var \Phalcon\Db\AdapterInterface $connection */
                                        $sqlVariables = $connection->getSQLVariables();
                                        if (count($sqlVariables)) {
                                            $query = str_replace(array_map(function ($v) {
                                                return ':' . $v;
                                            }, array_keys($sqlVariables)), array_values($sqlVariables), $connection->getSQLStatement());
                                            $logger->log($query, \Phalcon\Logger::INFO);
                                        } else {
                                            $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                                        }
                                    }
                                }
                            );
                        }

                        return $db;
                    } else {
                        $this->getLogger()->error('No readonly database config found. please check config file exists or APP_ENV is configed');
                        throw new \RuntimeException('No readonly database config found. please check config file exists or APP_ENV is configed');
                    }
                }
            );
        }
    }
}
