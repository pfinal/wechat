<?php

namespace PFinal\Wechat\Service;

class QrcodeService extends BaseService
{
    /**
     * 生成临时二唯码
     * @param int $sceneId 场景值ID 32位非0整型, 建议大于100000,避免与永久二唯码冲突
     * @param null $expireSeconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），为null时默认有效期为30秒。
     * @return array
     */
    public static function temporary($sceneId, $expireSeconds = null)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN';

        $data = array(
            'action_name' => 'QR_SCENE',
            'action_info' => array(
                'scene_id' => $sceneId,
            ),
        );

        if ($expireSeconds !== null) {
            $data['action_info']['expire_seconds'] = $expireSeconds;
        }

        return parent::request($url, $data);
    }


    public static function forever($sceneId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN';

        if (is_numeric($sceneId) && $sceneId >= 1 && $sceneId < 100000) {
            $data = array(
                'action_name' => 'QR_LIMIT_SCENE',
                'action_info' => array(
                    'scene_id' => $sceneId,
                ),
            );
        } else {
            $data = array(
                'action_name' => 'QR_LIMIT_STR_SCENE',
                'action_info' => array(
                    'scene_str' => $sceneId,
                ),
            );
        }

        return parent::request($url, $data);
    }

    public static function url($ticket)
    {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
    }


}
