# [WeChat SDK](http://pfinal.cn)

一个简单易用的微信公众平台SDK。

>支持微信公众平台 https://mp.weixin.qq.com

微信开发者交流 QQ 群：`16455997`

## 特点

 - 基于微信官方SDK简单封装，避免过度封装带来的额外学习成本以及影响扩展性;
 - 核心API类单文件，简单易用，隐藏开发者不需要关注的细节;
 - 抽象了消息事件，让你的控制器代码更优雅，扩展和维护更容易;
 - 详细的调试日志，让开发更轻松;
 - 支持PHP 5.3+、7.x版本;
 - 符合 [PSR](https://github.com/php-fig/fig-standards) 标准，非常方便与各主流PHP框架集成;

## 视频教程

> [http://www.pfinal.cn/subject/wechat](http://www.pfinal.cn/subject/wechat)
>
> [在线文档](doc/index.md)

## 安装

环境要求：PHP >= 5.3

* 使用 [composer](https://getcomposer.org/)

```shell
composer require pfinal/wechat
```

* 如果你的项目没有使用composer，请下载[完整包](https://github.com/pfinal/wechat/raw/publish/pfinal-wechat-full.zip)


## 示例

查看demo中的示例  demo/server.php 是服务端

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use PFinal\Wechat\Kernel;
use PFinal\Wechat\Message\Receive;
use PFinal\Wechat\Message;
use PFinal\Wechat\WechatEvent;
use PFinal\Wechat\Support\Log;

//配置项 
$config = array(
    'appId' => 'xxxxxxxxx',
    'appSecret' => 'xxxxxxxxxxxxxxxxxxxx',
    'token' => 'xxxxxx',
    'encodingAesKey' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    //更多详细配置请参考 demo/config-local.example
);

//初始化
Kernel::init($config);

//消息处理
Kernel::register(Receive::TYPE_TEXT, function (WechatEvent $event) {
    $message = $event->getMessage();
    $event->setResponse('你好');
    $event->stopPropagation();
});

//关注事件
Kernel::register(Receive::TYPE_EVENT_SUBSCRIBE, function (WechatEvent $event) {
    $event->setResponse('你关注或是不关注，我都在这里，不悲不喜~~');
    $event->stopPropagation();
});

//处理微信服务器的请求
$response = Kernel::handle();

echo $response;

```


## http代理

```php
<?php
putenv('WECHAT_PROXY', '127.0.0.1');
putenv('WECHAT_PROXYPORT', '8080');

# proxy server
# https://github.com/pfinal/proxy
# curl -o proxy https://github.com/pfinal/proxy/releases/download/v1.0.0/proxy-linux
# chmod +x proxy
# ./proxy --port :8080
```

## 中控服务器

```
putenv('WECHAT_ACCESS_TOKEN_SERVER', 'http://192.168.1.33/wechat-access-token');

中控服务器接收参数:
$_POST['appId']
$_POST['useCache']  '1'表示可用缓存  '0' 表示不用缓存
响应内容 {"status": true, "access_token": "xxx"}
```



## 效果截图

![](doc/demo1.png)
![](doc/demo2.png)
![](doc/demo3.png)
![](doc/demo4.png)


