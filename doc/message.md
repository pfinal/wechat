# 消息与事件

当用户与微信公众号互动的时候，微信平台会将消息推送到开发者填写的URL，我们就能处理这些消息。用户在公众号中的互动包括：关注/取消关注、发送消息、点击菜单、扫描带参数二唯码、上报位置等。


## 接收消息和事件

为了方便处理，将 [消息](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140453&token=&lang=zh_CN) 和 [事件](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140454&token=&lang=zh_CN) 统一封装为 `PFinal\Wechat\Message\Receive` 类，无论用户是发送消息(文本、图片、语音、视频等)还是触发事件(关注、点击菜单等)，均使用相同的方法处理。

在SDK中，使用 Kernel::register(消息类型, 回调) 来设置消息处理函数：

```PHP
<?php
require __DIR__ . '/../vendor/autoload.php';

use PFinal\Wechat\Kernel;
use PFinal\Wechat\Support\Log;
use PFinal\Wechat\WechatEvent;

//配置项
$config = [
    'appId' => 'your-app-id',
    'appSecret' => 'your-secret',
    'token' => 'your-token',
    'encodingAesKey' => 'your-aes-key',
];

//初始化
Kernel::init($config);

//接收文本消息
Kernel::register(Receive::TYPE_TEXT, function (WechatEvent $event) {
    $message = $event->getMessage(); 
    $event->setResponse('收到文本信息:' . $message->Content);
});

//接收用户关注事件
Kernel::register(Receive::TYPE_EVENT_SUBSCRIBE, function (WechatEvent $event) {
    $event->setResponse('你关注或是不关注，我都在这里，不悲不喜~~');
});

//处理微信服务器的请求
$response = Kernel::handle();

//将响应输出
echo $response;

```

## 类型

Kernel::register()第一个参数为消息类型(MsgType)，SDK做了简单封装，对应`PFinal\Wechat\Message\Receive::TYPE_XXX`常量

```
TYPE_TEXT                 //文本消息
TYPE_VOICE                //语音
TYPE_IMAGE                //图片
TYPE_LOCATION             //位置消息(聊天窗口发送的位置)
TYPE_LINK                 //链接
TYPE_VIDEO                //视频
TYPE_SHORT_VIDEO          //小视频

TYPE_EVENT_SUBSCRIBE      //关注事件
TYPE_EVENT_UNSUBSCRIBE    //关注事件
TYPE_EVENT_CLICK          //点击菜单
TYPE_EVENT_VIEW           //菜单跳转
TYPE_EVENT_SCAN_CODE_WAIT_MSG   //扫码推事件
TYPE_EVENT_PIC_SYSPHOTO         //弹出系统拍照发图的事件推送
TYPE_EVENT_PIC_PHOTO_OR_ALBUM   //弹出拍照或者相册发图的事件推送
TYPE_EVENT_PIC_WEIXIN           //弹出微信相册发图器的事件推送
TYPE_EVENT_LOCATION_SELECT      //弹出地理位置选择器的事件推送
TYPE_EVENT_SCAN                 //扫描二唯码
TYPE_EVENT_LOCATION             //上报地理位置事件
TYPE_EVENT_MASS_SEND_JOB_FINISH //群发消息结果
TYPE_EVENT_CARD_PASS_CHECK      //卡券通过审核
TYPE_EVENT_CARD_NOT_PASS_CHECK  //卡券审核不通过
TYPE_EVENT_USER_GET_CARD        //用户在领取卡券
TYPE_EVENT_USER_DEL_CARD        //用户在删除卡券
TYPE_EVENT_USER_VIEW_CARD       //用户在进入会员卡
```
如果有新的消息或事件类型，未包含在上述常量中，也可以直接使用字符串，由微信推送XML中的MsgType和Event两个值组合而成。例如文本消息为`text`，关注事件为`event.subscribe`  更多详情参考微信官方文档：[接收消息](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140453&token=&lang=zh_CN) 、[接收事件](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140454&token=&lang=zh_CN)


## 属性

Kernel::register()第二个参数为处理消息的闭包([closure](http://php.net/manual/en/class.closure.php)) 该闭包接收一个参数 $event，通过此对象，可以很方便的获取消息内容和回复消息 `$message = $event->getMessage()` 获取到的`$message`对象，是 `PFinal\Wechat\Message\Receive`类的实例，相关属性如下(属性大小写，与微信官方XML一样)

```
//基本属性(所有消息都有)
$message->ToUserName    接收方帐号（该公众号 ID）
$message->FromUserName  发送方帐号（OpenID, 代表用户的唯一标识）
$message->CreateTime    消息创建时间（时间戳）
$message->MsgId         消息 ID（64位整型）

//文本消息
$message->MsgType  text
$message->Content  文本消息内容

//图片
$message->MsgType  image
$message->PicUrl   图片链接

//语音
$message->MsgType        voice
$message->MediaId        语音消息媒体id，可以调用多媒体文件下载接口拉取数据
$message->Format         语音格式，如 amr，speex 等
$message->Recongnition   开通语音识别后才有

//视频
$message->MsgType       video
$message->MediaId       视频消息媒体id，可以调用多媒体文件下载接口拉取数据
$message->ThumbMediaId  视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据

//小视频：
$message->MsgType     shortvideo
$message->MediaId     视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
$message->ThumbMediaId    视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据

//事件
$message->MsgType     event
$message->Event       事件类型 （如：subscribe(订阅)、unsubscribe(取消订阅) CLICK 等）

//扫描带参数二维码事件
$message->EventKey    事件KEY值，比如：qrscene_100，qrscene_为前缀，后面为二维码的参数值
$message->Ticket      二维码的 ticket，可用来换取二维码图片

//上报地理位置事件
$message->Latitude    23.137466   地理位置纬度
$message->Longitude   113.352425  地理位置经度
$message->Precision   119.385040  地理位置精度

//自定义菜单事件
$message->EventKey    事件KEY值，与自定义菜单接口中KEY值对应

//地理位置：
$message->MsgType     location
$message->Location_X  地理位置纬度
$message->Location_Y  地理位置经度
$message->Scale       地图缩放大小
$message->Label       地理位置信息

//链接：
$message->MsgType      link
$message->Title        消息标题
$message->Description  消息描述
$message->Url          消息链接

```

## 响应回复

收到消息后，需要在5秒内立即做出回应，通过 `$event->setResponse('回复内容')` 即可将文本信息回复给用户

* 回复图文消息

```PHP
$news = new \PFinal\Wechat\Message\News('pFinal社区', '致力于提供优质PHP中文学习资源', 'http://pfinal.cn/', 'http://pfinal.cn/pfinal.png');
$event->setResponse($news);
```

* 回复图片

```
$image = new \PFinal\Wechat\Message\Image($imageMediaId);//$imageMediaId需要上传素材得到
$event->setResponse($image);
```

实现 ReplyMessage接口的类，均可用于消息被动回复，在命名空间`PFinal\Wechat\Message`下，例如:Image、Music、News、Text、Video、Voice。框架对常用类型进行了封装，让你不必去拼接XML。具体如何实例化这些类，请查看源代码，根据构造方法要求，传入相应参数即可。

> News做了优化，可以很方便的支持单个或多个图文消息












