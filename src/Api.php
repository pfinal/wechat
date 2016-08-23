<?php

namespace PFinal\Wechat;

use PFinal\Wechat\Message\News;
use PFinal\Wechat\Message\Receive;
use PFinal\Wechat\Message\Text;
use PFinal\Wechat\SDK\WXBizMsgCrypt;
use PFinal\Wechat\Support\Json;
use PFinal\Wechat\Contract\ReplyMessage;
use PFinal\Wechat\Support\Xml;
use PFinal\Wechat\Support\Cache;
use PFinal\Wechat\Support\Curl;
use PFinal\Wechat\Support\Log;

/**
 * 微信公众平台API
 * https://mp.weixin.qq.com/wiki
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class Api
{
    //加密类型
    const ENCRYPT_TYPE_RAW = 'raw';
    const ENCRYPT_TYPE_AES = 'aes';

    protected $appId;
    protected $appSecret;
    protected $token;
    protected $encodingAesKey;
    protected $encodingAesKeyLast;
    protected $middleUrl;

    protected $accessToken;

    //当次请求的加密方式，对应ENCRYPT_TYPE_XX常量
    private $encryptType;

    //当次请求所使用的encodingAesKey ($encodingAesKey或$encodingAesKeyLast其中一个)
    private $currentEncodingAesKey;

    /**
     * 构造方法 可以使用一个数组作为参数
     * @param array|string $appId
     * @param string|null $appSecret
     * @param string|null $token
     * @param string|null $encodingAesKey
     * @param string|null $encodingAesKeyLast
     */
    public function __construct($appId, $appSecret = null, $token = null, $encodingAesKey = null, $encodingAesKeyLast = null, $middleUrl = null)
    {
        if (is_array($appId)) {
            extract($appId, EXTR_OVERWRITE);
        }

        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->encodingAesKeyLast = $encodingAesKeyLast;
        $this->middleUrl = $middleUrl;
    }

    /**
     * 返回appId
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * 返回appSecret
     * @return string
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return string|null
     */
    public function getMiddleUrl()
    {
        return $this->middleUrl;
    }

    /**
     * 验证请求是否来自微信公共平台
     * @return bool
     */
    public function checkSignature()
    {
        $signature = isset($_GET['signature']) ? $_GET['signature'] : '';
        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

        //验证token
        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        return $tmpStr === $signature;
    }

    /**
     * 返回消息对象，字段大小写与开放平台文档一致
     * @return Receive
     * @throws WechatException
     */
    public function getMessage()
    {
        static $message = null;

        if ($message == null) {
            //微信提交过来的xml
            $xmlStr = file_get_contents('php://input');
            if (empty($xmlStr)) {
                throw new WechatException('http raw post data is empty.');
            }

            //自动识别是否需要解密
            $xmlStr = $this->attemptDecrypt($xmlStr);

            $xmlElement = @simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
            if ($xmlElement === false) {
                throw new WechatException('load xml error. ' . $xmlStr);
            }

            //将SimpleXMLElement对象转为Receive对象
            $message = new Receive();
            foreach ((array)$xmlElement as $key => $value) {
                $message->$key = $value;
            }
        }

        return $message;
    }

    /**
     * 尝试解密数据，从$_GET['encrypt_type']中获取加密类型，如果未加密，原样返回
     * @param string $message
     * @return string
     * @throws WechatException
     * @throws \Exception
     */
    protected function attemptDecrypt($message)
    {
        //加密类型
        if ($this->encryptType === null) {
            $this->encryptType = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] === 'aes')) ? static::ENCRYPT_TYPE_AES : static::ENCRYPT_TYPE_RAW;
        }

        //未加密,原样返回
        if ($this->encryptType == static::ENCRYPT_TYPE_RAW) {
            return $message;
        }

        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $msgSignature = isset($_GET['msg_signature']) ? $_GET['msg_signature'] : '';

        //解密
        if ($this->encryptType == static::ENCRYPT_TYPE_AES) {

            //记录当次解密使用的AesKey
            $this->currentEncodingAesKey = $this->encodingAesKey;

            try {

                return $this->decryptMsg($msgSignature, $timestamp, $nonce, $message);

            } catch (WechatException $ex) {

                //解密失败后尝试使用encodingAesKeyLast解密
                if ($this->encodingAesKeyLast !== null) {
                    $this->currentEncodingAesKey = $this->encodingAesKeyLast;
                    return $this->decryptMsg($msgSignature, $timestamp, $nonce, $message);
                }

                throw $ex;
            }
        }

        throw new WechatException('unknown encrypt type: ' . $this->encryptType);
    }

    /**
     * 对响应给微信服务器的消息进行加密，自动识别是否需要加密，本次请求未加密时，数据原样返回
     * @param string $message
     * @return string
     * @throws WechatException
     */
    protected function attemptEncrypt($message)
    {
        //请求未加密时，回复明文内容
        if ($this->encryptType == static::ENCRYPT_TYPE_RAW) {
            return $message;
        }

        //加密
        if ($this->encryptType == static::ENCRYPT_TYPE_AES) {
            $timestamp = time();
            $nonce = uniqid();
            return $this->encryptMsg($message, $timestamp, $nonce);
        }

        throw new WechatException('unknown encrypt type: ' . $this->encryptType);
    }

    /**
     * 解密
     * @param string $msgSignature
     * @param string $timestamp
     * @param string $nonce
     * @param string $encryptMsg
     * @return string
     * @throws WechatException
     */
    protected function decryptMsg($msgSignature, $timestamp, $nonce, $encryptMsg)
    {
        $msg = '';

        //传入公众号第三方平台的token（申请公众号第三方平台时填写的接收消息的校验token）, 公众号第三方平台的appid, 公众号第三方平台的 EncodingAESKey（申请公众号第三方平台时填写的接收消息的加密symmetric_key）
        $pc = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);

        // 第三方收到公众号平台发送的消息
        $errCode = $pc->decryptMsg($msgSignature, $timestamp, $nonce, $encryptMsg, $msg);

        if ($errCode == 0) {

            Log::debug((string)$msg);

            return $msg;
        }

        throw new WechatException('decrypt msg error. error code ' . $errCode);
    }

    /**
     * 解密
     * @param $replyMsg
     * @param $timestamp
     * @param $nonce
     * @return string
     * @throws WechatException
     */
    protected function encryptMsg($replyMsg, $timestamp, $nonce)
    {
        $msg = '';

        //传入公众号第三方平台的token（申请公众号第三方平台时填写的接收消息的校验token）, 公众号第三方平台的appid, 公众号第三方平台的 EncodingAESKey（申请公众号第三方平台时填写的接收消息的加密symmetric_key）
        $pc = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);

        //第三方收到公众号平台发送的消息
        $errCode = $pc->encryptMsg($replyMsg, $timestamp, $nonce, $msg);

        if ($errCode == 0) {
            return $msg;
        }
        throw new WechatException('encrypt msg error. error code ' . $errCode);
    }

    /**
     * 构造响应xml字符串，用于微信服务器请求时被动响应
     * @param mixed $reply 被动响应的内容，ReplyMessage对象，News数组 或string
     * @return string
     * @throws WechatException
     */
    public function buildReply($reply)
    {
        //回复的消息为null，会返回给微信一个"success"，微信服务器不会对此作任何处理，并且不会发起重试
        if (is_null($reply)) {
            return 'success';
        }

        if (is_string($reply) || is_numeric($reply)) {
            $reply = new Text("$reply");
        }

        //索引数组，多图文消息
        if (is_array($reply) && array_keys($reply) === range(0, count($reply) - 1)) {
            if ($reply[0] instanceof News) {
                $reply = new News($reply);
            }
        }

        if (!$reply instanceof ReplyMessage) {
            throw new WechatException('Argument $reply must implement interface PFinal\Wechat\Contract\ReplyMessage');
        }

        //手动构建好的xml字符串
        if (strtolower($reply->type()) === 'raw') {
            return $this->attemptEncrypt($reply->xmlData());
        }

        $data = array(
            'ToUserName' => $this->getMessage()->FromUserName,
            'FromUserName' => $this->getMessage()->ToUserName,
            'CreateTime' => time(),
            'MsgType' => $reply->type(),
        );

        $data = array_merge($data, $reply->xmlData());

        $data = Xml::build($data);

        return $this->attemptEncrypt($data);

    }

    /**
     * 设置AccessToken
     * @param string $accessToken
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * 获取公众号的全局唯一票据accessToken，公众号主动调用各接口时都需使用accessToken
     * accessToken默认有效时间为7200秒，每天调用次数有限制，认证服务号每天最多100000次
     *
     * @param bool $useCache 是否使用缓存
     * @return string|null
     */
    public function getAccessToken($useCache = true)
    {
        //缓存key
        $cacheKey = md5(__FILE__ . __METHOD__ . $this->appId);

        //检查是否启用缓存
        if ($useCache) {

            if (empty($this->accessToken)) {
                $this->accessToken = Cache::get($cacheKey);
            }

            if (!empty($this->accessToken)) {
                return $this->accessToken;
            }
        } else {

            $this->accessToken = null;
            Cache::delete($cacheKey);
        }

        //获取accessToken
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
        $response = Curl::get(sprintf($url, $this->appId, $this->appSecret));

        //返回格式 {"access_token":"ACCESS_TOKEN","expires_in":7200}
        //{"access_token":"43BKbLBjBnwH600H5TMSlx6AKT9TCZ3BRXjxvT0erRpzHTIaUuaJBDUoUykTqA","expires_in":7200}
        //每日调用超过次数，将提示
        //{"errcode":45009,"errmsg":"reach max api daily quota limit hint: [PuguNA0618vr22]"}

        $arr = Json::parseOrFail($response);

        $this->accessToken = $arr['access_token'];

        //默认时间是7200秒(120分钟)
        $expires = $arr['expires_in'] - 1200;
        if ($expires > 0) {
            Cache::set($cacheKey, $this->accessToken, $expires);
        }
        return $this->accessToken;
    }
}