<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-07-06
 */
namespace Uniondrug\Framework;

use Phalcon\Http\Request\Exception;
use Phalcon\Http\Request as PhalconRequest;

/**
 * 覆盖HTTP请求
 * @package Uniondrug\Framework
 */
class Request extends PhalconRequest
{
    /**
     * 是否MQ请求
     * @var bool
     */
    private $_mqRequest = false;
    /**
     * 消息ID
     * @var string
     */
    private $_mqId;
    /**
     * 主题名称
     * @var string
     */
    private $_mqTopic;
    /**
     * 主题TAG名称
     * @var string
     */
    private $_mqFilterTag;
    /**
     * 订阅名称
     * @var string
     */
    private $_mqSubscription;

    /**
     * 读取请求入参数据
     * @param boolean $associative
     * @return array|\stdClass
     */
    public function getJsonRawBody($associative = null)
    {
        $data = null;
        $assoc = $associative === true;
        // 1. 获取BODY
        $body = parent::getRawBody();
        if (!$this->generageRawBody($data, $body, $assoc)) {
            // 1.1 解析JSON出错
            return $data;
        }
        // 2. 数组模式
        if ($assoc) {
            // 2.1 是否为MQ消息
            if (isset($data['messageBodyMD5'], $data['messageBody']) && strtoupper($data['messageBodyMD5']) === strtoupper(md5($data['messageBody']))) {
                // 2.1.1 解析失败
                $temp = null;
                if (!$this->generageRawBody($temp, $data['messageBody'], $assoc)) {
                    return $temp;
                }
                // 2.1.2 必须字段
                if (!isset($temp['Message'], $temp['MessageMD5']) || strtoupper($temp['MessageMD5']) !== strtoupper(md5($temp['Message']))) {
                    return $this->generateError('not MQ message format', $data['messageBody'], $assoc);
                }
                // 2.1.3 消息内容
                $result = null;
                if (!$this->generageRawBody($result, $temp['Message'], $assoc) || !isset($result['body'])) {
                    return $this->generateError('not MQ message body', $temp['Message'], $assoc);
                }
                if (!isset($result['body']) || !$this->generageRawBody($result, $result['body'], $assoc)) {
                    return $result;
                }
                // 2.1.4 MQ消息处理
                $this->_mqRequest = true;
                $this->_mqId = isset($temp['MessageId']) ? $temp['MessageId'] : '';
                $this->_mqTopic = isset($temp['TopicName']) ? $temp['TopicName'] : '';
                $this->_mqSubscription = isset($temp['SubscriptionName']) ? $temp['SubscriptionName'] : '';
                $this->_mqFilterTag = isset($temp['MessageTag']) ? $temp['MessageTag'] : '';
                return $result;
            }
            // 2.2 普通请求
            return $data;
        }
        // 3. STD模式
        if (isset($data->messageBodyMD5, $data->messageBody) && strtoupper($data->messageBodyMD5) === strtoupper(md5($data->messageBody))) {
            $temp = null;
            // 3.1 解析失败
            if (!$this->generageRawBody($temp, $data->messageBody, $assoc)) {
                return $temp;
            }
            // 3.2 必须字段
            if (!isset($temp->Message, $temp->MessageMD5) || strtoupper($temp->MessageMD5) !== strtoupper(md5($temp->Message))) {
                return $this->generateError('not MQ message format', $data->messageBody, $assoc);
            }
            // 3.3 消息内容
            $result = null;
            if (!$this->generageRawBody($result, $temp->Message, $assoc) || !isset($result->body)) {
                return $this->generateError('not MQ message body', $data->messageBody, $assoc);
            }
            if (!isset($result->body) || !$this->generageRawBody($result, $result->body, $assoc)) {
                return $result;
            }
            // 3.4 MQ消息处理
            $this->_mqRequest = true;
            $this->_mqId = isset($temp->MessageId) ? $temp->MessageId : '';
            $this->_mqTopic = isset($temp->TopicName) ? $temp->TopicName : '';
            $this->_mqSubscription = isset($temp->SubscriptionName) ? $temp->SubscriptionName : '';
            $this->_mqFilterTag = isset($temp->MessageTag) ? $temp->MessageTag : '';
            return $result;
        }
        return $data;
    }

