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
 *   Desc:   _
 */
declare(strict_types=1);

use yanlongli\wechat\support\Config;
use yanlongli\wechat\service\OAuthService;
use yanlongli\wechat\officialAccount\OfficialAccount;
use yanlongli\wechat\WechatException;

include '../vendor/autoload.php';
Config::loadConfigFile(__DIR__ . '/config.php');

$officialAccount = new OfficialAccount(Config::get('config.'));

try {
    session_start();

    $user = $_SESSION['wechat'] ??= OAuthService::getOpenid($officialAccount);
    var_dump($_SESSION);

    var_dump($user);
//    CallMessageService::send($officialAccount, $user, new Text("hello"));
//    TemplateMessageService::send($officialAccount, $user, new \yanlongli\wechat\messaging\message\Template('ofPaNraz-JVnIHLWhNyACUSDS1ulQnxvLi1GKMVaWbU', [
//        'user' => '管理员'
//    ]));


    //获取菜单
    //    try {
    //        $result = MenuService::all($officialAccount);
    //        var_dump($result);
    //    } catch (WechatException $wechatException) {
    //        if ($wechatException->getCode() === 46003) {
    //            echo '没有设定菜单';
    //        }
    //    }

    // 设定菜单
//    try {
//        $result = MenuService::create($officialAccount, [
//            MenuService::optionSubButton('菜单1', [
//                MenuService::optionClick("点击事件", 'key_01'),
//                MenuService::optionLocation('发送位置', 'key_02'),
////                MenuService::optionNews('发送图文消息', 'Rh14NtJuVzYrugHvQEzN4QDiOKQsVXTe1ZJ3wbzhYc2dFGnD4c94VBxHTBnYmSUl'),//暂时没有素材ID
////                MenuService::optionMedia('发送媒体消息', 'HLsinRqaJRijwHGRAaXsnePIN-VigOxhuUXjky9KBtk9uKL6gtnOnPDVFo94OANX'),//同上
//            ]),
//            MenuService::optionSubButton('图片类2', [
//                MenuService::optionPicSysPhoto('拍照', 'key_05'),
//                MenuService::optionPicPhotoOrAlbum('拍照或相册', 'key_06'),
//                MenuService::optionPicWeixin('微信相册', 'key_07')
//            ]),
//            MenuService::optionSubButton('其他3', [
//                MenuService::optionScancodePush('扫码推送', 'key_08'),
//                MenuService::optionScancodeWaitmsg('扫码提示', 'key_09'),
////                MenuService::optionMiniprogram('小程序', 'wx1e36f098b140fa11', '/', 'https://wechat.yanlongli.com'),
//                MenuService::optionView('网址', 'https://wechat.yanlongli.com')
//            ])
//        ]);
//        var_dump($result);
//    } catch (WechatException $wechatException) {
//        echo '遇到错误<br/>';
//        var_dump($wechatException);
//    }


} catch (WechatException $e) {
    throw $e;
}