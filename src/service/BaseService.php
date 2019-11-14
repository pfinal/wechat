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
 *   Author: Yanlongli <jobs@yanlongli.com>、Zou Yiliang<>
 *   Date:   2019/11/13
 *   IDE:    PhpStorm
 *   Desc:
 */
declare(strict_types=1);

namespace yanlongli\wechat\service;


use yanlongli\wechat\App;
use yanlongli\wechat\support\Curl;
use yanlongli\wechat\support\Json;
use yanlongli\wechat\Wechat;
use yanlongli\wechat\WechatException;

class BaseService
{

    /**
     * 请求微信平台服务器，并解析返回的json字符串为数组，失败抛异常
     * @param string $url
     * @param App $app
     * @param array|null $data
     * @param bool $jsonEncode
     * @return array
     * @throws WechatException
     */
    public static function request(string $url, App $app, array $data = null, bool $jsonEncode = true)
    {
        // 修正 仅在含有需要token的链接中替换token
        if (strpos($url, 'ACCESS_TOKEN') !== false) {
            $executeUrl = str_replace('ACCESS_TOKEN', Wechat::getApp($app->appId)->getAccessToken(), $url);
        } else {
            $executeUrl = $url;
        }
        if ($jsonEncode) {
            $data = Json::encode($data);
        }

        try {

            return Json::parseOrFail(Curl::execute($executeUrl, is_null($data) ? 'get' : 'post', $data));

        } catch (WechatException $ex) {

            //更新AccessToken再次请求
            if ($ex->getCode() == 40001) {
                $executeUrl = str_replace('ACCESS_TOKEN', Wechat::getApp($app->appId)->getAccessToken(false), $url);
                return Json::parseOrFail(Curl::execute($executeUrl, is_null($data) ? 'get' : 'post', $data));
            }

            throw $ex;
        }
    }
}