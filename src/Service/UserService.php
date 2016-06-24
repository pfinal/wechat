<?php

namespace PFinal\Wechat\Service;

class UserService extends BaseService
{
    public static function get($openid, $lang = 'zh_CN')
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=%s&lang=%s';
        $url = sprintf($url, $openid, $lang);

        return parent::request($url);
    }

    public static function batchGet(array $openIds, $lang = 'zh_CN')
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=ACCESS_TOKEN';

        $data = array();
        foreach ($openIds as $openid) {
            $data[] = array('openid' => $openid, 'lang' => $lang);
        }

        $data = array('user_list' => $data);

        $result = parent::request($url, $data);

        return $result['user_info_list'];
    }
}
