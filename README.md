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
 - 对PHP版本要求低，需要5.4版本;
 - 符合 [PSR](https://github.com/php-fig/fig-standards) 标准，支持与各主流PHP框架集成;

## 安装

环境要求：PHP >= 5.4

1. 使用 [composer](https://getcomposer.org/)

  ```shell
  composer require "pfinal/wechat:~1.0"
  ```

![](doc/demo1.png)
![](doc/demo2.png)
![](doc/demo3.png)