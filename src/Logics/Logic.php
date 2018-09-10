<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-06-14
 */
namespace Uniondrug\Framework\Logics;

use stdClass;
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
     * 消息优选级
     * 范围: 1~16
     * 排序: 小到大, 即1为最高优先级
     * 默认: 8
     */
    const LOGIC_DEFAULT_TOPIC_PRIORITY = 8;
    /**
     * MQ消息主题名称
     * @var null|string
     */
    protected $topicName = null;
    /**
     * MQ消息标签名
     * @var null|string
     */
    protected $topicTag = null;
    /**
     * MQ消息优先级
     * @var int
     */
    protected $topicPriority = 0;
    /**
     * 待发送的消息内容列表
     * @var array
     */
    private $topicBodies = [];

    /**
     * 结构体工厂
     * @param array|stdClass|null $payload
     * @return mixed
     */
    public static function factory($payload)
    {
        // 1. new实例
        $logic = new static();
        // 2. run过程
        $logic->beforeRun();
        $result = $logic->run($payload);
        $logic->afterRun($result);
        // 3. 默认消息内容
        //    a): 在run/afterRun()中未调用过addTopicBody()方法
        //    b): run()返回array|string|StructInterface时作为默认消息内容
        $count = $logic->getTopicBodyCount();
        if ($count === 0) {
            if ($result instanceof StructInterface || in_array(gettype($result), [
                    'array',
                    'string'
                ])
            ) {
                $logic->addTopicBody($result);
            }
        }
        $logic->afterFactory($count);
        // 4. 最后返回结果
        return $result;
    }

    /**
     * @param array|string|StructInterface $result
     * @return $this
     */
    public function addTopicBody($result)
    {
        $this->topicBodies[] = $result;
        return $this;
    }

    /**
     * 后续业务
     * 1. 检查是否需要发MQ消息
     * 2. 当需要发时自动切换发送方式
     * @param int $count
     */
    public function afterFactory(int $count)
    {
        // 1. 是否需要发MQ消息
        $name = $this->getTopicName();
        $tag = $this->getTopicTag();
        if (!$name || !$tag) {
            return;
        }
        $priority = $this->getTopicPriority();
        $count > 1 ? $this->callMBSBatch($name, $tag, $priority) : $this->callMBSPublish($name, $tag, $priority);
    }

    /**
     * run()运行之后
     * @param mixed $result 值为run()方法的出参结果
     */
    public function afterRun($result)
    {
    }

    /**
     * 运行run()方法之前
     */
    public function beforeRun()
    {
    }

    /**
     * 待发送的消息数量
     * @return int
     */
    public function getTopicBodyCount()
    {
        return count($this->topicBodies);
    }

    /**
     * 读取MQ消息主题
     * @return false|string
     */
    public function getTopicName()
    {
        $topic = $this->topicName;
        if (is_string($topic) && $topic !== '') {
            return $topic;
        }
        return false;
    }

    /**
     * 读取MQ消息标签
     * @return false|string
     */
    public function getTopicTag()
    {
        $tag = $this->topicTag;
        if (is_string($tag) && $tag !== '') {
            return $tag;
        }
        return false;
    }

    /**
     * 读取MQ消息优先级
     * @return int
     */
    public function getTopicPriority()
    {
        $priority = $this->topicPriority;
        if (is_numeric($priority) && $priority >= 1 && $priority <= 16) {
            return (int) $priority;
        }
        return self::LOGIC_DEFAULT_TOPIC_PRIORITY;
    }

    /**
     * 发送消息到MBS
     * @param string $topicName
     * @param string $topicTag
     * @param int    $priority
     */
    private function callMBSBatch(string $topicName, string $topicTag, int $priority)
    {
        // 1. 组织消息内容
        $contents = [
            'topicName' => $topicName,
            'topicTag' => $topicName,
            'priority' => $priority,
            'messages' => []
        ];
        // 2. 批量消息
        foreach ($this->topicBodies as $body) {
            if ($body instanceof StructInterface) {
                $contents['messages'][] = $body;
            } else if (is_array($body) || is_string($body)) {
                $contents['messages'][] = $body;
            }
        }
        // 3. 调用SDK
        $this->serviceSdk->mbs2->batch($contents, [
            'headers' => [
                'mbs-app' => $this->config->path('app.appName'),
                'mbs-unique' => uniqid()
            ]
        ]);
    }

    /**
     * 向MBS发送一条消息
     * @param string $topicName
     * @param string $topicTag
     * @param int    $priority
     */
    private function callMBSPublish(string $topicName, string $topicTag, int $priority)
    {
        // 1. 组织消息内容
        $contents = [
            'topicName' => $topicName,
            'topicTag' => $topicName,
            'priority' => $priority,
            'message' => []
        ];
        // 2. 单条消息
        $message = $this->topicBodies[0];
        if ($message instanceof StructInterface) {
            $contents['message'] = $message->toArray();
        } else if (is_array($message) || is_string($message)) {
            $contents['message'] = $message;
        }
        // 3. 调用SDK
        $this->serviceSdk->mbs2->publish($contents, [
            'headers' => [
                'mbs-app' => $this->config->path('app.appName'),
                'mbs-unique' => uniqid()
            ]
        ]);
    }
}
