<?php

namespace PFinal\Wechat\Support;

use PFinal\Wechat\Kernel;

class Cache
{
    /**
     * @var \PFinal\Cache\CacheInterface
     */
    protected static $cache = null;

    public static function init()
    {
        if (empty(self::$cache)) {
            $config = Kernel::getConfig('cache', array('class' => 'PFinal\Cache\FileCache', 'keyPrefix' => 'pfinal.wechat'));
            $class = $config['class'];
            unset($config['class']);
            self::$cache = new $class($config);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        self::init();

        if (method_exists(self::$cache, $name)) {
            return call_user_func_array([self::$cache, $name], $arguments);
        }

        throw new \Exception('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }
}