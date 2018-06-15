<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-06-15
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Logger\Formatter;
use Phalcon\Logger\FormatterInterface;

/**
 * 日志格式化
 * @package Uniondrug\Framework\Providers
 */
class LoggerFormatter extends Formatter implements FormatterInterface
{
    private $timeformat = 'H:i:s.u O';

    /**
     * 格式化日志输出
     * @param string $message
     * @param int    $type
     * @param int    $timestamp
     * @param null   $context
     * @return string
     */
    public function format($message, $type, $timestamp, $context = null)
    {
        $date = new \DateTime('now');
        $text = '['.$date->format($this->timeformat).']';
        $text .= '['.$this->getTypeString($type).'] ';
        $text .= $message;
        return $text."\n";
    }

    /**
     * 设置时间格式
     * @param string $timeformat
     * @return $this
     */
    public function setTimeformat($timeformat)
    {
        $this->timeformat = $timeformat;
        return $this;
    }
}
