<?php
/**
 * 框架级Helper
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-18
 */
namespace Pails\Helpers;

/**
 * 用户级Session数据结构
 * @package Pails\Helpers
 */
class SessionData extends Session
{
    private $memberId = 0;
    private $accountId = 0;
    private $merchantId = 0;
    private $timestamp = 0;
    private $cookieValue = '';

    /**
     * 构造Session数据结构
     *
     * @param string $value 浏览器存储的Cookie值
     */
    public function __construct($value)
    {
        $this->cookieValue = $value;
        $data = $this->openStorage($value);
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                property_exists($this, $key) && $this->$key = (int) $val;
            }
            $this->update();
        }
    }

    /**
     * 读取Session数据结构的字段值
     *
     * @param string $key 属性名称
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        throw new \Exception("属性'{$key}'未定义");
    }

    /**
     * 只读属性, 禁止修改
     *
     * @param string $key
     * @param int    $value
     *
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        throw new \Exception("只读属性'{$key}'禁止修改");
    }

    /**
     * 读取当前Session数据结构对应到浏览器端存储的Cookie值
     * @return string
     */
    public function getValue()
    {
        return $this->cookieValue;
    }

    /**
     * 是否为游客(未登录)
     * @return bool
     */
    public function isGuest()
    {
        return !$this->isMember() && !$this->isMerchant();
    }

    /**
     * 是否为会员
     * @return bool
     */
    public function isMember()
    {
        return $this->memberId > 0;
    }

    /**
     * 是否为商户
     * 1. 账号ID(员工) > 0
     * 2. 商启ID(商家) >= 0
     * @return bool
     */
    public function isMerchant()
    {
        return $this->accountId > 0;
    }

    /**
     * Session是否需要更新过期时间
     * @return bool
     */
    public function isUpdate()
    {
        if ($this->timestamp > 0 && ($this->timestamp + parent::DEADLINE_FRESH) < (int) time()) {
            return true;
        }
        return false;
    }

    /**
     * C端会员登录
     *
     * @param int $memberId 用户ID
     *
     * @return bool
     */
    public function login($memberId)
    {
        return $this->set($memberId, 0, 0);
    }

    /**
     * A/B/C端用户退出登录
     * @return bool
     */
    public function logout()
    {
        return $this->set(0, 0, 0);
    }

    /**
     * B端商户登录
     *
     * @param int $accountId 账号ID
     * @param int $merchantId 商户ID
     *
     * @return bool
     */
    public function merchantLogin($accountId, $merchantId)
    {
        return $this->set(0, $accountId, $merchantId);
    }

    /**
     * Session数据转数组
     * @return array
     */
    public function toArray()
    {
        return [
            'memberId' => $this->memberId,
            'accountId' => $this->accountId,
            'merchantId' => $this->merchantId,
            'timestamp' => $this->timestamp
        ];
    }

    public function update()
    {
        if ($this->isUpdate()) {
            $this->timestamp = (int) time();
            return $this->saveStorage($this->getValue(), $this);
        }
        return false;
    }

    /**
     * 以登录/退出时以私有方法修改属性值
     *
     * @param int $memberId C类会员ID
     * @param int $accountId B类账号ID
     * @param int $merchantId B类商户ID
     *
     * @return bool
     */
    private function set($memberId, $accountId, $merchantId)
    {
        $this->memberId = $memberId;
        $this->accountId = $accountId;
        $this->merchantId = $merchantId;
        $this->timestamp = (int) time();
        return $this->saveStorage($this->getValue(), $this);
    }
}