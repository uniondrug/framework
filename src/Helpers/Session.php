<?php
/**
 * cn.uniondrug.app.insurance
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-18
 */
namespace Pails\Helpers;

use Phalcon\Config;
use Phalcon\Di;

abstract class Session extends \stdClass
{
    const DEADLINE_NO = 3600;           // 未登录时Session有效期
    const DEADLINE_YES = 259200;        // 登录时的Session有效期
    const DEADLINE_FRESH = 900;         // 多久(单位:秒)后更新过期时间
    /**
     * @var \Redis
     */
    private static $connection;

    protected function openStorage($value)
    {
        $key = $this->generateStorageKey($value);
        return $this->getConnection()->hGetAll($key);
    }

    /**
     * @param string      $value
     * @param SessionData $data
     *
     * @return bool
     */
    protected function saveStorage($value, & $data)
    {
        $key = $this->generateStorageKey($value);
        if ($this->getConnection()->hMset($key, $data->toArray())) {
            return $this->getConnection()->expire($key, $data->isGuest() ? self::DEADLINE_NO : self::DEADLINE_YES);
        }
        return false;
    }

    /**
     * 读取Redis连接
     * @return \Redis
     * @throws \Exception
     */
    protected function getConnection()
    {
        if (self::$connection === null) {
            $conf = Di::getDefault()->getConfig()->path('redis');
            if (!isset($conf->port, $conf->host)) {
                throw new \Exception("Redis配置字段'host'或'port'未定义");
            }
            self::$connection = new \Redis();
            self::$connection->open($conf->host, $conf->port);
            if (isset($conf->auth)) {
                self::$connection->auth($conf->auth);
            }
            if (isset($conf->indexes) && $conf->indexes instanceof Config) {
                if (isset($conf->indexes->session)) {
                    self::$connection->select($conf->indexes->session);
                }
            }
        }
        return self::$connection;
    }

    /**
     * 按Cookie值生成Redis的Key
     *
     * @param string $value
     *
     * @return string
     */
    private function generateStorageKey($value)
    {
        return 's:'.substr(md5($value), 8, 16);
    }
}