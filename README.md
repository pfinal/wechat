微信SDK
===
>希望打造一个尽量全面的微信平台管理工具，包含公众号、小程序、微信商户、移动app、网站app等。


当前为方便开发，第一个大版本中所有子扩展均集成在一起，并且主维护 公众号 功能， 后期将会单例拆分各个子扩展。


### 更新日志

    2019年12月4日
    修复部分错误
    增强类属性的类型定义
    更新PHP依赖版本PHP7.4及以上版本

    2019年11月22日
    处理服务注册及消息监听处理
    是的，就要完成基本的公众号工作了。现在需要处理一些细节，比如accessToken的获取和储存逻辑，log打印等

    2019年11月20日
    调整事件基础接口改为抽象类

    2019年11月14日
    补曾移动app类型
    增加Config类用于参数处理
    增加 消息事件 模型
    大量工作内容
    
    2019年11月13日
    改进服务调用方式改为静态方法，优化性能
    
    2019年11月7日
    初始化项目
    编排子扩展结构，方便后期拆分

### 说明 
本项目采用中国 木兰开源许可协议 

大部分代码及处理方式逻辑来自 pfinal/wechat 更多信息请访问 [pfinal/wechat](https://github.com/pfinal/wechat)


### 文档目录
* [微信APP定义](/doc/AppType.md)
* [命名规则](/doc/NamingRules.md)