<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-19
 */
namespace Uniondrug\Framework\Models;

use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * 全局模块配置
 * disableEvents=false, Enables/Disables globally the internal events
 * columnRenaming=true, Enables/Disables column renaming
 * notNullValidations=true, Enables/Disables automatic not null validation
 * exceptionOnFailedSave=true, Enables/Disables throws an exception if the saving process fails
 * phqlLiterals=false, Enables/Disables literals in PHQL this improves the security of applications
 * virtualForeignKeys=true, Enables/Disables virtual foreign keys
 * lateStateBinding=false, Enables/Disables late state binding on model hydration
 * castOnHydrate=false, Enables/Disables automatic cast to original types on hydration
 * ignoreUnknownColumns=true, Allows to ignore unknown columns when hydrating objects
 * updateSnapshotOnSave=false
 * disableAssignSetters=false
 */
Model::setup([
    'disableEvents' => false,
    'columnRenaming' => true,
    'notNullValidations' => true,
    'exceptionOnFailedSave' => true,
    'phqlLiterals' => true,
    'virtualForeignKeys' => true,
    'lateStateBinding' => false,
    'castOnHydrate' => false,
    'ignoreUnknownColumns' => true,
    'updateSnapshotOnSave' => true,
    'disableAssignSetters' => false
]);

/**
 * Model基类
 * @property string $gmtCreated 创建时间(Y-m-d H:i:s; 如 2018-03-01 08:09:10)
 * @property string $gmtUpdated 修改时间(Y-m-d H:i:s; 如 2018-03-10 11:12:13)
 * @package App\Models
 */
abstract class Model extends PhalconModel
{
    const MODEL_TIME_FIELD_CREATED = 'gmtCreated';
    const MODEL_TIME_FIELD_UPDATED = 'gmtUpdated';
    const MODEL_TIME_FORMAT_DATE = 'Y-m-d';
    const MODEL_TIME_FORMAT_DATETIME = 'Y-m-d H:i';
    const MODEL_TIME_FORMAT_TIME = 'H:i';

    /**
     * 覆盖SlaveConnection
     * 当处于事务状态时, 固定返回writeConnection
     */
    public function getReadConnection()
    {
        /**
         * @var \Phalcon\Db\Adapter\Pdo $connection
         */
        $connection = $this->getWriteConnection();
        if ($connection->isUnderTransaction()) {
            return $connection;
        }
        return parent::getReadConnection();
    }

    /**
     * 记录添加日期
     * @return string
     * @example return '2018-01-10';
     */
    final public function getCreatedDate()
    {
        return $this->getModelTimeformat(static::MODEL_TIME_FIELD_CREATED, static::MODEL_TIME_FORMAT_DATE);
    }

    /**
     * 记录添加完整时间
     * @return string
     * @example return '2018-01-10 13:00';
     */
    final public function getCreatedDatetime()
    {
        return $this->getModelTimeformat(static::MODEL_TIME_FIELD_CREATED, static::MODEL_TIME_FORMAT_DATETIME);
    }

    /**
     * 记录添加时间
     * @return string
     * @example return '13:00';
     */
    final public function getCreatedTime()
    {
        return $this->getModelTimeformat(static::MODEL_TIME_FIELD_CREATED, static::MODEL_TIME_FORMAT_TIME);
    }

    /**
     * 最后修改日期
     * @return string
     * @example return '2018-01-10';
     */
    final public function getUpdatedDate()
    {
        return $this->getModelTimeformat(static::MODEL_TIME_FIELD_UPDATED, static::MODEL_TIME_FORMAT_DATE);
    }

    /**
     * 最后修改完整时间
     * @return string
     * @example return '2018-01-10 13:00';
     */
    final public function getUpdatedDatetime()
    {
        return $this->getModelTimeformat(static::MODEL_TIME_FIELD_UPDATED, static::MODEL_TIME_FORMAT_DATETIME);
    }

    /**
     * 最后修改时间
     * @return string
     * @example return '13:00';
     */
    final public function getUpdatedTime()
    {
        return $this->getModelTimeformat(static::MODEL_TIME_FIELD_UPDATED, static::MODEL_TIME_FORMAT_TIME);
    }

    /**
     * 时间格式化
     * @param string $name   字段名称
     * @param string $format 格式化
     * @return string
     */
    final public function getModelTimeformat($name, $format)
    {
        $value = isset($this->{$name}) ? $this->{$name} : null;
        if ($value !== null) {
            if (($timestamp = strtotime($value)) !== false) {
                return date($format, $timestamp);
            }
        }
        return '';
    }

    /**
     * Model初始化
     */
    public function initialize()
    {
        // 1. 动态更新
        //    即没有变化的字段在update时不会出现在sql里面。
        //    否则每次都是全字段更新。
        $this->useDynamicUpdate(true);
        // 2. 全局时间
        //    数据Insert和最后的Update时间
        $this->addBehavior(new Timestampable([
            'beforeCreate' => [
                'field' => [
                    'gmtCreated',
                    'gmtUpdated'
                ],
                'format' => 'Y-m-d H:i:s',
            ],
            'beforeUpdate' => [
                'field' => 'gmtUpdated',
                'format' => 'Y-m-d H:i:s',
            ]
        ]));
        // 3. 读写分离
        //    当未在config/database.php中配置slave
        //    连接参数时, 将直接使用master配置
        $this->setWriteConnectionService("db");
        $this->setReadConnectionService("dbSlave");
    }

    /**
     * 模型数据转JSON字符串
     * @return string
     */
    public function toJson()
    {
        $data = $this->parseArrayDataType($this->toArray());
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $data
     * @return array
     */
    private function parseArrayDataType($data)
    {
        foreach ($data as & $value) {
            $t = strtolower(gettype($value));
            switch ($t) {
                case 'bool' :
                case 'boolean' :
                    $value = $value ? '1' : '0';
                    break;
                case 'double' :
                case 'float' :
                case 'int' :
                case 'integer' :
                    $value = (string) $value;
                    break;
                case 'null' :
                    $value = '';
                    break;
                case 'array' :
                    $value = $this->parseArrayDataType($value);
                    break;
            }
        }
        return $data;
    }
}
