<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\SendMessage;

/**
 * 发送卡券
 * 客服消息接口投放卡券仅支持非自定义Code码和导入code模式的卡券的卡券
 * http://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1451025056&token=&lang=zh_CN&anchor=2.2.2
 */
class Wxcard implements SendMessage
{
    protected $type = 'wxcard';
    protected $cardId;

    public function __construct($cardId)
    {
        $this->cardId = $cardId;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonData()
    {
        return array(
            'wxcard' => array(
                'card_id' => $this->cardId,
            ),
        );
    }
}