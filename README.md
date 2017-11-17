# 目录结构

* 目录结构
* 类与方法
* 微服务用法
    * 微服务客户端
    * 如何使用服务端
* 单元测试


### 目录结构

> 框架级`Framework`目录结构

```text
/ vendor/uniondrug/framework/src
├── Controllers
│   ├── ServiceClientController.php         // Service客户端
│   ├── ServiceServerController.php         // Service服务端
│   └── TestsController.php                 // 单元测试调度
├── Interfaces
│   ├── RelateChildInterface.php
│   ├── RelateFetchInterface.php
│   ├── RelateWriteInterface.php
│   ├── SingleChildInterface.php
│   ├── SingleFetchInterface.php
│   └── SingleWriteInterface.php
├── Providers
│   ├── ConfigProvider.php
│   ├── DatabaseProvider.php
│   ├── LoggerProvider.php
│   └── RouterProvider.php
├── Services
│   ├── FrameworkService.php
│   ├── RelateChildService.php
│   ├── RelateService.php
│   ├── SingleChildService.php
│   └── SingleService.php
├── Application.php
└── Container.php
```


### 类与方法

> Service继承, 基于接口`RelateChildInterface`、`RelateFetchInterface`、`RelateWriteInterface`、`SingleChildInterface`、`SingleFetchInterface`、`SingleWriteInterface`预定义的通用Service。

```php
<?php
/**
 * 示例Service
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-17
 */
namespace App\Services;
use Pails\Services\RelateChildService;

/**
 * 示例Service应用场景
 * 1. 有隶属关系
 * 2. 有上/下级
 * @package App\Services
 */
class ExampleService extends RelateChildService
{
}
```

1. **Phalcon\Di\Injectable{}**
    1. `__get()`
    1. `getDI()`
    1. `getEventsManager()`
    1. `setDI()`
    1. `setEventsManager()`
    1. **Pails\Services\FrameworkService{}**
        1. `fetchAll()` - 按条件读取全部
        1. `fetchCount()` - 按条件读取数量
        1. `fetchOne()` - 按条件读取一条
        1. `fetchPaging()` - 按条件读取分页
        1. `getAutoIncrementColumn()` - 读取模型的流水号ID字段名称
        1. `getError()` - 读取最的的错误
        1. `getErrorMessage()` - 读取最近的错误原因
        1. `getModel()` - 读取Service对应的Model
        1. `hasError()` - 检查是否有错误
        1. `setError()` - 设置最近的错误
        1. `setModel()` - 设置Service对应的Model, 若不指定则自动识别
        1. **Pails\Services\SingleService{}**
            1. `delete()` - 批量删除
            1. `deleteById()` - 按ID删除
            1. `fetchAllByColumn()` - 按指定字段读取全部
            1. `fetchOneByColumn()` - 按指定字段读取一条
            1. `fetchOneById()` - 按ID读取一条
            1. `insert()` - 添加新记录
            1. `update()` - 修改记录
            1. `updateById()` - 按记录ID修改
            1. `Pails\Services\SingleChildService{}`
                1. `fetchChild()` - 读取一条下级记录
                1. `fetchChildren()` - 读取下级记录列表
                1. `fetchTree()` - 读取树形结构
                1. `hasChild()` - 检查是否有下级记录
        1. **Pails\Services\RelateService{}**
            1. `delete()` - 批量删除
            1. `deleteById()` - 按ID删除
            1. `fetchAllByColumn()` - 按指定字段读取全部
            1. `fetchOneByColumn()` - 按指定字段读取一条
            1. `fetchOneById()` - 按ID读取一条
            1. `insert()` - 添加新记录
            1. `update()` - 修改记录
            1. `updateById()` - 按记录ID修改
            1. `Pails\Services\RelateChildService{}`
                1. `fetchChild()` - 读取一条下级记录
                1. `fetchChildren()` - 读取下级记录列表
                1. `fetchTree()` - 读取树形结构
                1. `hasChild()` - 检查是否有下级记录


### 微服务用法

