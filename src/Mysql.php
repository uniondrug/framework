<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-04-10
 */
namespace Uniondrug\Framework;

/**
 * 共享的MySQL实例
 * @package Uniondrug\Framework
 */
class Mysql extends \Phalcon\Db\Adapter\Pdo\Mysql
{
    /**
     * 共享名称
     * @var string
     */
    private $_sharedName;
    private $_sharedDbname;

    /**
     * 读取完整SQL语句
     * 在Logger中写入SQL完整语句时, 按SQL入参替换占位符
     * 实现完整SQL渲染
     * @return string
     */
    public function getListenerSQLStatment()
    {
        $sql = $this->getSQLStatement();
        $vars = $this->getSqlVariables();
        if (!is_array($vars)) {
            return $sql;
        }
        foreach ($vars as $key => $value) {
            if (!is_numeric($value)) {
                if (is_string($value)) {
                    $value = "'".addslashes(stripslashes($value))."'";
                } else if (is_null($value)) {
                    $value = "NULL";
                } else {
                    $value = '{{'.gettype($value).'}}';
                }
            }
            $sql = str_replace(":{$key}", $value, $sql);
        }
        return $sql;
    }

    /**
     * 读取共享名称
     * @return string
     */
    public function getSharedName()
    {
        return $this->_sharedName;
    }

    /**
     * @return string
     */
    public function getSharedDbname()
    {
        return $this->_sharedDbname;
    }

    /**
     * 设置共享名称
     * @param string $name
     * @return $this
     */
    public function setSharedName(string $name)
    {
        $this->_sharedName = $name;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setSharedDbname(string $name)
    {
        $this->_sharedDbname = $name;
        return $this;
    }
}
