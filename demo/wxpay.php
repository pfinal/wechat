<?php


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
$notifyUrl = 'http://xxx.com/index.php/wxpay/notify';

// 订单信息
$order = array(
    'totalFee' => 0.01,
    'tradeNo' => uniqid(),
    'name' => '测试订单',
);

// 业务签名
$bizPackage = \PFinal\Wechat\Service\PayService::createJsBizPackage(
    $openid, $order['totalFee'], $order['tradeNo'], $order['name'], $notifyUrl, $signPackage['timestamp']);

// 加载视图
return \View::render('wxpay.twig', array(
    'openid' => $openid,
    'signPackage' => $signPackage,
    'bizPackage' => $bizPackage,
));
