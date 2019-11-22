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

include '../vendor/autoload.php';
Config::loadConfigFile(__DIR__ . '/config.php');

$officialAccount = new OfficialAccount(Config::get('config.'));

try {
    $user = OAuthService::getOpenid($officialAccount);
    var_dump($user);
} catch (\yanlongli\wechat\WechatException $e) {
    throw $e;
}