<?php
declare(strict_types=1);

namespace yanlongli\wechat;

use Throwable;

class WechatException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $time = date(DATE_ATOM);
        file_put_contents('error.log', "[$time] [$code] : $message");
    }
}