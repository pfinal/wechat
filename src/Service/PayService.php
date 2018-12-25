<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Kernel;
use PFinal\Wechat\SDK\Redpack\CommonUtil;
use PFinal\Wechat\Support\Log;
use PFinal\Wechat\WechatException;

/**
 * 微信支付
 * https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_4
 */
class PayService
{
    public static function getConfig()
    {
        return array(
            'appid' => Kernel::getConfig('appId'),          //微信公众号appid
            'mini_appid' => Kernel::getConfig('miniAppId'), //微信小程序appid
            'mch_id' => Kernel::getConfig('mchId'),
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
     *
     * @param $timestamp
     * @return array
     * @throws WechatException
     */
    public static function createJsBizPackage($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp)
    {
        // notify_url 中包含问号时，有可能会导致回调时签名验证不成功
        if (strpos($notifyUrl, '?') !== false) {
            Log::warning('notify_url中包含问号:' . $notifyUrl);
            throw new WechatException('notify_url error: cannot contain "?"');
        }

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

        //<xml>
        //    <return_code><![CDATA[SUCCESS]]></return_code>
        //    <return_msg><![CDATA[OK]]></return_msg>
        //    <appid><![CDATA[wx00e5904efec77699]]></appid>
        //    <mch_id><![CDATA[1220647301]]></mch_id>
        //    <nonce_str><![CDATA[1LHBROsdmqfXoWQR]]></nonce_str>
        //    <sign><![CDATA[ACA7BC8A9164D1FBED06C7DFC13EC839]]></sign>
        //    <result_code><![CDATA[SUCCESS]]></result_code>
        //    <prepay_id><![CDATA[wx2015032016590503f1bcd9c30421762652]]></prepay_id>
        //    <trade_type><![CDATA[JSAPI]]></trade_type>
        //</xml>

        //https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=23_5
        libxml_disable_entity_loader(true);
        $unifiedOrder = @simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($unifiedOrder === false) {
            Log::warning('parse xml error: ' . $responseXml);
            throw new WechatException('parse xml error');
        }
        if ((string)$unifiedOrder->return_code !== 'SUCCESS') {
            Log::warning('return_code: ' . $unifiedOrder->return_msg);
            throw new WechatException('return_code: ' . $unifiedOrder->return_msg);
        }
        if ((string)$unifiedOrder->result_code !== 'SUCCESS') {
            Log::warning('result_code: ' . $unifiedOrder->err_code);
            throw new WechatException('result_code: ' . $unifiedOrder->err_code);

            //NOAUTH  商户无此接口权限
            //NOTENOUGH  余额不足
            //ORDERPAID  商户订单已支付
            //ORDERCLOSED  订单已关闭
            //SYSTEMERROR  系统错误
            //APPID_NOT_EXIST     APPID不存在
            //MCHID_NOT_EXIST  MCHID不存在
            //APPID_MCHID_NOT_MATCH appid和mch_id不匹配
            //LACK_PARAMS 缺少参数
            //OUT_TRADE_NO_USED 商户订单号重复
            //SIGNERROR 签名错误
            //XML_FORMAT_ERROR XML格式错误
            //REQUIRE_POST_METHOD 请使用post方法
            //POST_DATA_EMPTY post数据为空
            //NOT_UTF8 编码格式错误
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
     * 小程序请求支付
     * https://developers.weixin.qq.com/miniprogram/dev/api/api-pay.html
     *
     * @param $openid
     * @param float $totalFee 金额 单位元
     * @param $outTradeNo
     * @param $orderName
     * @param $notifyUrl
     * @param $timestamp
     * @return array
     */
    public static function miniRequestPayment($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp)
    {
        //订单名太长支付会失败
        //$orderName = mb_strlen($orderName) > 10 ? (mb_substr($orderName, 10) . '...') : $orderName;

        $config = self::getConfig();

        //小程序配置信息
        $mini_appid = $config['mini_appid'];

        //支付配置
        $mch_id = $config['mch_id'];
        $payKey = $config['key'];

        $timeStamp = (string)$timestamp;
        $nonceStr = self::createNonceStr();
        $signType = 'MD5';

        $unified = array(
            'appid' => $config['mini_appid'],
            'mch_id' => $mch_id,
            'nonce_str' => $nonceStr,
            'body' => $orderName,
            'out_trade_no' => $outTradeNo,
            'total_fee' => sprintf('%.0f', self::calc($totalFee, 100, '*', 2)),//单位 转为分
            'spbill_create_ip' => '127.0.0.1',  //终端IP
            'notify_url' => $notifyUrl,
            'trade_type' => 'JSAPI',

            //'attach' => '支付',                          //商家数据包，原样返回

            //rade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。openid如何获取
            'openid' => $openid,                        //rade_type=JSAPI，此参数必传
        );

        $arr = static::unifiedOrder($unified, $payKey);
        $package = 'prepay_id=' . $arr['prepay_id'];

        $paySignData = array(
            'appId' => $mini_appid,
            'timeStamp' => $timeStamp,
            'nonceStr' => $nonceStr,
            'package' => $package,
            'signType' => 'MD5',
        );

        $paySign = PayService::getSign($paySignData, $payKey);

        $requestPayment = compact('timeStamp', 'nonceStr', 'package', 'signType', 'paySign');

        return $requestPayment;
    }


    public static function unifiedOrder($unified, $payKey)
    {

//        $unified = array(
//            'appid' => $config['appid'],
//            'mch_id' => $config['mch_id'],
//            'nonce_str' => self::createNonceStr(),
//            'body' => $orderName,
//            'out_trade_no' => $outTradeNo,
//            'total_fee' => sprintf('%.0f', self::calc($totalFee, 100, '*', 2)),//单位 转为分
//            'spbill_create_ip' => '127.0.0.1',  //终端IP
//            'notify_url' => $notifyUrl,
//            'trade_type' => 'JSAPI',
//
//            //'attach' => '支付',                          //商家数据包，原样返回
//
//            //rade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。openid如何获取
//            'openid' => $openid,                        //rade_type=JSAPI，此参数必传
//        );

        $unified['sign_type'] = 'MD5';
        $unified['sign'] = self::getSign($unified, $payKey);

        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));


        //<xml>
        //    <return_code><![CDATA[SUCCESS]]></return_code>
        //    <return_msg><![CDATA[OK]]></return_msg>
        //    <appid><![CDATA[wx00e5904efec77699]]></appid>
        //    <mch_id><![CDATA[1220647301]]></mch_id>
        //    <nonce_str><![CDATA[1LHBROsdmqfXoWQR]]></nonce_str>
        //    <sign><![CDATA[ACA7BC8A9164D1FBED06C7DFC13EC839]]></sign>
        //    <result_code><![CDATA[SUCCESS]]></result_code>
        //    <prepay_id><![CDATA[wx2015032016590503f1bcd9c30421762652]]></prepay_id>
        //    <trade_type><![CDATA[JSAPI]]></trade_type>
        //</xml>

        libxml_disable_entity_loader(true);
        $unifiedOrder = @simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($unifiedOrder === false) {
            Log::warning('parse xml error: ' . $responseXml);
            throw new WechatException('parse xml error');
        }
        if ((string)$unifiedOrder->return_code !== 'SUCCESS') {
            Log::warning('return_code: ' . $unifiedOrder->return_msg);
            throw new WechatException('return_code: ' . $unifiedOrder->return_msg);
        }
        if ((string)$unifiedOrder->result_code !== 'SUCCESS') {
            Log::warning('result_code: ' . $unifiedOrder->err_code);
            throw new WechatException('result_code: ' . $unifiedOrder->err_code);

            //NOAUTH  商户无此接口权限
            //NOTENOUGH  余额不足
            //ORDERPAID  商户订单已支付
            //ORDERCLOSED  订单已关闭
            //SYSTEMERROR  系统错误
            //APPID_NOT_EXIST     APPID不存在
            //MCHID_NOT_EXIST  MCHID不存在
            //APPID_MCHID_NOT_MATCH appid和mch_id不匹配
            //LACK_PARAMS 缺少参数
            //OUT_TRADE_NO_USED 商户订单号重复
            //SIGNERROR 签名错误
            //XML_FORMAT_ERROR XML格式错误
            //REQUIRE_POST_METHOD 请使用post方法
            //POST_DATA_EMPTY post数据为空
            //NOT_UTF8 编码格式错误
        }

        //$unifiedOrder->trade_type  交易类型  调用接口提交的交易类型，取值如下：JSAPI，NATIVE，APP
        //$unifiedOrder->prepay_id  预支付交易会话标识 微信生成的预支付回话标识，用于后续接口调用中使用，该值有效期为2小时
        //$unifiedOrder->code_url 二维码链接 trade_type为NATIVE是有返回，可将该参数值生成二维码展示出来进行扫码支付

        return (array)$unifiedOrder;
    }

    /**
     * 获取微信支付异步通知 (支付金额单位已转为元)
     *
     * 验证成功返回数组
     *
     * @return array
     *
     * [
     *      mch_id          //微信支付分配的商户号
     *      appid           //微信分配的公众账号ID
     *      openid          //用户在商户appid下的唯一标识
     *      transaction_id  //微信支付订单号
     *      out_trade_no    //商户订单号
     *      total_fee       //订单总金额单位默认为分，已转为元
     *      is_subscribe    //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
     *      attach          //商家数据包，原样返回
     *      time_end        //支付完成时间
     * ]
     *
     * @throws WechatException
     */
    public static function notify($checkSign = true)
    {
        $postStr = file_get_contents('php://input');

        //$postStr = '<xml>
        //    <appid><![CDATA[wx00e5904efec77699]]></appid>
        //    <attach><![CDATA[支付测试]]></attach>
        //    <bank_type><![CDATA[CMB_CREDIT]]></bank_type>
        //    <cash_fee><![CDATA[1]]></cash_fee>
        //    <fee_type><![CDATA[CNY]]></fee_type>
        //    <is_subscribe><![CDATA[Y]]></is_subscribe>
        //    <mch_id><![CDATA[1220647301]]></mch_id>
        //    <nonce_str><![CDATA[a0tZ41phiHm8zfmO]]></nonce_str>
        //    <openid><![CDATA[oU3OCt5O46PumN7IE87WcoYZY9r0]]></openid>
        //    <out_trade_no><![CDATA[550bf2990c51f]]></out_trade_no>
        //    <result_code><![CDATA[SUCCESS]]></result_code>
        //    <return_code><![CDATA[SUCCESS]]></return_code>
        //    <sign><![CDATA[F6F519B4DD8DB978040F8C866C1E6250]]></sign>
        //    <time_end><![CDATA[20150320181606]]></time_end>
        //    <total_fee>1</total_fee>
        //    <trade_type><![CDATA[JSAPI]]></trade_type>
        //    <transaction_id><![CDATA[1008840847201503200034663980]]></transaction_id>
        //</xml>';

        libxml_disable_entity_loader(true);
        $postObj = @simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($postObj == false) {
            Log::warning('parse xml error: ' . $postStr);
            throw new WechatException('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            Log::warning('return_code: ' . $postObj->return_code);
            throw new WechatException('return_code: ' . $postObj->return_code);
        }
        if ($postObj->result_code != 'SUCCESS') {
            Log::warning('result_code: ' . $postObj->result_code);
            throw new WechatException('result_code: ' . $postObj->result_code);
        }

        $postArr = (array)$postObj;

        //不验证签名，主要用于获取appid、mch_id
        if (!$checkSign) {
            return $postArr;
        }

        $signArr = $postArr;
        unset($signArr['sign']);

        $config = self::getConfig();

        if (self::getSign($signArr, $config['key']) === $postArr['sign']) {

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
            $postArr['total_fee'] = self::calc($postArr['total_fee'], 100, '/', 2);

            return $postArr;
        } else {
            Log::warning('sign error');
            throw new WechatException('sign error');
        }
    }

    /**
     * 申请退款
     *
     * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态
     *
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_4
     *
     * 1、交易时间超过一年的订单无法提交退款
     * 2、微信支付退款支持单笔交易分多次退款，多次退款需要提交原支付订单的商户订单号和设置不同的退款单号。申请退款总金额不能超过订单金额。 一笔退款失败后重新提交，请不要更换退款单号，请使用原商户退款单号
     * 3、请求频率限制：150qps，即每秒钟正常的申请退款请求次数不超过150次 错误或无效请求频率限制：6qps，即每秒钟异常或错误的退款申请请求不超过6次
     * 4、每个支付订单的部分退款次数不能超过50次
     *
     * @param string $out_trade_no 商户订单号 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*@ ，且在同一个商户号下唯一 (transaction_id out_trade_no 二选一)
     * @param float $total_fee 订单金额 单位为元，精度为2位小数  此方法中会自动转为分单位
     * @param float $refund_fee 退款总金额 单位为元，精度为2位小数
     * @param string $out_refund_no 商户退款单号 商户系统内部的退款单号，商户系统内部唯一，只能是数字、大小写字母_-|*@ ，同一退款单号多次请求只退一笔。
     * @param string $transaction_id 微信订单号 String(28) 微信生成的订单号，在支付通知中有返回 (transaction_id out_trade_no 二选一)
     * @return array 成功返回如下数组，失败将抛出异常
     * Array
     * (
     *     [return_code] => SUCCESS
     *     [return_msg] => OK
     *     [appid] => wx4a38afb33f0d02f4
     *     [mch_id] => 1494589642
     *     [nonce_str] => tw2hpp5QVBP6pP8L
     *     [sign] => 1AEFD3744A1295C0795F3C3DC2109E32
     *     [result_code] => SUCCESS
     *     [transaction_id] => 4200000097201801314500515963
     *     [out_trade_no] => d1c8081837154fb5257d243b8966a4b9
     *     [out_refund_no] => 5a71d29ca49e6
     *     [refund_id] => 50000305562018013103367483357
     *     [refund_channel] => SimpleXMLElement Object
     *         (
     *         )
     *
     *     [refund_fee] => 1
     *     [coupon_refund_fee] => 0
     *     [total_fee] => 1
     *     [cash_fee] => 1
     *     [coupon_refund_count] => 0
     *     [cash_refund_fee] => 1
     * )
     */
    public static function refund($out_trade_no, $total_fee, $refund_fee, $out_refund_no, $transaction_id = '')
    {
        $config = self::getConfig();
        $helper = Kernel::getRedpackHelper();

        $total_fee = $total_fee * 100;//金额，转为单位分
        $total_fee = number_format($total_fee, 0, '.', '');//去掉小数
        if ($total_fee <= 0) {
            throw new \Exception('金额不能小于或等于0');
        }

        $refund_fee = $refund_fee * 100;//红包金额，转为单位分
        $refund_fee = number_format($refund_fee, 0, '.', '');//去掉小数
        if ($refund_fee <= 0) {
            throw new \Exception('金额不能小于或等于0');
        }

        $data = [
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'out_refund_no' => $out_refund_no,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'refund_fee' => $refund_fee,
            'transaction_id' => $transaction_id,
        ];
        $sign = self::getSign($data, $config['key']);

        $postXml = <<<XML
<xml>
   <appid>{$data['appid']}</appid>
   <mch_id>{$data['mch_id']}</mch_id>
   <nonce_str>{$data['nonce_str']}</nonce_str> 
   <out_refund_no>{$data['out_refund_no']}</out_refund_no>
   <out_trade_no>{$data['out_trade_no']}</out_trade_no>
   <refund_fee>{$data['refund_fee']}</refund_fee>
   <total_fee>{$data['total_fee']}</total_fee>
   <transaction_id>{$data['transaction_id']}</transaction_id>
   <sign>{$sign}</sign>
</xml>
XML;
        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

        $responseXml = $helper->curl_post_ssl($url, $postXml);

        if ($responseXml === false) {
            throw new \Exception("curl失败");
        }

        Log::debug($responseXml);

        libxml_disable_entity_loader(true);
        $responseObj = @simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($responseObj === false) {
            throw new \Exception("解析xml失败");
        }

        //SUCCESS/FAIL  SUCCESS退款申请接收成功，结果通过退款查询接口查询 FAIL 提交业务失败
        if ('SUCCESS' === (string)$responseObj->result_code) {
            return (array)$responseObj;
        }

        Log::warning('refund error', (array)$responseObj);
        //{"return_code":"SUCCESS","return_msg":"OK","appid":"wxd1048134f34b25d9","mch_id":"1510414541","nonce_str":"qfcr3qFBZWaSeBSv","sign":"98C20C8148B2328C31AA844E51E97B16","result_code":"FAIL","err_code":"NOTENOUGH","err_code_des":"基本账户余额不足，请充值后重新发起"}

        throw new \Exception($responseObj->err_code . ' ' . $responseObj->return_msg . ' ' . $responseObj->err_code_des);
    }

    /**
     * 查询退款
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_5
     *
     * 四选一( refund_id   out_refund_no  transaction_id   out_trade_no)
     * 微信订单号查询的优先级是： refund_id > out_refund_no > transaction_id > out_trade_no
     *
     * @param string $out_refund_no 商户退款单号 商户系统内部的退款单号 申请退款时生成
     * @param string $refund_id 微信退款单号 微信生成的退款单号，在申请退款接口有返回
     * @param string $out_trade_no 商户订单号
     * @param string $transaction_id 微信订单号
     *
     * @return array
     * Array
     * (
     *     [appid] => wx4a38afb33f0d02f4
     *     [cash_fee] => 1
     *     [mch_id] => 1494589642
     *     [nonce_str] => e2EMFq897chBJYYz
     *     [out_refund_no_0] => 5a71d29ca49e6
     *     [out_trade_no] => d1c8081837154fb5257d243b8966a4b9
     *     [refund_account_0] => REFUND_SOURCE_UNSETTLED_FUNDS
     *     [refund_channel_0] => ORIGINAL
     *     [refund_count] => 1
     *     [refund_fee] => 1
     *     [refund_fee_0] => 1
     *     [refund_id_0] => 50000305562018013103367483357
     *     [refund_recv_accout_0] => 支付用户的零钱
     *     [refund_status_0] => PROCESSING
     *     [result_code] => SUCCESS
     *     [return_code] => SUCCESS
     *     [return_msg] => OK
     *     [sign] => 8C5D54311D0134AA932C6D8C7133C8F8
     *     [total_fee] => 1
     *     [transaction_id] => 4200000097201801314500515963
     * )
     */
    public static function refundQuery($out_refund_no, $refund_id = '', $out_trade_no = '', $transaction_id = '')
    {
        $config = self::getConfig();
        $helper = Kernel::getRedpackHelper();

        $data = [
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'out_refund_no' => $out_refund_no,
            'out_trade_no' => $out_trade_no,
            'refund_id' => $refund_id,
            'transaction_id' => $transaction_id,
        ];
        $sign = self::getSign($data, $config['key']);

        $postXml = <<<XML
<xml>
   <appid>{$data['appid']}</appid>
   <mch_id>{$data['mch_id']}</mch_id>
   <nonce_str>{$data['nonce_str']}</nonce_str> 
   <out_refund_no>{$data['out_refund_no']}</out_refund_no>
   <out_trade_no>{$data['out_trade_no']}</out_trade_no>
   <refund_id>{$data['refund_id']}</refund_id>
   <transaction_id>{$data['transaction_id']}</transaction_id>
   <sign>{$sign}</sign>
</xml>
XML;
        $url = 'https://api.mch.weixin.qq.com/pay/refundquery';

        $responseXml = $helper->curl_post_ssl($url, $postXml);

        if ($responseXml === false) {
            throw new \Exception("curl失败");
        }

        Log::debug($responseXml);

        libxml_disable_entity_loader(true);
        $responseObj = @simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($responseObj === false) {
            throw new \Exception("解析xml失败");
        }

        //SUCCESS/FAIL  SUCCESS退款申请接收成功，结果通过退款查询接口查询 FAIL 提交业务失败
        if ('SUCCESS' === (string)$responseObj->result_code) {
            return (array)$responseObj;
        }

        throw new \Exception($responseObj->err_code . ' ' . $responseObj->return_msg);

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