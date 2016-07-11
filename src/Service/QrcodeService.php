<?php

namespace PFinal\Wechat\Service;

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
     * @param int $sceneId 场景值ID 32位非0整型, 建议大于100000,避免与永久二唯码冲突(永久二维码时最大值为100000,目前参数只支持1--100000)
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

        $data = array(
            'action_name' => 'QR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_id' => $sceneId,
                ),
            ),
        );

        if ($expireSeconds !== null) {
            $data['expire_seconds'] = $expireSeconds;
        }

        return parent::request($url, $data);
    }

    /**
     * 生成永久二唯码
     * @param int|string $sceneId 场景值ID 32位非0整型,最大值为100000,目前参数只支持1--100000; 字符串形式的ID，长度限制为1到64，仅永久二维码支持
     * @return array 返回值参考 QrcodeService::temporary()方法的返回值
     */
    public static function forever($sceneId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN';

        if (is_numeric($sceneId) && $sceneId >= 1 && $sceneId < 100000) {
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
}
