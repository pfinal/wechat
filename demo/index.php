<?php
require __DIR__ . '/../vendor/autoload.php';

use PFinal\Wechat\Kernel;
use PFinal\Wechat\Service\JsService;

//请复制 config-local.example 为 config-local.php
$config = require __DIR__ . '/config-local.php';

Kernel::init($config);

$signPackage = JsService::getSignPackage();

?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <title>微信平台SDK</title>
</head>
<body>

<h1>微信平台SDK</h1>

<ul>
    <li><a href="mp.php?token">刷新accessToken</a></li>
    <li><a href="mp.php?openid">获取openid</a></li>
    <li><a href="mp.php?user">获取用户信息</a></li>
    <li><a href="mp.php?send">发客服消息</a></li>
    <li><a href="mp.php?preview">群发预览</a></li>
    <li><a href="mp.php?redpack">发红包</a></li>
    <li><a href="mp.php?qr">二唯码</a></li>
    <li><a href="mp.php?template">模板消息</a></li>
    <li><a href="mp.php?menu">自定义菜单</a></li>
</ul>


<a href="javascript:;" class="chooseImage">JS_SDK选择图片</a>

<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>


<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        //debug: true,
        appId: '<?php echo $signPackage["appId"];?>',
        timestamp: <?php echo $signPackage["timestamp"];?>,
        nonceStr: '<?php echo $signPackage["nonceStr"];?>',
        signature: '<?php echo $signPackage["signature"];?>',
        jsApiList: [
            // 所有要调用的 API 都要加到这个列表中
            'onMenuShareAppMessage',
            'onMenuShareTimeline',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            //拍照或相册
            'chooseImage',
            //上传图片
            'uploadImage'
        ]
    });
    wx.ready(function () {
        // 在这里调用 API
    });

    $(function () {
        $(".chooseImage").click(function () {
            wx.chooseImage({
                count: 1, // 默认9
                sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                success: function (res) {
                    var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片

                    alert(localIds);
                }
            });
        });
    });

</script>

</body>
</html>