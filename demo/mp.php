<?php

require __DIR__ . '/../vendor/autoload.php';

use PFinal\Wechat\Kernel;

//请复制 config-local.example 为 config-local.php
$config = require __DIR__ . '/config-local.php';

Kernel::init($config);

if (file_exists(__DIR__ . '/test-data.php')) {
    require __DIR__ . '/test-data.php'; // 一些测试用的数据
}

$api = Kernel::getApi();

if (isset($_GET['test'])) {

    //var_dump(\PFinal\Wechat\Service\MessageService::send($openid2, new \PFinal\Wechat\Message\News($news)));

    var_dump(\PFinal\Wechat\Service\MaterialService::uploadFileTemporary('./test.jpg', 'image'));
    //var_dump(\PFinal\Wechat\Service\MaterialService::uploadFile('./test.jpg','image'));
    //var_dump(\PFinal\Wechat\Service\MaterialService::uploadFile('./mz.png','image'));

    //array(2) { ["media_id"]=> string(43) "ZbXWSFYu_gmXjP9EW5ydPCSCPlhJC99YeqUtL0rRYXQ" ["url"]=> string(133) "https://mmbiz.qlogo.cn/mmbiz/D7sHwECXBUtWxg2eVOmIsqWOERic2dfBWpjONuWl2prbAI07ZRiasOw9Dt3ibILvs9uIJib7kIiaVbowRWrpnJH1Zew/0?wx_fmt=png" }

    //$arr = \PFinal\Wechat\Service\MaterialService::uploadNews('迈征', 'ZbXWSFYu_gmXjP9EW5ydPCSCPlhJC99YeqUtL0rRYXQ', 'ethan', '描述内容', 'https://mmbiz.qlogo.cn/mmbiz/D7sHwECXBUtWxg2eVOmIsqWOERic2dfBWpjONuWl2prbAI07ZRiasOw9Dt3ibILvs9uIJib7kIiaVbowRWrpnJH1Zew/0?wx_fmt=png', '内容内容');
    //$arr = \PFinal\Wechat\Service\MaterialService::uploadFile('./voice.mp3','voice');
    //$arr = \PFinal\Wechat\Service\MaterialService::uploadFile('./video.mp4', 'video', '测试', '描述');
    //var_dump($arr);

    //var_dump(\PFinal\Wechat\Service\MessageService::send($openid2, new \PFinal\Wechat\Message\Video($videoMediaId, $imageMediaId, 'aa', 'bb')));


    exit;
}

if (isset($_GET['token'])) {
    echo $api->getAccessToken(false); //不使用缓存，直接从服务器获取token
    exit;
}

if (isset($_GET['openid'])) {
    echo \PFinal\Wechat\Service\OAuthService::getOpenid();
    exit;

}

if (isset($_GET['user'])) {
    $user = \PFinal\Wechat\Service\OAuthService::getUser();
    var_dump($user);
    exit;
}

if (isset($_GET['send'])) {
    $result = \PFinal\Wechat\Service\MessageService::send($openid2, new \PFinal\Wechat\Message\Text('test'));
    var_dump($result);
    exit;
}


if (isset($_GET['preview'])) {
    //$result = \PFinal\Wechat\Service\MessageService::previewWithWxname('rainphp',new \PFinal\Wechat\Message\Text('test'));
    //$result = \PFinal\Wechat\Service\MessageService::previewWithWxname('rainphp',new \PFinal\Wechat\Message\Image($imageMediaId));
    //$result = \PFinal\Wechat\Service\MessageService::previewWithWxname('rainphp',new \PFinal\Wechat\Message\Voice($voiceMediaId));
    //$result = \PFinal\Wechat\Service\MessageService::previewWithWxname('rainphp',new \PFinal\Wechat\Message\MpNews($newsMediaId));

    $result = \PFinal\Wechat\Service\MaterialService::uploadFile('./voice.mp3', 'music');

    var_dump($result);
    exit;
}

if (isset($_GET['redpack'])) {

    //o0N6bt-9GESHZIqAaxvlZvjW5Rwk
    //o0N6bt40edruwOR2OoOZDpj7slPY
    $result = \PFinal\Wechat\Service\RedPackService::send('o0N6bt2Ikobz0SY35sd76rubkOvc', 1.01);
    var_dump($result);
}


if (isset($_GET['qr'])) {

    //临时
    $result = \PFinal\Wechat\Service\QrcodeService::temporary(1000001, 60 * 60 * 24);

    $url = \PFinal\Wechat\Service\QrcodeService::url($result['ticket']);
    echo "<img src='$url'>";


    //永久 数字key
    $result = \PFinal\Wechat\Service\QrcodeService::forever(33);
    $url = \PFinal\Wechat\Service\QrcodeService::url($result['ticket']);
    echo "<img src='$url'>";


    //永久 字符串key
    $result = \PFinal\Wechat\Service\QrcodeService::forever("haha");
    $url = \PFinal\Wechat\Service\QrcodeService::url($result['ticket']);
    echo "<img src='$url'>";

}


if (isset($_GET['template'])) {
    //$data = ['first' => '234', 'keyword1' => 'aaa', 'keyword2' => 'adsfsaf', 'keyword3', 'remark' => 'aaaa'];
    $data = ['first' => ['value' => '234', 'color' => '#FC5C48'], 'keyword1' => 'aaa', 'keyword2' => 'adsfsaf', 'keyword3', 'remark' => 'aaaa'];
    $result = \PFinal\Wechat\Service\MessageService::template($openid3, 'VSmzI2hL3MuHyd1eqw9eIxNsLmY4N8CFTgX4tiCDyYI', $data);
    var_dump($result);
}

if (isset($_GET['menu'])) {
    
    //var_dump(\PFinal\Wechat\Service\MenuService::create($menus));
    var_dump(\PFinal\Wechat\Service\MenuService::all());

}

//var_dump(\PFinal\Wechat\Service\MessageService::send($openid, new \PFinal\Wechat\Message\Text('pFinal.cn')));

//$result = \PFinal\Wechat\Service\MaterialService::uploadFileTemporary('./test.jpg', 'image');
//var_dump($result);
//var_dump(\PFinal\Wechat\Service\MessageService::send($openid, new \PFinal\Wechat\Message\Image($result['media_id'])));

//var_dump(\PFinal\Wechat\Service\MessageService::send($openid, new \PFinal\Wechat\Message\News($news)));


//var_dump(\PFinal\Wechat\Service\UserService::get($openid));
//var_dump(\PFinal\Wechat\Service\UserService::batchGet([$openid, $openid2]));
//var_dump(\PFinal\Wechat\Service\QrcodeService::temporary(11));
//var_dump(\PFinal\Wechat\Service\QrcodeService::forever(99));
//var_dump(\PFinal\Wechat\Service\QrcodeService::url('gQE08ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL05rTzVMYURtYS1zU3pudDVoMi1DAAIEs7oLVwMEAAAAAA=='));
