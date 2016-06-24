<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\MassMessage;

/**
 * 图文消息
 */
class MpNews implements MassMessage
{
    protected $type = 'mpnews';
    protected $mediaId;

    public function __construct($mediaId)
    {
        $this->mediaId = $mediaId;
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
            'mpnews' => array(
                'media_id' => $this->mediaId,
            ),
        );
    }
}