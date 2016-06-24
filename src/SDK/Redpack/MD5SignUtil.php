<?php

namespace PFinal\Wechat\SDK\Redpack;

class MD5SignUtil
{

    function sign($content, $key)
    {
        try {
            if (null == $key) {
                throw new SDKRuntimeException("财付通签名key不能为空！" . "<br>");
            }
            if (null == $content) {
                throw new SDKRuntimeException("财付通签名内容不能为空" . "<br>");
            }
            $signStr = $content . "&key=" . $key;

            return strtoupper(md5($signStr));
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    function verifySignature($content, $sign, $md5Key)
    {
        $signStr = $content . "&key=" . $md5Key;
        $calculateSign = strtolower(md5($signStr));
        $tenpaySign = strtolower($sign);
        return $calculateSign == $tenpaySign;
    }

}

