<?php

namespace PFinal\Wechat\Support;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var \Monolog\Logger
     */
    private $logger = null;

    /**
     * @var string The logging channel
     */
    private $name = 'pfinal.logger';
    /**
     * @var string 日志文件
     */
    private $file;

    /**
     * @var int 日志等级 对应常量: \Monolog\Logger::WARNING 、\Monolog\Logger::ERROR 等
     */
    private $level = \Monolog\Logger::DEBUG;

    public function __construct(array $config = array())
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        $this->init();
    }

    public function init()
    {
        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            return;
        }

        $logger = new \Monolog\Logger($this->name);

        if (defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } else if (empty($this->file)) {
            $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $this->level));
        } else {
            $logger->pushHandler(new StreamHandler($this->file, $this->level));
        }
        $this->logger = $logger;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->logger, $name), $arguments);
    }
}