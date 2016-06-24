<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\MassMessage;
use PFinal\Wechat\Contract\ReplyMessage;
use PFinal\Wechat\Contract\SendMessage;

/**
 * 文本消息
 */
class Text implements ReplyMessage, SendMessage, MassMessage
{
    protected $type = 'text';
    protected $attributes;

    public function __construct($content)
    {
        $this->attributes = array('content' => $content);
    }

    public function type()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function xmlData()
    {
        return array(
            'Content' => $this->attributes['content'],
        );
    }

    /**
     * @return array
     */
    public function jsonData()
    {
        return array('text' => $this->attributes);
    }

}