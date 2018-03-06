# 目录结构

* 路由
* 目录结构


### 路由

支持默认路由和注解路由，支持自定义路由。

开启注解路由：
`config/app.php` 增加配置项 `userAnnotationRouter` 并且设置为 `true` 即可。注解路由打开后，默认路由失效。

自定义路由在：
`config/routes.php` 中定义。

### 目录结构

> 框架级`Framework`目录结构

```text
/vendor/uniondrug/framework/src
├── Events
│   └── Listeners
│       └── DatabaseListener.php
├── Providers
│   ├── ConfigProvider.php
│   ├── DatabaseProvider.php
│   ├── LoggerProvider.php
│   └── RouterProvider.php
├── Application.php
├── Container.php
├── Logger.php
├── Request.php
└── Router.php
```
