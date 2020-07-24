<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\ReplyMessage;

/**
 * 消息转发到客服
 */
class TransferCustomer implements ReplyMessage
{
    private $kfAccount;

    /**
     * TransferCustomer constructor.
     * @param null $kfAccount 指定一个客服帐号，也可以不指定
     */
    public function __construct($kfAccount = null)
    {
        $this->kfAccount = $kfAccount;
    }

    /**
     * @return array
     */
    public function xmlData()
    {
        if (empty($this->kfAccount)) {
            return array();
        }
        return array('TransInfo' => array('KfAccount' => $this->$kfAccount));
    }


    public function type()
    {
        return 'transfer_customer_service';
    }

}