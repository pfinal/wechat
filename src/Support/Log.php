<?php

namespace PFinal\Wechat\Support;

use PFinal\Wechat\Kernel;

class Log
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected static $logger = null;

    private static function init()
    {
        if (static::$logger === null) {
            $config = Kernel::getConfig('log', array('class' => 'PFinal\Wechat\Support\Logger'));
            $class = $config['class'];
            unset($config['class']);
            static::$logger = new $class($config);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        self::init();
        return call_user_func_array(array(self::$logger, $name), $arguments);
    }
}
