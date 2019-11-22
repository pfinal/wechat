<?php

namespace yanlongli\wechat\sdk\Redpack;

class CommonUtil
{
    /**
     *
     * @param string toURL
     * @param string paras
     * @return
     */
    function genAllUrl($toURL, $paras)
    {
        $allUrl = null;
        if (null === $toURL) {
            die("toURL is null");
        }
        if ("" === strripos($toURL, "?")) {
            $allUrl = $toURL . "?" . $paras;
        } else {
            $allUrl = $toURL . "&" . $paras;
        }

        return $allUrl;
    }

    function create_noncestr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            //$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $str;
    }

    /**
     *
     *
     * @param string src
     * @param string token
     * @return array
     */
    function splitParaStr($src, $token)
    {
        $resMap = array();
        $items = explode($token, $src);
        foreach ($items as $item) {
            $paraAndValue = explode("=", $item);
            if ($paraAndValue != "") {
                $resMap[$paraAndValue[0]] = $paraAndValue[1];
            }
        }
        return $resMap;
    }

    /**
     * trim
     *
     * @param string value
     * @return string
     */
    static function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (0 === strlen($ret)) {
                $ret = null;
            }
        }
        return $ret;
    }

    function formatQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v && "sign" != $k) {
                if ($urlencode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = null;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            //	if (null != $v && "null" != $v && "sign" != $k) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
            //}
        }
        $reqPar = null;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

}
