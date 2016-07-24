<?php

namespace PFinal\Wechat\Support;

use PFinal\Wechat\WechatException;

class Json
{
    /**
     * 将数据编码为json，用于请求微信平台服务器
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $str = json_encode($data);
            $str = preg_replace_callback(
                '#\\\u([0-9a-f]{4})#i',
                function ($matchs) {
                    return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
                },
                $str
            );
            return str_replace('\/', '/', $str);
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); //php 5.4.0
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