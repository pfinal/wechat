<?php

namespace PFinal\Wechat\SDK\Redpack;

class  SDKRuntimeException extends \Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }

}

