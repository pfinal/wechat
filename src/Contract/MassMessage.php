<?php

namespace PFinal\Wechat\Contract;

interface MassMessage extends Message
{
    /**
     * @return array
     */
    public function jsonData();
}