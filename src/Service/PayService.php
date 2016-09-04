<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Kernel;
use PFinal\Wechat\Support\Log;

/**
 * 微信支付
 */
class PayService
{
    public static function getConfig()
    {
        return array(
            'mch_id' => Kernel::getConfig('mchId'),
            'appid' => Kernel::getConfig('appId'),
            'key' => Kernel::getConfig('apiSecret'),//https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
        );
    }

    /**
     * https://pay.weixin.qq.com/wiki/doc/api/wap.php?chapter=9_1
     *
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     *      https://mp.weixin.qq.com/  微信支付-开发配置-测试目录
     *      测试目录 http://www.example.com/paytest/    最后需要斜线，(需要精确到二级或三级目录)
     * @return string
     */
    public static function createJsBizPackage($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp)
    {
        $config = self::getConfig();

        $unified = array(
            'appid' => $config['appid'],
            'attach' => '支付',                          //商家数据包，原样返回
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notifyUrl,
            'openid' => $openid,                        //rade_type=JSAPI，此参数必传
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'total_fee' => sprintf('%.0f', self::calc($totalFee, 100, '*', 2)),//单位 转为分
            'trade_type' => 'JSAPI',
        );

        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));

        /*
        <xml>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <return_msg><![CDATA[OK]]></return_msg>
        <appid><![CDATA[wx00e5904efec77699]]></appid>
        <mch_id><![CDATA[1220647301]]></mch_id>
        <nonce_str><![CDATA[1LHBROsdmqfXoWQR]]></nonce_str>
        <sign><![CDATA[ACA7BC8A9164D1FBED06C7DFC13EC839]]></sign>
        <result_code><![CDATA[SUCCESS]]></result_code>
        <prepay_id><![CDATA[wx2015032016590503f1bcd9c30421762652]]></prepay_id>
        <trade_type><![CDATA[JSAPI]]></trade_type>
        </xml>
        */
        $unifiedOrder = @simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        //Log::info($responseXml);

        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ("{$unifiedOrder->return_code}" !== 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ("{$unifiedOrder->result_code}" !== 'SUCCESS') {
            die($unifiedOrder->err_code);
            /*
            NOAUTH  商户无此接口权限
            NOTENOUGH  余额不足
            ORDERPAID  商户订单已支付
            ORDERCLOSED  订单已关闭
            SYSTEMERROR  系统错误
            APPID_NOT_EXIST     APPID不存在
            MCHID_NOT_EXIST  MCHID不存在
            APPID_MCHID_NOT_MATCH appid和mch_id不匹配
            LACK_PARAMS 缺少参数
            OUT_TRADE_NO_USED 商户订单号重复
            SIGNERROR 签名错误
            XML_FORMAT_ERROR XML格式错误
            REQUIRE_POST_METHOD 请使用post方法
            POST_DATA_EMPTY post数据为空
            NOT_UTF8 编码格式错误
           */
        }

        //$unifiedOrder->trade_type  交易类型  调用接口提交的交易类型，取值如下：JSAPI，NATIVE，APP
        //$unifiedOrder->prepay_id  预支付交易会话标识 微信生成的预支付回话标识，用于后续接口调用中使用，该值有效期为2小时
        //$unifiedOrder->code_url 二维码链接 trade_type为NATIVE是有返回，可将该参数值生成二维码展示出来进行扫码支付

        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => $timestamp,
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
        );

        $arr['paySign'] = self::getSign($arr, $config['key']);
        return $arr;
    }

    /**
     * 获取微信支付异步通知。(支付金额单位已转为元)
     * 成功返回通知信息数组,失败返回null
     *
     * @return array
     * [
     *      $mch_id          //微信支付分配的商户号
     *      $appid           //微信分配的公众账号ID
     *      $openid          //用户在商户appid下的唯一标识
     *      $transaction_id  //微信支付订单号
     *      $out_trade_no    //商户订单号
     *      $total_fee       //订单总金额单位默认为分，已转为元
     *      $is_subscribe    //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
     *      $attach          //商家数据包，原样返回
     *      $time_end        //支付完成时间
     * ]
     */
    public static function notify()
    {
        $config = self::getConfig();

        $postStr = file_get_contents('php://input');

        /*
        $postStr = '<xml>
        <appid><![CDATA[wx00e5904efec77699]]></appid>
        <attach><![CDATA[支付测试]]></attach>
        <bank_type><![CDATA[CMB_CREDIT]]></bank_type>
        <cash_fee><![CDATA[1]]></cash_fee>
        <fee_type><![CDATA[CNY]]></fee_type>
        <is_subscribe><![CDATA[Y]]></is_subscribe>
        <mch_id><![CDATA[1220647301]]></mch_id>
        <nonce_str><![CDATA[a0tZ41phiHm8zfmO]]></nonce_str>
        <openid><![CDATA[oU3OCt5O46PumN7IE87WcoYZY9r0]]></openid>
        <out_trade_no><![CDATA[550bf2990c51f]]></out_trade_no>
        <result_code><![CDATA[SUCCESS]]></result_code>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <sign><![CDATA[F6F519B4DD8DB978040F8C866C1E6250]]></sign>
        <time_end><![CDATA[20150320181606]]></time_end>
        <total_fee>1</total_fee>
        <trade_type><![CDATA[JSAPI]]></trade_type>
        <transaction_id><![CDATA[1008840847201503200034663980]]></transaction_id>
        </xml>';
        */
        $postObj = @simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($postObj === false) {
            Log::error('parse xml error: ' . $postStr);
            return;
        }
        if ($postObj->return_code != 'SUCCESS') {
            Log::error($postObj->return_msg);
            return;
        }
        if ($postObj->result_code != 'SUCCESS') {
            Log::error($postObj->result_code);
            return;
        }

        $arr = (array)$postObj;
        unset($arr['sign']);

        if (self::getSign($arr, $config['key']) === "{$postObj->sign}") {

            // $mch_id = $postObj->mch_id;  //微信支付分配的商户号
            // $appid = $postObj->appid; //微信分配的公众账号ID
            // $openid = $postObj->openid; //用户在商户appid下的唯一标识
            // $transaction_id = $postObj->transaction_id;//微信支付订单号
            // $out_trade_no = $postObj->out_trade_no;//商户订单号
            // $total_fee = $postObj->total_fee; //订单总金额，单位为分
            // $is_subscribe = $postObj->is_subscribe; //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
            // $attach = $postObj->attach;//商家数据包，原样返回
            // $time_end = $postObj->time_end;//支付完成时间

            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';

            //金额单位转为元
            $postObj->total_fee = self::calc($postObj->total_fee, 100, '/', 2);

            return $postObj;
        } else {
            Log::error('sign error');
        }
    }

    /**
     * curl get
     *
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public static function arrayToXml($arr)
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

    /**
     * 例如：
     * appid：    wxd930ea5d5a258f4f
     * mch_id：    10000100
     * device_info：  1000
     * Body：    test
     * nonce_str：  ibuaiVcKdpRxkhJA
     * 第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
     * stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_id=10000100&nonce_str=ibuaiVcKdpRxkhJA";
     * 第二步：拼接支付密钥：
     * stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
     * sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A9CF3B7"
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     * 精度计算
     * 默认使用bcmath扩展
     * 如果没有启用bcmath扩展: 比较大小时转为整数比较、计算时使用number_format格式化返回值
     *
     * var_dump(floor((0.1 + 0.7) * 10)); // 7
     * var_dump(floor(\Rain\self::calc(\Rain\self::calc(0.1, 0.7, '+'), 10, '*'))); // 8
     *
     * @param string $a
     * @param string $b
     * @param string $operator 操作符 支持: "+"、 "-"、 "*"、 "/"、 "comp"
     * @param int $scale 小数精度位数，默认2位
     * @return string|int 加减乖除运算，返回string。比较大小时，返回int(相等返回0, $a大于$b返回1, 否则返回-1)
     * @throws \Exception
     */
    public static function calc($a, $b, $operator, $scale = 2)
    {
        $scale = (int)$scale;
        $scale = $scale < 0 ? 0 : $scale;

        $bc = array(
            '+' => 'bcadd',
            '-' => 'bcsub',
            '*' => 'bcmul',
            '/' => 'bcdiv',
            'comp' => 'bccomp',
        );

        if (!array_key_exists($operator, $bc)) {
            throw new \Exception('operator error');
        }

        if (function_exists($bc[$operator])) {
            $fun = $bc[$operator];
            return $fun($a, $b, $scale);
        }

        switch ($operator) {
            case '+':
                $c = $a + $b;
                break;
            case '-':
                $c = $a - $b;
                break;
            case '*':
                $c = $a * $b;
                break;
            case '/':
                $c = $a / $b;
                break;
            case 'comp':

                // 按指定精度，去掉小数点，放大为整数字符串
                $a = ltrim(number_format((float)$a, $scale, '', ''), '0');
                $b = ltrim(number_format((float)$b, $scale, '', ''), '0');
                $a = $a === '' ? '0' : $a;
                $b = $b === '' ? '0' : $b;

                if ($a === $b) {
                    return 0;
                }

                return $a > $b ? 1 : -1;

            default:
                throw new \Exception('operator error');
        }

        $c = number_format($c, $scale, '.', '');

        return $c;
    }
}