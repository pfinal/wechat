<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Api;
use PFinal\Wechat\Kernel;
use PFinal\Wechat\Support\Curl;
use PFinal\Wechat\WechatException;

/**
 * 生成带参数的二维码
 * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542
 * @author  Zou Yiliang
 * @since   1.0
 */
class QrcodeService extends BaseService
{
    /**
     * 生成临时二唯码
     * @param int|string $sceneId
     *
     * 场景值ID 为整数时:32位非0整型, 建议大于100000,避免与永久二唯码冲突
     * 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64
     *
     * @param null $expireSeconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），为null时默认有效期为30秒。
     * @return array
     * [
     *      'ticket'=>'gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9x',   //获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码
     *      'expire_seconds'=>'60',                                         //该二维码有效时间，以秒为单位。 最大不超过2592000（即30天)
     *      'url'=>'http://weixin.qq.com/q/kZgfwMTm72WWPkovabbI',           //二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
     * ]
     */
    public static function temporary($sceneId, $expireSeconds = null)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN';

        //0x7FFFFFFF 32位int最大值
        if (is_integer($sceneId) && $sceneId > 100000 && $sceneId <= 0x7FFFFFFF) {
            $data = array(
                'action_name' => 'QR_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_id' => $sceneId,
                    ),
                ),
            );
        } else {
            $data = array(
                'action_name' => 'QR_STR_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_str' => $sceneId,
                    ),
                ),
            );
        }

        if ($expireSeconds !== null) {
            $data['expire_seconds'] = $expireSeconds;
        }

        return parent::request($url, $data);
    }

    /**
     * 生成永久二唯码
     * @param int|string $sceneId 场景值ID 32位非0整型,最大值为100000,目前参数只支持1--100000; 字符串形式的ID，长度限制为1到64
     * @return array 返回值参考 QrcodeService::temporary()方法的返回值
     */
    public static function forever($sceneId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN';

        if (is_integer($sceneId) && $sceneId >= 1 && $sceneId <= 100000) {
            $data = array(
                'action_name' => 'QR_LIMIT_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_id' => $sceneId,
                    ),
                ),
            );
        } else {
            $data = array(
                'action_name' => 'QR_LIMIT_STR_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_str' => $sceneId,
                    ),
                ),
            );
        }

        return parent::request($url, $data);
    }

    /**
     * 通过ticket换取二维码
     * @param  string $ticket 获取二维码ticket后，用ticket换取二维码图片。本接口无须登录态即可调用
     * @return string 返回可用于 <img src="...">
     */
    public static function url($ticket)
    {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
    }

    /**
     * 生成小程序二维码
     * @see https://developers.weixin.qq.com/miniprogram/dev/api/getWXACodeUnlimit.html
     *
     * @param string $page 必须是已经发布的小程序存在的页面（否则报错），例如 pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
     * @param string $scene 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
     * @return mixed 直接返回图片二进制内容 如果要输出到浏览器，可以加上 header('Content-type: image/jpeg');
     * @throws WechatException
     */
    public static function miniProgramQrcode($page, $scene)
    {
        //page: pages/share-detail/main
        //scene: 567898

        //小程序配置信息
        $miniAppId = Kernel::getConfig('miniAppId');
        $miniSecret = Kernel::getConfig('miniSecret');

        $api = new Api($miniAppId, $miniSecret);
        $accessToken = $api->getAccessToken();

        //POST 参数需要转成 JSON 字符串，不支持 form 表单提交。
        $data = json_encode([

            //必须是已经发布的小程序存在的页面（否则报错），例如 pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
            'page' => $page,

            //最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
            'scene' => $scene,
        ]);

        //https://developers.weixin.qq.com/miniprogram/dev/api/getWXACodeUnlimit.html
        $client = new Curl();
        $image = $client->execute('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $accessToken, 'post', $data, [
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ]
            ]
        );

        if (strlen($image) > 500) {
            //header('Content-type: image');
            return $image;
        }

        //出错了
        $arr = @json_decode($image, true);

        //{"errcode":40001,"errmsg":"invalid credential, access_token is invalid or not latest hint: [AuPmBa0394vr53!]"}

        if (is_array($arr) && $arr['errcode'] == 40001) {

            //更新token
            $accessToken = $api->getAccessToken(false);

            //POST 参数需要转成 JSON 字符串，不支持 form 表单提交。
            $data = json_encode([

                //必须是已经发布的小程序存在的页面（否则报错），例如 pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
                'page' => $page,

                //最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
                'scene' => $scene,
            ]);

            $client = new Curl();
            $image = $client->execute('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $accessToken, 'post', $data, [
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                    ]
                ]
            );

            if (strlen($image) > 500) {
                //header('Content-type: image');
                return $image;
            }
            $arr = @json_decode($image, true);
        }

        throw new WechatException($arr['errmsg'], $arr['errcode']);
    }
}
