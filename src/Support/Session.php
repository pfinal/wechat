<?php

namespace PFinal\Wechat\Support;

use PFinal\Wechat\Kernel;

class Session
{
    /**
     * @var \PFinal\Session\SessionInterface
     */
    protected static $session;

    protected static function init()
    {
        if (static::$session === null) {
            $config = Kernel::getConfig('session', array('class' => 'PFinal\Session\NativeSession', 'keyPrefix' => 'pfinal.wechat'));
            $class = $config['class'];
            unset($config['class']);
            static::$session = new $class($config);
        }
    }

    public static function  __callStatic($name, $arguments)
    {
        static::init();

        if (method_exists(static::$session, $name)) {
            return call_user_func_array([static::$session, $name], $arguments);
        }

        throw new \Exception('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }
}