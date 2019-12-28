<?php
/**
 *   Copyright (c) [2019] [Yanlongli <jobs@yanlongli.com>]
 *   [Wechat] is licensed under the Mulan PSL v1.
 *   You can use this software according to the terms and conditions of the Mulan PSL v1.
 *   You may obtain a copy of Mulan PSL v1 at:
 *       http://license.coscl.org.cn/MulanPSL
 *   THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT, MERCHANTABILITY OR FIT FOR A PARTICULAR
 *   PURPOSE.
 *   See the Mulan PSL v1 for more details.
 *
 *   Author: Yanlongli <jobs@yanlongli.com>
 *   Date:   2019/11/20
 *   IDE:    PhpStorm
 *   Desc:   服务器监听事件演示demo
 */
declare(strict_types=1);

use yanlongli\wechat\WechatException;
use yanlongli\wechat\support\Config;
use yanlongli\wechat\officialAccount\OfficialAccount;
use yanlongli\wechat\officialAccount\HandleEventService;
use yanlongli\wechat\messaging\receive\event\Subscribe;
use yanlongli\wechat\messaging\message\Text;
use yanlongli\wechat\messaging\receive\general\Text as receiveText;
use yanlongli\wechat\messaging\message\Image;
use yanlongli\wechat\messaging\receive\general\Image as receiveImage;
use yanlongli\wechat\messaging\receive\general\Location as receiveLocation;
use yanlongli\wechat\messaging\receive\event\Location as receiveEventLocation;
use yanlongli\wechat\service\CallMessageService;
use yanlongli\wechat\messaging\contract\ReplyMessage;
use yanlongli\wechat\messaging\receive\ReceiveMessage;
use yanlongli\wechat\messaging\receive\event\QRScene;
use yanlongli\wechat\support\Request;
use yanlongli\wechat\messaging\receive\event\Click;
use yanlongli\wechat\messaging\receive\event\LocationSelect;
use yanlongli\wechat\messaging\receive\event\PicPhotoOrAlbum;
use yanlongli\wechat\messaging\receive\event\PicWeixin;
use yanlongli\wechat\messaging\receive\event\PicSysPhoto;
use yanlongli\wechat\messaging\receive\event\ScanCodePush;
use yanlongli\wechat\messaging\receive\event\ScanCodeWaitMsg;
use yanlongli\wechat\messaging\receive\event\View;
use yanlongli\wechat\messaging\receive\event\ViewMiniprogram;


include '../vendor/autoload.php';

Config::loadConfigFile(__DIR__ . '/config.php');

$officialAccount = new OfficialAccount(Config::get('config'));

$service = new HandleEventService($officialAccount);

// 订阅事件
$service->register(function (Subscribe $subscribe) {
    $subscribe->sendMessage(new Text("感谢您的关注"));
});
// 扫码关注事件
$service->register(function (QRScene $QRScene) {
    return new Text($QRScene->EventKey);
});

//文字消息
$service->register(function (receiveText $text) use ($officialAccount): ReplyMessage {
    if ($text->Content === '1') {
        return new Text($text->FromUserName);
    }

    return new Text($text->Content);
});
//图片消息
$service->register(function (receiveImage $image) use ($officialAccount): ReplyMessage {
    CallMessageService::send($officialAccount, $image->FromUserName, new Text($image->MediaId));
    return new Image($image->MediaId);
});
//位置消息
$service->register(function (receiveLocation $location) {
    return new Text("收到普通的地址坐标：$location->Location_X,$location->Location_Y,$location->Scale,$location->MsgId");
});
// 坐标事件
$service->register(function (receiveEventLocation $location) {
    return new Text("收到事件的地址坐标：5s一次 " . date(DATE_ATOM));
});

$service->register(function (ReceiveMessage $receiveMessage): ReplyMessage {
    return new Text("收到一条未处理消息:");
});
//点击菜单事件
$service->register(function (Click $click) {
    return new Text("您点击了菜单," . $click->EventKey);
});
$service->register(function (ScanCodeWaitMsg $click) use ($officialAccount) {
    return new Text("扫码等待消息" . json_encode(Request::param()));
});

// 下面这些不能回复消息，回了也没反应，可以主动发送消息
//打开地图选择器事件
$service->register(function (LocationSelect $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("您打开了地图选择器"));
});
//点击二选一图片菜单事件
$service->register(function (PicPhotoOrAlbum $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("您点击自由选择图片"));
});
//点击相册选择图片事件
$service->register(function (PicWeixin $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("您点击微信图片选择器"));
});
//点击直接拍照事件
$service->register(function (PicSysPhoto $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("您只能直接拍照"));
});
//扫码推送事件，会自动展示code内容或打开网页等
$service->register(function (ScanCodePush $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("扫码推送"));
});
//打开菜单的URL链接
$service->register(function (View $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("打开URL"));
});
//打开菜单关联的小程序
$service->register(function (ViewMiniprogram $click) use ($officialAccount) {
    CallMessageService::send($officialAccount, $click->FromUserName, new Text("打开小程序"));
});
//处理动作
try {
    $service->handle();
} catch (WechatException $e) {
    //处理异常，回复 success让微信不报错
    echo 'success';
}
