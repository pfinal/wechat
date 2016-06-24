<?php

namespace PFinal\Wechat\Contract;

interface ReplyMessage extends Message
{
    /**
     * @return array
     */
    public function xmlData();
}