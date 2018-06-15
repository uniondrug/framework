<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-06-14
 */
namespace Uniondrug\Framework\Logics;

use Uniondrug\Framework\Injectable;
use Uniondrug\Framework\Services\ServiceTrait;
use Uniondrug\Structs\StructInterface;

/**
 * 业务逻辑抽像
 * @package Uniondrug\Framework\Logics
 */
abstract class Logic extends Injectable implements LogicInterface
{
    use ServiceTrait;
    /**
     * 延迟发送
     * `0` : 不延迟
     * `n` : 延迟时长(单位: 秒)
     * @var int
     */
    public $topicDelay = 0;
    /**
     * Topic名称
     * @var bool|string
     */
    public $topicName = false;
    /**
     * Topic标签
     * @var bool|string
     */
    public $topicTag = false;

    /**
     * 逻辑工厂
     * @param array|null|object $payload 入参
     * @return array|StructInterface 逻辑执行结果
     */
    public static function factory($payload = null)
    {
        $logic = new static();
        $struct = $logic->run($payload);
        if ($struct instanceof StructInterface) {
            $logic->afterFactory($struct);
        }
        return $struct;
    }

    /**
     * 读取延迟时长
     * 指定消息来发送时间开始, N秒后才可被消费
     * @return int
     */
    public function getTopicDelay()
    {
        if (is_numeric($this->topicDelay) && $this->topicDelay > 0) {
            return $this->topicDelay;
        }
        return 0;
    }

    /**
     * 读取MQ消息名称
     * 该方法可以子类中覆盖, 定义MQ的Topic名称,
     * 默认为false, 即不发送MQ消息
     * @return string|false
     */
    public function getTopicName()
    {
        return $this->topicName;
    }

    /**
     * 读取MQ消息标签
     * 该方法可以子类中覆盖, 定义消息的Tag名称,
     * 默认为实例逻辑的类名
     * @return string
     */
    public function getTopicTag()
    {
        // 1. 从覆盖属性中读取
        if ($this->topicTag !== false) {
            return $this->topicTag;
        }
        // 2. 计算Logic名称
        $names = explode('\\', get_class($this));
        $length = count($names) - 1;
        if ($length >= 0) {
            return strtoupper($names[$length]);
        }
        // 3. 非法名称
        return false;
    }

    /**
     * Logic执行完成之后的MQ业务检查
     * @param StructInterface $struct
     */
    final public function afterFactory(StructInterface $struct)
    {
        $data = ['message' => '', 'delay' => $this->getTopicDelay()];
        // 1. Topic名称必须
        $data['topicName'] = $this->getTopicName();
        if (!$data['topicName']) {
            return;
        }
        // 2. FilterTag必须
        //    2.1 从属性或覆盖方法中读取
        //    2.2 类名中读取
        $data['filterTag'] = $this->getTopicTag();
        if (!$data['filterTag']) {
            return;
        }
        // 3. 计算消息内容
        $jsonOption = JSON_UNESCAPED_UNICODE;
        $message = ['body' => $struct->toJson($jsonOption)];
        $data['message'] = json_encode($message, $jsonOption);
        // 4. 消息Logger
        $uuid = uniqid();
        $logger = $this->di->getLogger('mbs');
        $logger->info("[{$uuid}] - 准备MQ消息");
        $logger->info("[{$uuid}] - 用[{$data['filterTag']}]标签, 发到[{$data['topicName']}]主题");
        $logger->info("[{$uuid}] - 消息内容 - {$data['message']}");
        // 5. 开始发送
        $logger->info("[{$uuid}] - 开始发送MQ消息");
        $response = $this->serviceSdk->mbs->publish($data);
        if ($response->hasError()) {
            $logger->info("[{$uuid}] - 发送MQ消息失败 - ({$response->getErrno()})".$response->getError());
            return;
        }
        // 6. 发送完成
        $logger->info("[{$uuid}] - 发送MQ消息完成 - ".$response->__toString());
    }
}
