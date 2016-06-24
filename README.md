# [WeChat SDK](http://pfinal.cn)

一个简单易用的微信公众平台SDK。

>支持微信公众平台 https://mp.weixin.qq.com
>
>支持微信开放平台 https://open.weixin.qq.com

微信开发者交流 QQ 群：`16455997`

## 特点

 - 基于微信官方SDK简单封装，避免过度封装带来的额外学习成本以及影响扩展性;
 - 核心API类单文件，简单易用，隐藏开发者不需要关注的细节;
 - 抽象了消息事件，让你的控制器代码更优雅，扩展和维护更容易;
 - 详细的调式日志，让开发更轻松;
 - 支持PHP 5.4、5.5、5.6、7版本;
 - 符合 [PSR](https://github.com/php-fig/fig-standards) 标准，非常方便与各主流PHP框架集成;

## 安装

环境要求：PHP >= 5.4

1. 使用 [composer](https://getcomposer.org/)

  ```shell
  composer require pfinal/wechat
  ```

如果你的项目没有使用composer，可以直接下载[完整包](https://github.com/pfinal/wechat/blob/master/down/pfinal-wechat-1.0.zip)


2. 查看demo中的示例。 demo/server.php 是服务端

   ```php
    <?php
    use PFinal\Wechat\Kernel;
    use PFinal\Wechat\Message\Receive;
    use PFinal\Wechat\Message;
    use PFinal\Wechat\WechatEvent;

    //初始化
    $config = require __DIR__ . '/config-local.php';
    Kernel::init($config);

    //消息处理
    Kernel::register(Receive::TYPE_TEXT, function (WechatEvent $event) {
        $message = $event->getMessage();
        $event->setResponse('你好');
    }

    //关注事件
    Kernel::register(Receive::TYPE_EVENT_SUBSCRIBE, function (WechatEvent $event) {
        $event->setResponse('你关注或是不关注，我都在这里，不悲不喜~~');
        $event->stopPropagation();
    });

   ```

![](doc/demo1.png)
![](doc/demo2.png)
![](doc/demo3.png)
![](doc/demo4.png)