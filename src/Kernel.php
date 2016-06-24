<?php

namespace PFinal\Wechat;

use PFinal\Wechat\Support\Log;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Kernel
{
    /**
     * @var EventDispatcher
     */
    protected static $dispatcher = null;
    /**
     * @var Api
     */
    protected static $api = null;

    /**
     * @var \PFinal\Wechat\SDK\Redpack\Helper
     */
    protected static $redpackHelper = null;

    private static $config = array();

    public static function init($config)
    {
        static::$config = $config;
    }

    public static function getConfig($name, $defaultValue = null)
    {
        if (array_key_exists($name, static::$config)) {
            return static::$config[$name];
        }
        return $defaultValue;
    }

    /**
     * @return Api
     */
    public static function getApi()
    {
        if (static::$api == null) {
            static::$api = new \PFinal\Wechat\Api(self::$config);
        }
        return static::$api;
    }

    /**
     * @return \PFinal\Wechat\SDK\Redpack\Helper
     */
    public static function getRedpackHelper()
    {
        if (static::$redpackHelper == null) {
            static::$redpackHelper = new \PFinal\Wechat\SDK\Redpack\Helper(self::$config);
        }

        return static::$redpackHelper;
    }

    /**
     * @param string $type 微信事件类型，对应 Message::TYPE_XXX 常量
     * @param callback $callback 处理函数，此函数接收一个WechatEvent对象
     * @param int $priority 值大者优先级高
     */
    public static function register($type, $callback, $priority = 0)
    {
        self::getDispatcher()->addListener($type, $callback, $priority);
    }

    /**
     * @return EventDispatcher
     */
    public static function getDispatcher()
    {
        if (self::$dispatcher == null) {
            self::$dispatcher = new EventDispatcher();
        }
        return self::$dispatcher;
    }

    /**
     * @return string
     */
    public static function handle()
    {
        $api = self::getApi();

        $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        //验证token
        if (!$api->checkSignature()) {
            Log::error('signature error. url:' . $url);
            return 'signature error';
        }

        //公众号接入(微信平台修改服务器配置)
        if (isset($_GET['echostr'])) {
            Log::debug('echostr. url:' . $url);
            self::getDispatcher()->dispatch('echostr', new WechatEvent($api));
            return $_GET['echostr'];
        }

        $message = $api->getMessage();

        //事件名称与Message类中的常量对应
        $eventName = $message->MsgType;
        if ($message->MsgType === 'event') {
            $eventName = $eventName . '.' . $message->Event;
        }

        //派发事件
        Log::debug('dispatch event:' . $eventName);
        $event = self::getDispatcher()->dispatch($eventName, new WechatEvent($api));

        $reply = $event->getResponse();

        return (string)$api->buildReply($reply);
    }
}
