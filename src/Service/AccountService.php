<?php

namespace PFinal\Wechat\Service;

class AccountService extends BaseService
{
    public static function create($account, $nickname, $password)
    {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/add?access_token=ACCESS_TOKEN';
        return parent::request($url, compact('account', 'nickname', 'password'));
    }

    public static function update($account, $nickname, $password)
    {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/update?access_token=ACCESS_TOKEN';
        return parent::request($url, compact('account', 'nickname', 'password'));
    }

    /**
     * 设置客服帐号的头像
     * @param $account
     * @param string $filename 头像图片文件必须是jpg格式，推荐使用640*640大小的图片以达到最佳效果
     * @return array
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function avatar($account, $filename)
    {
        $url = 'http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=ACCESS_TOKEN&kf_account=' . $account;

        $filename = realpath($filename);

        if (class_exists('\CURLFile')) {
            $data['media'] = new \CURLFile($filename);
        } else {
            $data['media'] = '@' . $filename;
        }

        return parent::request($url, $data, false);
    }

    /**
     * 获取所有客服账号
     * @return array
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function all()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=ACCESS_TOKEN';

        $result = parent::request($url);
        return $result['kf_list'];
    }

}
