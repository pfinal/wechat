<?php

namespace yanlongli\wechat\sdk\Redpack;

class  sdkRuntimeException extends \Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }

}

