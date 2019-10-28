<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-07-06
 */
namespace Uniondrug\Framework;

use Phalcon\Di;

/**
 * 请求链
 * @package Uniondrug\Framework
 */
class Trace extends Injectable
{
    const PREFIX = "X-B3-";
    const TRACE_NAME = "Traceid";
    const SPAN_NAME = "Spanid";
    const PARENT_SPAN_NAME = "Parentspanid";
    const SAMPLED_NAME = "Sampled";
    private $traceId;               // 主链ID
    private $spanId;                // 请求ID
    private $parentSpanId;          // 上级请求ID
    private $sampled;               // 抽样
    private $defaultSampled = '0';  // 默认抽样值
    private $loggerString = '';     // 日志前缀
    private $taskTrace = false;     // 是否为Task请求

    /**
     * 容器对象
     * @return Container
     */
    public function getContainer()
    {
        $container = Di::getDefault();
        if ($container instanceof Di) {
            return $container;
        }
        throw new \RuntimeException("unknown trace container");
    }

    /**
     * 请求对象
     * @return Request
     */
    public function getRequest()
    {
        $request = $this->getContainer()->getShared('request');
        if ($request instanceof \Phalcon\Http\Request) {
            return $request;
        }
        throw new \RuntimeException("unknown trace request");
    }

    /**
     * 重设置参数
     * @param bool $taskTrace
     */
    public function reset(bool $taskTrace = false)
    {
        $this->taskTrace = $taskTrace;
        $request = $this->getContainer()->getRequest();
        // 1. 主链ID
        $this->traceId = (string) $request->getHeader(self::PREFIX.self::TRACE_NAME);
        $this->traceId === '' && $this->traceId = $this->makeTraceId();
        // 2. 上级SpanID
        $this->parentSpanId = (string) $this->request->getHeader(self::PREFIX.self::PARENT_SPAN_NAME);
        // 3. SpanID
        $this->spanId = (string) $this->request->getHeader(self::PREFIX.self::SPAN_NAME);
        $this->spanId === '' && $this->spanId = $this->traceId;
        // 4. Sampled
        $this->sampled = (string) $this->request->getHeader(self::PREFIX.self::SAMPLED_NAME);
        $this->sampled === '' && $this->sampled = $this->defaultSampled;
        // 5. Logger String
        $this->loggerString = sprintf("[traceid=%s][parentspanid=%s][spanid=%s]", $this->traceId, $this->parentSpanId, $this->spanId);
    }

    /**
     * 读取写入日志的字符串
     * @return string
     */
    public function getLoggerString()
    {
        return $this->loggerString;
    }

    /**
     * 读取请求主链
     * @return string
     */
    public function getRequestId()
    {
        return $this->traceId;
    }

    /**
     * 读取请求参数
     * @return array
     */
    public function getRequestHeaders()
    {
        $headers = [];
        $headers[self::PREFIX.self::TRACE_NAME] = $this->traceId;
        $headers[self::PREFIX.self::PARENT_SPAN_NAME] = $this->parentSpanId;
        $headers[self::PREFIX.self::SPAN_NAME] = $this->spanId;
        $headers[self::PREFIX.self::SAMPLED_NAME] = $this->sampled;
        return $headers;
    }

    /**
     * 读取当前请求ID
     * @return string
     */
    public function getSpanId()
    {
        return $this->spanId;
    }

    /**
     * 读取下个请求
     * 在当前请求中, 请求其它服务时, 传递参数给下级
     * 服务
     * @return array
     */
    public function getTraceHeaders()
    {
        $headers = [];
        $headers[self::PREFIX.self::TRACE_NAME] = $this->traceId;
        $headers[self::PREFIX.self::PARENT_SPAN_NAME] = $this->spanId;
        $headers[self::PREFIX.self::SPAN_NAME] = $this->makeTraceId();
        $headers[self::PREFIX.self::SAMPLED_NAME] = $this->sampled;
        return $headers;
    }

    /**
     * 生成主链ID
     * 只有在入口级
     * @return string
     */
    public function makeTraceId()
    {
        $tm = explode(' ', microtime(false));
        return sprintf("%s%d%06d%d%d", $this->taskTrace ? 't' : 'r', $tm[1], $tm[0], mt_rand(1000000, 9999999), mt_rand(10000000, 99999999));
    }
}
