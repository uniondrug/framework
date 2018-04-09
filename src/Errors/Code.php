<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-21
 */
namespace Uniondrug\Framework\Errors;

/**
 * 框架级错误码定义
 * @package Uniondrug\Framework\Errors
 */
abstract class Code
{
    /**
     * 获取错误码时, 在原错误的基础之上再加上
     * 固定的整型值
     * @var int
     */
    protected static $codePlus = 0;
    /**
     * 错误码对应的原始文本
     * <code>
     * $codeMessages = [
     *     Code::CONSTANT_NAME => '文本内容',
     * ]
     * <code>
     * @var array
     */
    protected static $codeMessages = [];
    /**
     * @var string
     */
    protected static $unknownText = 'unknown message';

    /**
     * 用原始错误码获取应用错误码
     * @param int $code
     * @return int
     */
    public static function getCode(int $code)
    {
        return static::$codePlus + $code;
    }

    /**
     * 按原始错误码获取错误原因
     * @param int $code 原始错误码
     * @return string
     */
    public static function getMessage(int $code)
    {
        if (isset(static::$codeMessages[$code])) {
            return static::$codeMessages[$code];
        }
        return static::$unknownText;
    }

    /**
     * 导出MARKDOWN格式编码文档
     * @return string
     * @example Error::exportMarkdown()
     */
    public static function exportMarkdown()
    {
        $message = '| 编码 | 常量名 | 用途与描述 |';
        $message .= "\r\n".'| :-- | :-- | :-- |';
        $instance = new static();
        $refelect = new \ReflectionClass($instance);
        foreach ($refelect->getConstants() as $name => $code) {
            $message .= "\r\n";
            $message .= sprintf("| %d | %s | %s |", static::$codePlus + $code, $name, static::getMessage($code));
        }
        return $message;
    }
}
