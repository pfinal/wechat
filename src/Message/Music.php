<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\SendMessage;
use PFinal\Wechat\Contract\ReplyMessage;

class Music implements ReplyMessage, SendMessage
{
    protected $type = 'music';
    protected $attributes;

    public function __construct($thumbMediaId, $title = '', $description = '', $musicUrl = '', $hqMusicUrl = '')
    {
        if (is_array($thumbMediaId)) {
            extract($thumbMediaId, EXTR_OVERWRITE);
        }

        $this->attributes = array(
            'Music' => array(
                'Title' => $title,
                'Description' => $description,
                'MusicUrl' => $musicUrl,
                'HQMusicUrl' => $hqMusicUrl,
                'ThumbMediaId' => $thumbMediaId,
            )
        );
    }

    /**
     * @return array
     */
    public function xmlData()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonData()
    {
        $music = array();

        //将key转为小写，微信json格式为全小写
        foreach ($this->attributes['Music'] as $k => $v) {
            $music[$k] = array_change_key_case($v, CASE_LOWER);
        }

        return array('music' => $music);
    }
}