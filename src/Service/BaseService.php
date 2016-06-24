<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Kernel;
use PFinal\Wechat\Support\Curl;
use PFinal\Wechat\Support\Json;
use PFinal\Wechat\WechatException;

class BaseService
{

    protected static function getApi()
    {
        return Kernel::getApi();
    }

    /**
     * 请求微信平台服务器，并解析返回的json字符串为数组，失败抛异常
     * @param $url
     * @param $data
     * @return array
     * @throws \PFinal\Wechat\WechatException
     */
    protected static function request($url, $data = null, $jsonEncode = true)
    {
        $executeUrl = str_replace('ACCESS_TOKEN', self::getApi()->getAccessToken(), $url);

        if ($jsonEncode) {
            $data = Json::encode($data);
        }

        try {

            return Json::parseOrFail(Curl::execute($executeUrl, is_null($data) ? 'get' : 'post', $data));

        } catch (WechatException $ex) {

            //更新AccessToken再次请求
            if ($ex->getCode() == 40001) {
                $executeUrl = str_replace('ACCESS_TOKEN', self::getApi()->getAccessToken(false), $url);
                return Json::parseOrFail(Curl::execute($executeUrl, is_null($data) ? 'get' : 'post', $data));
            }

            throw $ex;
        }
    }
}