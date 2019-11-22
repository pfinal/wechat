<?php
/**
 *   Copyright (c) [2019] [Yanlongli <jobs@yanlongli.com>]
 *   [Wechat] is licensed under the Mulan PSL v1.
 *   You can use this software according to the terms and conditions of the Mulan PSL v1.
 *   You may obtain a copy of Mulan PSL v1 at:
 *       http://license.coscl.org.cn/MulanPSL
 *   THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT, MERCHANTABILITY OR FIT FOR A PARTICULAR
 *   PURPOSE.
 *   See the Mulan PSL v1 for more details.
 *
 *   Author: Yanlongli <jobs@yanlongli.com>
 *   Date:   2019/11/20
 *   IDE:    PhpStorm
 *   Desc:   服务抽象类
 *      注意，小程序服务收到的时json格式数据，不支持xml，公众号收到的时xml格式不支持json
 */
declare(strict_types=1);

namespace yanlongli\wechat;

use yanlongli\wechat\support\Request;
use yanlongli\wechat\messaging\receive\EventMessage;
use yanlongli\wechat\messaging\receive\GeneralMessage;
use yanlongli\wechat\messaging\contract\ReplyMessage;
use yanlongli\wechat\messaging\receive\ReceiveMessage;
use yanlongli\wechat\support\Xml;
use yanlongli\wechat\sdk\WXBizMsgCrypt;
use yanlongli\wechat\officialAccount\OfficialAccount;
use yanlongli\wechat\support\Json;
use yanlongli\wechat\miniProgram\MiniProgram;
use yanlongli\wechat\messaging\message\NoReply;

/**
 * Class Service
 * @package yanlongli\wechat
 */
abstract class Service
{
    //加密类型
    const ENCRYPT_TYPE_RAW = 'raw';
    const ENCRYPT_TYPE_AES = 'aes';


    /**
     * @var array $handles 事件集合
     */
    protected $handles = [];

    protected $stopPropagation = false;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var ReceiveMessage
     */
    protected $receiveMessage;

    //当次请求的加密方式，对应ENCRYPT_TYPE_XX常量
    /**
     * @var string self::ENCRYPT_TYPE_RAW|self::ENCRYPT_TYPE_AES
     */
    protected $encryptType;

    /**
     * Service constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 验证签名验证
     * @return bool
     */
    protected function checkSignature(): bool
    {
        $tmpArr = array($this->app->accessToken, Request::param('timestamp'), Request::param('nonce'));
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        return Request::param('signature') === $tmpStr;
    }

    /**
     * 处理首次配置
     * @return bool
     */
    protected function handleFirstOption(): bool
    {
        //先处理签名验证
        if (Request::has('echostr') && Request::has('nonce')) {
            return $this->checkSignature();
        }
        return true;
    }

    /**
     * 注册事件处理函数
     * @param string $event
     * @param  $function
     * @see 没有优先级控制，请按照先后顺序进行注册
     */
    public function register(string $event, $function): void
    {
        $this->handles[$event][] = $function;
    }

    /**
     * 处理动作
     * @throws WechatException
     * @see 该方法会终止继续执行 die()
     */
    public function handle(): void
    {
        //处理首次请求验证
        if (!$this->handleFirstOption()) {
            die('signature error');
        }

        //如果识别出是加密才会进行解析等操作
        if ($this->attemptIsEncrypt()) {
            // 这里不一定就是xml传输 还可能是json传输
            $xmlStr = $this->attemptDecrypt(file_get_contents('php://input'));
            Request::setParams(Request::xmlToArray($xmlStr));
        }
        $MsgType = Request::param('MsgType/s');

        if (EventMessage::TYPE === $MsgType) {
            $this->receiveMessage = EventMessage::build(Request::param('Event/s'), Request::param('EventKey'));
        } else {
            $this->receiveMessage = GeneralMessage::build($MsgType);
        }
        //赋值处理
        $this->receiveMessage->setAttr(Request::param());

        //处理事件消息
        if (isset($this->handles[$MsgType])) {
            foreach ($this->handles[$MsgType] as $key => $handle) {

                if (!$this->receiveMessage->isPropagationStopped()) {
                    call_user_func($handle, $this->receiveMessage);
                }
            }
        } else {
            $this->receiveMessage->sendMessage(new NoReply());
        }
        echo $this->buildReply();
        //结束
    }

