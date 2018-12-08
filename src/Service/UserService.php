<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Kernel;
use PFinal\Wechat\SDK\MiniAppCrypt\WXBizDataCrypt;
use PFinal\Wechat\WechatException;

class UserService extends BaseService
{
    /**
     * 获取用户基本信息
     * @param $openid
     * @param string $lang
     * @return array 数据如下
     *     [
     *          "subscribe": 1,  //用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息
     *          "openid": "otvxTs4dckWG7imySrJd6jSi0CWE",
     *          "nickname": "iWithery",
     *          "sex": 1,  //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *          "language": "zh_CN",
     *          "city": "Jieyang",
     *          "province": "Guangdong",
     *          "country": "China",
     *          "headimgurl": "http://wx.qlogo.cn/mmopen/xbIQx1GRqdvyqkMMhEaGOX802l1CyqMJNgUzKP8MeAeHFicRDSnZH7FY4XB7p8XHXIf6uJA2SCun
     *          TPicGKezDC4saKISzRj3nz/0",
     *          "subscribe_time": 1434093047,   //用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
     *          "unionid": "oR5GjjgEhCMJFyzaVZdrxZ2zRRF4",
     *          "remark": "",  //公众号运营者对粉丝的备注
     *          "groupid": 0,  //用户所在的分组ID（兼容旧的用户分组接口）
     *          "tagid_list":[128,2]  //用户被打上的标签ID列表
     *    ]
     *
     */
    public static function get($openid, $lang = 'zh_CN')
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=%s&lang=%s';
        $url = sprintf($url, $openid, $lang);

        return parent::request($url);
    }

    /**
     * 批量获取用户基本信息 最多支持一次拉取100条
     * @param array $openIds
     * @param string $lang
     * @return array 示例中为一次性拉取了2个openid的用户基本信息，第一个是已关注的，第二个是未关注的
     * [
     *      {
     *          "subscribe": 1,
     *          "openid": "otvxTs4dckWG7imySrJd6jSi0CWE",
     *          "nickname": "iWithery",
     *          "sex": 1,  //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *          "language": "zh_CN",
     *          "city": "Jieyang",
     *          "province": "Guangdong",
     *          "country": "China",
     *          "headimgurl": "http://wx.qlogo.cn/mmopen/xbIQx1GRqdvyqkMMhEaGOX802l1CyqMJNgUzKP8MeAeHFicRDSnZH7FY4XB7p8XHXIf6uJA2SCun
     *          TPicGKezDC4saKISzRj3nz/0",
     *          "subscribe_time": 1434093047,   //用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
     *          "unionid": "oR5GjjgEhCMJFyzaVZdrxZ2zRRF4",
     *          "remark": "",  //公众号运营者对粉丝的备注
     *          "groupid": 0,  //用户所在的分组ID（兼容旧的用户分组接口）
     *          "tagid_list":[128,2]  //用户被打上的标签ID列表
     *      },
     *      {
     *          "subscribe": 0,
     *          "openid": "otvxTs_JZ6SEiP0imdhpi50fuSZg",
     *          "unionid": "oR5GjjjrbqBZbrnPwwmSxFukE41U",
     *      }
     * ]
     */
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

    /**
     * 获取关注者列表
     *
     * 公众号可通过本接口来获取帐号的关注者列表，关注者列表由一串OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的）组成
     * 一次拉取调用最多拉取10000个关注者的OpenID
     * 将上一次调用得到的返回中的next_openid值，作为下一次调用中的next_openid值
     * 关注者列表已返回完时，返回next_openid为空
     *
     * @param string $nextOpenId
     * @return array ["total"=>关注该公众账号的总用户数,"count"=>本次拉取个数,"data"=>["openid":["","OPENID1","OPENID2"]],"next_openid"=>"NEXT_OPENID"]
     */
    public static function all($nextOpenId = '')
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=%s";
        $url = sprintf($url, $nextOpenId);

        return parent::request($url);
    }

    /**
     * 从小程序中获取用户手机号
     * 小程序 获取微信用户绑定的手机号，需先调用login接口，拿到 $sessionKey
     *
     * https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/getPhoneNumber.html
     *
     * countryCode
     * purePhoneNumber
     * phoneNumber
     * @throws WechatException
     */
    public static function getPhoneNumber($encryptedData, $iv, $sessionKey)
    {
        return self::decryptDataForMiniProgram($encryptedData, $iv, $sessionKey);
    }

    /**
     * 解密小程序数据 例如 wx.getUserInfo  getPhoneNumber
     *
     * https://developers.weixin.qq.com/miniprogram/dev/api/wx.getUserInfo.html
     * https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/getPhoneNumber.html
     *
     * @param $encryptedData
     * @param $iv
     * @param $sessionKey
     * @return array
     * @throws WechatException
     */
    public static function decryptDataForMiniProgram($encryptedData, $iv, $sessionKey)
    {
        //小程序配置信息
        $mini_appid = Kernel::getConfig('miniAppId');

        $pc = new WXBizDataCrypt($mini_appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode != 0) {
            throw new WechatException('解密失败' . $errCode);
        }
        return json_decode($data, true);
    }
}
