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
 *   Author: Yanlongli <jobs@yanlongli.com>、Zou Yiliang<>
 *   Date:   2019/11/13
 *   IDE:    PhpStorm
 *   Desc:
 */

namespace yanlongli\wechat\service;

use yanlongli\wechat\officialAccount\OfficialAccount;
use yanlongli\wechat\WechatException;

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
     * @param OfficialAccount $app
     * @param int|string $sceneId
     *
     * 场景值ID 为整数时:32位非0整型, 建议大于100000,避免与永久二唯码冲突
     * 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64
     *
     * @param ?int $expireSeconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），为null时默认有效期为30秒。
     * @return array
     * [
     *      'ticket'=>'gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9x',   //获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码
     *      'expire_seconds'=>'60',                                         //该二维码有效时间，以秒为单位。 最大不超过2592000（即30天)
     *      'url'=>'http://weixin.qq.com/q/kZgfwMTm72WWPkovabbI',           //二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
     * ]
     * @throws WechatException
     */
    public static function temporary(OfficialAccount $app, string $sceneId, int $expireSeconds = null)
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

        if (null !== $expireSeconds) {
            $data['expire_seconds'] = $expireSeconds;
        }

        return parent::request($url, $app, $data);
    }

    /**
     * 生成永久二唯码
     * @param OfficialAccount $app
     * @param int|string $sceneId 场景值ID 32位非0整型,最大值为100000,目前参数只支持1--100000; 字符串形式的ID，长度限制为1到64
     * @return array 返回值参考 QrcodeService::temporary()方法的返回值
     * @throws WechatException
     */
    public static function forever(OfficialAccount $app, $sceneId)
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

        return parent::request($url, $app, $data);
    }

    /**
     * 通过ticket换取二维码
     * @param string $ticket 获取二维码ticket后，用ticket换取二维码图片。本接口无须登录态即可调用
     * @return string 返回可用于 <img src="...">
     */
    public static function url(string $ticket)
    {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
    }
}
