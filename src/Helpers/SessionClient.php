<?php
/**
 * 框架级Helper
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-18
 */
namespace Pails\Helpers;

/**
 * 用户级Session客户端
 * @package Pails\Helpers
 */
class SessionClient extends Session
{
    /**
     * @var string 存储在浏览器端的Cookie名称
     */
    private static $name = 'UGCLI';
    private static $value;
    private static $data;

    /**
     * 初始化Session客户端
     */
    public function __construct()
    {
        if (isset($_COOKIE[self::$name]) && $_COOKIE[self::$name] !== "") {
            if ($this->isCookieValue($_COOKIE[self::$name])) {
                self::$value = $_COOKIE[self::$name];
            }
        }
        if (self::$value === null) {
            self::$value = $this->generateValue();
            $this->saveValue();
        }
        self::$data = new SessionData(self::$value);
    }

    /**
     * 读取Session数据结构
     * @return SessionData
     */
    public function getData()
    {
        return self::$data;
    }

    /**
     * 生成UserAgent字符串
     * @return bool|string
     */
    private function generateAgent()
    {
        return substr(md5($_SERVER['HTTP_USER_AGENT']), 8, 16);
    }

    /**
     * 生成随机Cookie
     * @return string
     */
    private function generateValue()
    {
        return $this->generateAgent().substr(md5(microtime(true)."\t".mt_rand(1001, 9999)), 8, 16);
    }

    /**
     * 验证Cookie值合法性
     *
     * @param string $value 检查Cookie值是否合法
     *
     * @return bool
     */
    private function isCookieValue($value)
    {
        return substr($value, 0, 16) === $this->generateAgent();
    }

    /**
     * 存储Cookie到浏览器
     */
    private function saveValue()
    {
        $timestamp = (int) time() + parent::DEADLINE_YES;
        setcookie(self::$name, self::$value, $timestamp, '/', '');
    }
}