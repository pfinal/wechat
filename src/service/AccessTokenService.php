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
 *   Author: Yanlongli <jobs@yanlongli.com> 、Zou Yiliang<>
 *   Date:   2019/11/8
 *   IDE:    PhpStorm
 *   Desc:
 */
declare(strict_types=1);

namespace yanlongli\wechat\service;

use yanlongli\wechat\App;
use yanlongli\wechat\support\Json;
use yanlongli\wechat\WechatException;

/**
 * Class AccessTokenService
 * @package yanlongli\wechat\service
 */
class AccessTokenService extends BaseService
{

    /**
     * @param App $app
     * @param bool $useCache
     * @return string
     * @throws WechatException
     */
    public static function getAccessToken(App $app, bool $useCache = true)
    {
        //todo use $useCache
        //获取accessToken
//        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
//        $response = Curl::get(sprintf($url, $app->appId, $app->appSecret));
        //test
        $response = "{\"access_token\":\"27_hAQAdevf8_LTF_lLjT95kKHTdFqDA4cbWFkJbPsY71p83zRs9WTR2a8KOap5JzJedLxJMDs5oLQoQ7au_86JaQnXElVE93LGsGTgO1NuJog0JXICa83_oARkWh9n48ecEs6Vy5YK4k0SO-w7VCMaADALSC\",\"expires_in\":7200}";
        $arr = Json::parseOrFail($response);
        $app->accessToken = $arr['access_token'];

        return $app->accessToken;
    }
}