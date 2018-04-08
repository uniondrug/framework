<?php
/**
 * Request.php
 */
namespace Uniondrug\Framework;

use Phalcon\Http\Request\Exception;

class Request extends \Phalcon\Http\Request
{
    /**
     * @param boolean $associative
     * @return array|\stdClass
     */
    public function getJsonRawBody($associative = null)
    {
        $json = parent::getJsonRawBody($associative);
        if (json_last_error()) {
            if ($associative) {
                return [];
            }
            return new \stdClass();
        }
        return $json;
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
     * Replace method for getMethod() which cause memory leak with swoole.
     * @return string
     */
    public function getMethodReplacement()
    {
        $returnMethod = "";
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function isPost()
    {
        return $this->getMethodReplacement() === 'POST';
    }

    /**
     * @inheritdoc
     */
    public function isGet()
    {
        return $this->getMethodReplacement() === 'GET';
    }

    /**
     * @inheritdoc
     */
    public function isPut()
    {
        return $this->getMethodReplacement() === 'PUT';
    }

    /**
     * @inheritdoc
     */
    public function isHead()
    {
        return $this->getMethodReplacement() === 'HEAD';
    }

    /**
     * @inheritdoc
     */
    public function isPatch()
    {
        return $this->getMethodReplacement() === 'PATCH';
    }

    /**
     * @inheritdoc
     */
    public function isDelete()
    {
        return $this->getMethodReplacement() === 'DELETE';
    }

    /**
     * @inheritdoc
     */
    public function isOptions()
    {
        return $this->getMethodReplacement() === 'OPTIONS';
    }

    /**
     * @inheritdoc
     */
    public function isPurge()
    {
        return $this->getMethodReplacement() === 'PURGE';
    }

    /**
     * @inheritdoc
     */
    public function isTrace()
    {
        return $this->getMethodReplacement() === 'TRACE';
    }

    /**
     * @inheritdoc
     */
    public function isConnect()
    {
        return $this->getMethodReplacement() === 'CONNECT';
    }
}
