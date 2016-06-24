<?php

namespace PFinal\Wechat;

use PFinal\Wechat\Message\Receive;
use Symfony\Component\EventDispatcher\Event;

/**
 * 微信消息推送事件
 */
class WechatEvent extends Event
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var string | null
     */
    protected $response = null;

    public function __construct($api)
    {

        $this->api = $api;
    }

    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return Receive
     */
    public function getMessage()
    {
        return $this->api->getMessage();
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return null|string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
