<?php

namespace PFinal\Wechat\Contract;

interface SendMessage extends Message
{
    /**
     * @return array
     */
    public function jsonData();
}