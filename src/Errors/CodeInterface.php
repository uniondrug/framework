<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-03-21
 */
namespace Uniondrug\Framework\Errors;

/**
 * 错误定义接口
 * @package Uniondrug\Framework\Errors
 */
interface CodeInterface
{
    /**
     * 用原始错误码获取应用错误码
     *
     * @param int $code
     *
     * @return int
     */
    public static function getCode(int $code);

    /**
     * 按原始错误码获取错误原因
     *
     * @param int $code 原始错误码
     *
     * @return string
     */
    public static function getMessage(int $code);
}