    /**
     * 构造响应xml字符串，用于微信服务器请求时被动响应
     * @return string
     * @throws WechatException
     */
    public function buildReply()
    {
        //回复的消息为NoReply，会返回给微信一个"success"，微信服务器不会对此作任何处理，并且不会发起重试
        if ($this->receiveMessage->getReplyMessage() instanceof NoReply) {
            return 'success';
        }

        if (!$this->receiveMessage->getReplyMessage() instanceof ReplyMessage) {
            throw new WechatException('Argument ReplyMessage must implement interface ReplyMessage');
        }


        $data = array(
            'ToUserName' => $this->receiveMessage->FromUserName,
            'FromUserName' => $this->receiveMessage->ToUserName,
            'CreateTime' => time(),
            'MsgType' => $this->receiveMessage->getReplyMessage()->type(),
        );

        $data = array_merge($data, $this->receiveMessage->getReplyMessage()->xmlData());

        if ($this->app instanceof OfficialAccount) {

            $data = Xml::build($data);
        } else if ($this->app instanceof MiniProgram) {
            $data = Json::encode($data);
        }

        return $this->attemptEncrypt($data);

    }

    protected function attemptIsEncrypt()
    {
        //加密类型
        if (null === $this->encryptType) {
            $this->encryptType = (isset($_GET['encrypt_type']) && (static::ENCRYPT_TYPE_AES === $_GET['encrypt_type'])) ? static::ENCRYPT_TYPE_AES : static::ENCRYPT_TYPE_RAW;
        }
        return static::ENCRYPT_TYPE_AES === $this->encryptType;
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
        if (null === $this->encryptType) {
            $this->encryptType = (isset($_GET['encrypt_type']) && (static::ENCRYPT_TYPE_AES === $_GET['encrypt_type'])) ? static::ENCRYPT_TYPE_AES : static::ENCRYPT_TYPE_RAW;
        }

        //未加密,原样返回
        if (static::ENCRYPT_TYPE_RAW === $this->encryptType) {
            return $message;
        }

        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $msgSignature = isset($_GET['msg_signature']) ? $_GET['msg_signature'] : '';

        //解密
        if (static::ENCRYPT_TYPE_AES === $this->encryptType) {
            return $this->decryptMsg($msgSignature, $timestamp, $nonce, $message);
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
        $pc = new WXBizMsgCrypt($this->app->token, $this->app->encodingAesKey, $this->app->appId);

        // 第三方收到公众号平台发送的消息
        $errCode = $pc->decryptMsg($msgSignature, $timestamp, $nonce, $encryptMsg, $msg);

        if (0 === $errCode) {

            return $msg;
        }

        throw new WechatException('decrypt msg error. error code ' . $errCode);
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
        if (static::ENCRYPT_TYPE_RAW === $this->encryptType) {
            return $message;
        }

        //加密
        if (static::ENCRYPT_TYPE_AES === $this->encryptType) {
            $timestamp = time();
            $nonce = uniqid();
            return $this->encryptMsg($message, $timestamp, $nonce);
        }

        throw new WechatException('unknown encrypt type: ' . $this->encryptType);
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
        $pc = new WXBizMsgCrypt($this->app->token, $this->app->encodingAesKey, $this->app->appId);

        //第三方收到公众号平台发送的消息
        $errCode = $pc->encryptMsg($replyMsg, $timestamp, $nonce, $msg);

        if (0 === $errCode) {
            return $msg;
        }
        throw new WechatException('encrypt msg error. error code ' . $errCode);
    }
}