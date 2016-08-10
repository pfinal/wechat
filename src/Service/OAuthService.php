<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Support\Session;
use PFinal\Wechat\WechatException;

class OAuthService extends BaseService
{
    /**
     * 获取微信用户openid,此方法会跳转到微信授权页面获取用户授权然后返回
     * 在ajax中调用本方法，无效
     *
     * 请先填写授权回调页面域名
     * https://mp.weixin.qq.com 开发>接口权限>网页服务>网页账号>网页授权获取用户基本信息>授权回调页面域名
     *
     * @return string
     * @throws WechatException
     */
    public static function getOpenid()
    {
        $user = self::getUser(true);
        return $user['openid'];
    }

    /**
     * 获取微信用户信息,此方法会跳转到微信授权页面获取用户授权然后返回
     * 在ajax中调用本方法，无效
     *
     * @param bool|false $openidOnly 此参数为true时，仅返回openid 响应速度会更快，并且不需要用户点击同意授权
     * @return array
     * @throws WechatException
     */
    public static function getUser($openidOnly = false)
    {
        $flashKey = md5(__FILE__) . 'oAuthAuthState';

        //从微信oAuth页面跳转回来
        if (Session::hasFlash($flashKey)) {

            $flashData = @unserialize(Session::getFlash($flashKey));

            if (is_array($flashData) && (time() - $flashData[0] < 60) && isset($_GET['state']) && $_GET['state'] === $flashData[1]) {

                if (!isset($_GET['state'])) {
                    throw new WechatException('微信网页授权失败');
                }

                //通过code换取网页授权access_token
                $OauthAccessTokenArr = self::getOauthAccessToken($_GET['code']);

                if ($openidOnly) {
                    return $OauthAccessTokenArr;
                }

                //拉取用户信息(需scope为 snsapi_userinfo)
                return self::getOauthUserInfo($OauthAccessTokenArr['openid'], $OauthAccessTokenArr['access_token']);
            }
        }

        //当前url
        $uri = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        //跳转到微信oAuth授权页面
        $state = uniqid();
        Session::setFlash($flashKey, serialize(array(time(), $state)));

        self::redirect($uri, $state, $openidOnly ? 'snsapi_base' : 'snsapi_userinfo');
    }

    /**
     * 跳转到微信平台，让用户同意授权，获取code
     * @param $redirectUri
     * @param string $scopes
     */
    public static function redirect($redirectUri, $state = '0', $scopes = 'snsapi_userinfo')
    {
        //通过一个中间url
        $middleUrl = parent::getApi()->getMiddleUrl();
        if ($middleUrl !== null) {
            $redirectUri = $middleUrl . ((strpos($middleUrl, '?') === false) ? '?' : '&') . 'url=' . urlencode($redirectUri);
        }

        //跳转到微信授权url
        $url = self::getOauthAuthorizeUrl($redirectUri, $state, $scopes);

        header('Location: ' . $url);
        exit;
    }

    /**
     * 网页授权获取用户基本信息 流程第1步 引导用户进入授权页面的Url (用户允许后，获取code)
     * @param string $redirectUri 授权后重定向的回调链接地址
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo（弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public static function getOauthAuthorizeUrl($redirectUri, $state = '0', $scope = 'snsapi_userinfo')
    {
        $appId = parent::getApi()->getAppId();

        $redirectUri = urlencode($redirectUri);

        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appId}&redirect_uri={$redirectUri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    /**
     * 网页授权获取用户基本信息 流程第2步 通过code换取网页授权access_token
     * @param $code
     * @return array
     * [
     *      "access_token"=>"ACCESS_TOKEN",
     *      "expires_in"=>7200,    //access_token接口调用凭证超时时间，单位（秒）
     *      "refresh_token"=>"REFRESH_TOKEN",
     *      "openid"=>"OPENID",
     *      "scope"=>"SCOPE"
     * ]
     */
    public static function getOauthAccessToken($code)
    {
        $appId = parent::getApi()->getAppId();
        $secret = parent::getApi()->getAppSecret();
        $grant_type = 'authorization_code';

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appId}&secret={$secret}&code={$code}&grant_type={$grant_type}";

        return parent::request($url);
    }

    /**
     * 网页授权获取用户基本信息 流程第3步 刷新access_token（如果需要）
     */
    public static function refreshOauthAccessToken($refreshToken)
    {
        //todo
    }

    /**
     * 网页授权获取用户基本信息 流程第4步 拉取用户信息
     * @param $openId
     * @param $accessToken
     * @return array
     * [
     *    'openid'      //用户的唯一标识
     *    'nickname'    //用户昵称
     *    'sex'         //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *    'province'    //用户个人资料填写的省份
     *    'city'        //普通用户个人资料填写的城市
     *    'country'     //国家，如中国为CN
     *    'headimgurl'  //用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     * ]
     */
    public static function getOauthUserInfo($openId, $accessToken)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
        return parent::request($url);
    }
}
