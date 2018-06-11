<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-21
 */

namespace Uniondrug\Framework\Errors;

use Uniondrug\Framework\Container;

/**
 * @package Uniondrug\Framework\Errors
 */
abstract class Error extends \Exception
{
    /**
     * @var string
     */
    private static $codeClassName;

    /**
     * @param int         $code    错误原始码
     * @param string|null $message 自定义错误原因
     * @param array       ...$args 规则自定义错误原因中的参数
     *
     * @example throw new Error(Code::FAILURE_CREATE, "添加'%s'致'%s'失败", "参数1", "参数2")
     */
    final public function __construct(int $code, string $message = null, ... $args)
    {
        $this->initCodeInstance();
        // 1. error message
        if ($message === null) {
            $message = call_user_func_array(self::$codeClassName . '::getMessage', [$code]);
        } else {
            if (is_array($args) && count($args) > 0) {
                array_unshift($args, $message);
                $message = call_user_func_array('sprintf', $args);
            }
        }
        // 2. error number
        $errno = call_user_func_array(self::$codeClassName . '::getCode', [$code]);
        // 3. save logger
        $this->saveErrorLogger($message);
        // 4. call parent
        parent::__construct($message, $errno, null);
    }

    /**
     * 初始化错误码定义实例
     *
     * @return void
     * @throws \ReflectionException
     */
    private function initCodeInstance()
    {
        // 1. once reflection
        if (self::$codeClassName !== null) {
            return;
        }
        // 2. refelection check
        $reflect = new \ReflectionClass($this);
        $className = $reflect->getNamespaceName() . '\\Code';
        if (is_subclass_of($className, Code::class, true)) {
            self::$codeClassName = $className;

            return;
        }
        // 3. framework
        self::$codeClassName = Code::class;
    }

    /**
     * 保存错误日志
     * ```bash
     * cd log/error
     * more date.log | grep 'ERROR'
     * more data.log | grep 'traceid'
     * ```
     *
     * @param string $message
     */
    private function saveErrorLogger(string $message)
    {
        /**
         * @var Container $container
         * @var string    $traceId 错误跟踪ID
         */
        // 1. trace
        $times = explode(' ', microtime(false));
        $traceId = sprintf("trace%d%06d%04d", $times[1], $times[0] * 100000, mt_rand(1001, 9999));
        // 2. message add trace
        $message = "[{$traceId}] - {$message}\n" . $this->getTraceAsString();
        $message = preg_replace("/\n#(\d+)/", "\n[{$traceId}] - #\\1", $message);
        \logger('error')->error($message);
    }
}