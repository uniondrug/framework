<?php
/**
 * Uniondrug Api Framework
 * 容器
 * @author Unindrug
 */
namespace Uniondrug\Framework;

/**
 * Class Mysql
 * @package Uniondrug\Framework
 */
class Mysql extends \Phalcon\Db\Adapter\Pdo\Mysql
{
    private $_sharedName;

    /**
     * 读取共享名称
     * @return string
     */
    public function getSharedName()
    {
        return $this->_sharedName;
    }

    /**
     * 读取完整SQL语句
     * @return string
     */
    public function getListenerSQLStatment()
    {
        // 1. 原始SQL
        $sql = $this->getSQLStatement();
        // 2. 读取占位
        if (null === ($vars = $this->getSqlVariables())) {
            return $sql;
        }
        // 3. 替换占位符
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
     * 设置共享名称
     * @param string $name
     * @return $this
     */
    public function setSharedName(string $name)
    {
        $this->_sharedName = $name;
        return $this;
    }
}
