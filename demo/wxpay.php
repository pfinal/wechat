<?php

require __DIR__ . '/../vendor/autoload.php';

$config = array(
    'appId' => 'wx00e5904efes9',
    'appSecret' => '419ffc73eb1fga846c6b04b',
    'token' => '54b780cbe9047',
    'encodingAesKey' => 'cz7NR8g4vk5yMydpG3g8amyFGbjavCuR33',
    'mchId' => '1220633201',
    'apiSecret' => 'b4944959c6eaed319a86e3a10d',
);

// 初始化SDK
\PFinal\Wechat\Kernel::init($config);

// 获取OpenID
$openid = \PFinal\Wechat\Service\OAuthService::getOpenid();
//$openid='oU3OCt5O46PumN7IE87WcoYZY9r0';

// JS签名
$signPackage = \PFinal\Wechat\Service\JsService::getSignPackage();

// 支付成功通知url
/* 收到支付成功通知时,请使用下面的方法,验证订单号和支付金额
    $info = \PFinal\Wechat\Service\PayService::notify();
    $info数组,内容如下
    [
         mch_id          //微信支付分配的商户号
         appid           //微信分配的公众账号ID
         openid          //用户在商户appid下的唯一标识
         transaction_id  //微信支付订单号
         out_trade_no    //商户订单号
         total_fee       //订单总金额单位默认为分，已转为元
         is_subscribe    //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
         attach          //商家数据包，原样返回
         time_end        //支付完成时间
    ]
*/
$notifyUrl = 'http://xxx.com/index.php/wxpay/notify';

// 订单信息
$order = array(
    'totalFee' => 0.01,    //支付金额
    'tradeNo' => uniqid(), //订单号
    'name' => '测试订单',   //订单名称
);

// 业务签名
$bizPackage = \PFinal\Wechat\Service\PayService::createJsBizPackage(
    $openid,
    $order['totalFee'], $order['tradeNo'], $order['name'],
    $notifyUrl,
    $signPackage['timestamp']
);

?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>微信支付demo</title>
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<br>
<br>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <a href="javascript:;" class="js-btn-wxpay btn btn-success btn-block">微信支付</a>
        </div>
    </div>
</div>

<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function () {
        wx.config({
            debug: true,//调试模式
            appId: '<?php echo $signPackage['appId'] ?>',
            timestamp: <?php echo $signPackage['timestamp'] ?>,
            nonceStr: '<?php echo $signPackage['nonceStr'] ?>',
            signature: '<?php echo $signPackage['signature'] ?>',
            jsApiList: [
                'chooseWXPay'
            ]
        });
        $(".js-btn-wxpay").click(function () {
            if (typeof WeixinJSBridge == "undefined") {
                alert("请在微信中打开");
                return;
            }
            var success = false;
            wx.chooseWXPay({
                timestamp: <?php echo $bizPackage['timeStamp'] ?>,
                nonceStr: '<?php echo $bizPackage['nonceStr'] ?>',
                package: '<?php echo $bizPackage['package'] ?>',
                signType: '<?php echo $bizPackage['signType'] ?>',
                paySign: '<?php echo $bizPackage['paySign'] ?>',
                success: function (res) {
                    success = true;
                },
                complete: function () {
                    if (success) {
                        alert('支付成功');
                    } else {
                        alert('支付失败');
                    }
                }
            });
        });
    });

</script>
</body>
</html>