    /**
     * 转为JSON字符串
     * @param array|\stdClass $data
     * @param string          $body
     * @param bool            $assoc
     * @return bool
     */
    private function generageRawBody(& $data, $body, $assoc = false)
    {
        $data = json_decode($body, $assoc);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $this->generateError(json_last_error_msg(), $body, $assoc);
            return false;
        }
        return true;
    }

    /**
     * @param string $error
     * @param string $raw
     * @param bool   $assoc
     * @return array|\stdClass
     */
    private function generateError($error, $raw, $assoc = false)
    {
        if ($assoc) {
            return [
                '_raw' => $raw,
                '_error' => $error
            ];
        }
        $std = new \stdClass();
        $std->_raw = $raw;
        $std->_error = $error;
        return $std;
    }

    /**
     * 计算HTTP请求类型
     * @return string
     */
    public function getMethodReplacement()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $returnMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        } else {
            return "GET";
        }
        if ("POST" === $returnMethod) {
            $overrideMethod = $this->getHeader("X-HTTP-METHOD-OVERRIDE");
            if (!empty($overrideMethod)) {
                $returnMethod = strtoupper($overrideMethod);
            } else if ($this->_httpMethodParameterOverride) {
                if (isset($_REQUEST['_method'])) {
                    $returnMethod = strtoupper($_REQUEST['_method']);
                }
            }
        }
        if (!$this->isValidHttpMethod($returnMethod)) {
            return "GET";
        }
        return $returnMethod;
    }

    /**
     * 读取MQ消息ID
     * @return string
     */
    public function getMqId()
    {
        return $this->_mqId;
    }

    /**
     * 读取MQ的订阅名称
     * @return string
     */
    public function getMqSubscription()
    {
        return $this->_mqSubscription;
    }

    /**
     * 读取MQ消息的Tag名称
     * @return string
     */
    public function getMqTag()
    {
        return $this->_mqFilterTag;
    }

    /**
     * 读取MQ消息的Topic名称
     * @return string
     */
    public function getMqTopic()
    {
        return $this->_mqTopic;
    }

    /**
     * 是否为Connect请求
     * @return bool
     */
    public function isConnect()
    {
        return $this->getMethodReplacement() === 'CONNECT';
    }

    /**
     * 是否为Delete请求
     * @return bool
     */
    public function isDelete()
    {
        return $this->getMethodReplacement() === 'DELETE';
    }

    /**
     * 是否为Get请求
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethodReplacement() === 'GET';
    }

    /**
     * 是否为Head请求
     * @return bool
     */
    public function isHead()
    {
        return $this->getMethodReplacement() === 'HEAD';
    }

    /**
     * 是否为Options请求
     * @return bool
     */
    public function isOptions()
    {
        return $this->getMethodReplacement() === 'OPTIONS';
    }

    /**
     * 是否为Patch请求
     * @return bool
     */
    public function isPatch()
    {
        return $this->getMethodReplacement() === 'PATCH';
    }

    /**
     * 请求类型是否合法
     * @param string|array $methods
     * @param null         $strict
     * @return bool
     * @throws Exception
     */
    public function isMethod($methods, $strict = null)
    {
        $httpMethod = $this->getMethodReplacement();
        if (is_string($methods)) {
            if ($strict && !$this->isValidHttpMethod($methods)) {
                throw new Exception("Invalid HTTP method: ".$methods);
            }
            return $methods == $httpMethod;
        }
        if (is_array($methods)) {
            foreach ($methods as $method) {
                if ($this->isMethod($method, $strict)) {
                    return true;
                }
            }
            return false;
        }
        if ($strict) {
            throw new Exception("Invalid HTTP method: non-string");
        }
        return false;
    }

    public function isMqRequest(){
        return $this->_mqRequest;
    }

    /**
     * 是否为Post请求
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethodReplacement() === 'POST';
    }

    /**
     * 是否为Purge请求
     * @return bool
     */
    public function isPurge()
    {
        return $this->getMethodReplacement() === 'PURGE';
    }

    /**
     * 是否为Put请求
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethodReplacement() === 'PUT';
    }

    /**
     * 是否为Trace请求
     * @return bool
     */
    public function isTrace()
    {
        return $this->getMethodReplacement() === 'TRACE';
    }

    /**
     * Set in memory Put cache
     * @param null $data
     * @return $this
     */
    public function setPutCache($data = null)
    {
        $this->_putCache = $data;
        return $this;
    }

    /**
     * Set request raw body
     * @param null $body
     * @return $this
     */
    public function setRawBody($body = null)
    {
        $this->_rawBody = $body;
        return $this;
    }
}
