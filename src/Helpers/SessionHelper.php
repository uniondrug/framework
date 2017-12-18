<?php
/**
 * framework
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-15
 */
namespace Pails\Helpers;

use Phalcon\Config;
use Phalcon\Di;

/**
 * Session操作
 * <code>
 * // 1. 从浏览器获取Cookie信息
 * $value = $_COOKIE['PHPSESSID'];
 * // 2. 使用Cookie实例化对象
 * $session = new SessionHelper($value);
 * // 3. 获取(只读)存储字段
 * echo 'memberId = '.$session->memberId;
 * echo 'accountId = '.$session->accountId;
 * echo 'merchantId = '.$session->merchantId;
 * // 登录
 * $session->login(1);               // C端用户
 * $session->merchantLogin(1, 2);    // 商启登录
 * // 退出
 * $session->logout();               // 任意用户退出
 * </code>
 * @property int $memberId 会员ID/C端普通消费者会员
 * @property int $accountId 账号ID/B端商户下的账号
 * @property int $merchantId 商户ID/B端商户主体
 * @property int $timestamp 最后一次更新过期时间的UNIX时间戳
 * @package Pails\Helpers
 */
class SessionHelper extends \stdClass
{
    private $key;
    private static $sesssions = [];
    private static $redisHandler;
    const DEADLINE_REFRESH = 900;       // 连续2次更新的时间间隔
    const STATUS_YES = 259200;          // 登录后Session存储时长
    const STATUS_NO = 3600;             // 未登录Session存储时长

    /**
     * Session constructor.
     *
     * @param string $value 浏览器端存储的Cookie有效值
     */
    public function __construct($value)
    {
        $this->key = 's:'.substr(md5($value), 8, 16);
        if ($this->read()) {
            $this->saveTimestamp();
        }
    }

    /**
     * 读取属性值
     *
     * @param string $name 读取属性值
     *
     * @return int
     * @throws \Exception
     */
    public function __get($name)
    {
        if (isset(self::$sesssions[$this->key][$name])) {
            return (int) self::$sesssions[$this->key][$name];
        }
        throw new \Exception("未定义的'{$name}'属性");
    }

    /**
     * 控制只读变更属性值
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        throw new \Exception("禁止修改修改只读属性'{$name}'的值");
    }

    /**
     * 商户登录
     *
     * @param int $accountId
     * @param int $merchantId
     *
     * @return bool
     */
    public function merchantLogin($accountId, $merchantId)
    {
        return $this->authentication(0, $accountId, $merchantId)->save();
    }

    /**
     * 消费(C端用户)者登录
     *
     * @param int $memberId 会员ID
     *
     * @return bool
     */
    public function login($memberId)
    {
        return $this->authentication($memberId, 0, 0)->save();
    }

    /**
     * 退出
     * @return bool
     */
    public function logout()
    {
        return $this->authentication(0, 0, 0)->save();
    }

    /**
     * 用户认证信息
     *
     * @param int $memberId 会员ID
     * @param int $accountId 商户员工ID
     * @param int $merchantId 商户ID
     *
     * @return $this
     */
    private function authentication($memberId = 0, $accountId = 0, $merchantId = 0)
    {
        $hash = [
            'memberId' => (int) $memberId,
            'accountId' => (int) $accountId,
            'merchantId' => (int) $merchantId,
            'timestamp' => (int) time(),
        ];
        self::$sesssions[$this->key] = $hash;
        return $this;
    }

    /**
     * 读取Redis连接
     * @return \Redis
     * @throws \Exception
     */
    private function getConnection()
    {
        /**
         * 已连接
         */
        if (self::$redisHandler !== null) {
            return self::$redisHandler;
        }
        /**
         * 读取配置
         */
        $di = Di::getDefault();
        $config = $di->getConfig()->path('redis');
        if (null === $config) {
            throw new \Exception("can not load redis config");
        }
        /**
         * 创建连接
         */
        if (!class_exists('\\Redis', false)) {
            throw new \Exception("can not load redis extension");
        }
        // 1. 打开连接
        self::$redisHandler = new \Redis();
        self::$redisHandler->open($config->host, $config->port);
        // 2. 密码授权
        if (isset($config->auth) && $config->auth !== '') {
            self::$redisHandler->auth($config->auth);
        }
        // 3. 指定库号
        if (isset($config->indexes) && ($config->indexes instanceof Config)) {
            if (isset($config->indexes->session)) {
                self::$redisHandler->select($config->indexes->session);
            }
        }
        // 4. 返回连接
        return self::$redisHandler;
    }

    /**
     * 从Redis读取Hash集
     */
    private function read()
    {
        $has = true;
        $hash = $this->getConnection()->hGetAll($this->key);
        if (!is_array($hash) || count($hash) == 0) {
            $has = false;
            $hash = [
                'memberId' => 0,
                'accountId' => 0,
                'merchantId' => 0,
                'timestamp' => 0
            ];
        }
        self::$sesssions[$this->key] = $hash;
        return $has;
    }

    /**
     * 保存Session信息
     * @return bool
     */
    private function save()
    {
        $hash = self::$sesssions[$this->key];
        if ($this->getConnection()->hMset($this->key, $hash)) {
            return $this->saveDeadline();
        }
        return false;
    }

    /**
     * 保存过期时间
     * @return bool
     */
    private function saveDeadline()
    {
        $hash = &self::$sesssions[$this->key];
        $ttl = $hash['memberId'] > 0 || $hash['accountId'] > 0 ? self::STATUS_YES : self::STATUS_NO;
        return $this->getConnection()->expire($this->key, $ttl);
    }

    /**
     * 检查是否需要更新过期时间
     * @return bool
     */
    private function saveTimestamp()
    {
        $hash = &self::$sesssions[$this->key];
        if ($hash['timestamp'] > 0 && ($hash['timestamp'] + self::DEADLINE_REFRESH) < (int) time()) {
            $hash['timestamp'] = time();
            return $this->save();
        }
        return false;
    }
}