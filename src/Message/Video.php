<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\SendMessage;
use PFinal\Wechat\Contract\ReplyMessage;

/**
 * 视频
 */
class Video implements ReplyMessage, SendMessage
{
    protected $type = 'video';
    protected $attributes;

    public function __construct($mediaId, $thumbMediaId = null, $title = null, $description = null)
    {
        if (!is_array($mediaId)) {
            $mediaId = compact(array('mediaId', 'thumbMediaId', 'title', 'description'));
        }

        $this->attributes = $mediaId;
    }

    /**
     * @return array
     */
    public function xmlData()
    {
        return array(
            'Video' => array(
                'MediaId' => $this->attributes['mediaId'],
                'Title' => $this->attributes['title'],
                'Description' => $this->attributes['description'],
            ));
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
        return array(
            'video' => array(
                'media_id' => $this->attributes['mediaId'],
                'thumb_media_id' => $this->attributes['thumbMediaId'],
                'title' => $this->attributes['title'],
                'description' => $this->attributes['description'],
            ),
        );
    }
}