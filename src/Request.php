<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-07-06
 */
namespace Uniondrug\Framework;

use Phalcon\Http\Request\Exception;
use Phalcon\Http\Request as PhalconRequest;
use stdClass;

/**
 * 覆盖HTTP请求
 * @package Uniondrug\Framework
 */
class Request extends PhalconRequest
{
    /**
     * 读取请求入参数据
     * @param boolean $associative
     * @return array|stdClass
     */
    public function getJsonRawBody($associative = null)
    {
        $body = $this->getRawBody();
        // 1. 转为Array/StdClass
        $data = json_decode($body, $associative);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
        // 2. Array错误
        if ($associative) {
            return [
                '_raw' => $body,
                '_err' => json_last_error_msg()
            ];
        }
        // 3. JSON错误
        $data = new stdClass();
        $data->_err = json_last_error_msg();
        $data->_raw = $body;
        return $data;
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
     * MBS发过来的消息ID
     * @return string
     */
    public function getMbsId()
    {
        return $this->getHeader('MBS-ID');
    }

    /**
     * MBS发过来的消息所在队列
     * @return string
     */
    public function getMbsQueue()
    {
        return $this->getHeader('MBS-QUEUE');
    }

    /**
     * MBS发过来的消息所在标签
     * @return string
     */
    public function getMqTag()
    {
        return $this->getHeader('MBS-TAG');
    }

    /**
     * MBS发过来的消息所在主题
     * @return string
     */
    public function getMbsTopic()
    {
        return $this->getHeader('MBS-TOPIC');
    }

    /**
     * [MBS]消息所以主题的MESSAGEID
     * @return string
     */
    public function getMbsTopicId()
    {
        return $this->getHeader('MBS-TOPIC-ID');
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
