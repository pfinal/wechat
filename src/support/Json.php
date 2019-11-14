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

namespace yanlongli\wechat\support;


use yanlongli\wechat\WechatException;

class Json
{
    /**
     * 将数据编码为json，用于请求微信平台服务器
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 解析微信平台返回的json字符串，转为数组，错误时，抛异常
     * @param $jsonStr
     * @return array
     * @throws WechatException
     */
    public static function parseOrFail($jsonStr)
    {
        $arr = json_decode($jsonStr, true);

        if (isset($arr['errcode']) && 0 !== $arr['errcode']) {
            if (empty($arr['errmsg'])) {
                $arr['errmsg'] = 'Unknown';
            }

            throw new WechatException($arr['errmsg'], $arr['errcode']);
        }
        return $arr;
    }
}