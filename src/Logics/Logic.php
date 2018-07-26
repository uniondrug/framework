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
     * 是否批量消息
     * @var bool
     */
    public $topicBatch = false;
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
     * MQ默认优先级
     * @var int
     */
    public $priority = 0;
    /**
     * @var int
     */
    private $defaultPriority = 8;
    private $topicBatches = [];

    /**
     * 逻辑工厂
     * @param array|null|object $payload 入参
     * @return array|StructInterface 逻辑执行结果
     */
    public static function factory($payload = null)
    {
        $logic = new static();
        $struct = $logic->run($payload);
        $logic->afterFactory($struct);
        return $struct;
    }

    /**
     * 分批执行
     * @param array $datas
     * @return $this
     */
    protected function addTopicBatch(array $datas)
    {
        $this->topicBatches[] = $datas;
        return $this;
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
     * 读取MQ优先级
     * @return int
     */
    public function getTopicPriority()
    {
        if (is_numeric($this->priority) && $this->priority > 0) {
            return $this->priority;
        }
        return $this->defaultPriority;
    }

    /**
     * 读取MQ消息标签
     * 该方法可以子类中覆盖, 定义消息的Tag名称,
     * 默认为实例逻辑的类名
     * @return string
     */
    public function getTopicTag()
    {
        return $this->topicTag;
    }

    /**
     * Logic执行完成之后的MQ业务检查
     * @param StructInterface $struct
     * @return mixed
     */
    final public function afterFactory($struct)
    {
        $data = [];
        // 1. 检查是否需要发送MQ消息
        $data['topicName'] = $this->getTopicName();
        $data['filterTag'] = $this->getTopicTag();
        if (!$data['topicName'] || !$data['filterTag']) {
            return false;
        }
        // 2. 消息属性
        $logger = $this->di->getLogger('mbs');
        $data['priority'] = $this->getTopicPriority();
        $data['delaySeconds'] = $this->getTopicDelay();
        // 3. 单条发送
        if (!$this->topicBatch) {
            $data['message'] = '';
            if (count($this->topicBatches) > 0) {
                $data['message'] = json_encode($this->topicBatches[0], JSON_UNESCAPED_UNICODE);
            } else if ($struct instanceof StructInterface) {
                $data['message'] = $struct->toJson();
            }
            // 4. 空消息忽略
            if ($data['message'] === '') {
                $logger->error("[".__METHOD__."] - MQ消息内容为空, 忽略发送");
                return false;
            }
            // 5. 开始发送
            $response = $this->serviceSdk->mbs->publish($data);
            if ($response->hasError()) {
                $logger->error("[".__METHOD__."] - MQ消息发送失败 - ".$response->__toString());
                return false;
            }
            // 6. 发送成功
            $logger->info("[".__METHOD__."] - MQ消息发送成功 - ".$response->__toString());
            return true;
        }
        // 7. 批量执行
        $offset = 0;
        $uniqid = uniqid();
        $logger->info("[".__METHOD__."][{$uniqid}] - 发送批量消息开始");
        foreach ($this->topicBatches as $batch) {
            $response = $this->serviceSdk->mbs->batch([
                'base' => $data,
                'messages' => $batch
            ]);
            $offset++;
            if ($response->hasError()) {
                $logger->error("[".__METHOD__."][{$uniqid}] - 第{$offset}批失败 - 用时{$response->getDuration()}秒 - ".$response->__toString());
            } else {
                $logger->info("[".__METHOD__."][{$uniqid}] - 第{$offset}批成功 - 用时{$response->getDuration()}秒 -".$response->__toString());
            }
        }
        $logger->info("[".__METHOD__."][{$uniqid}] - 发送批量消息结束 - 共{$offset}批");
        return true;
    }
}
