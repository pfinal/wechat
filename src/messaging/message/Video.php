<?php
/**
 *   Copyright (c) [2019] [Yanlongli <jobs@yanlongli.com>]
 *   [Wechat] is licensed under the Mulan PSL v1.
 *   You can use this software according to the terms and conditions of the Mulan PSL v1.
 *   You may obtain a copy of Mulan PSL v1 at:
 *       http://license.coscl.org.cn/MulanPSL
 *   THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT, MERCHANTABILITY OR FIT FOR A PARTICULAR
 *   PURPOSE.
 *   See the Mulan PSL v1 for more details.
 *
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\message;

use yanlongli\wechat\messaging\contract\CallMessage;
use yanlongli\wechat\messaging\contract\ReplyMessage;

/**
 * 视频
 */
class Video implements ReplyMessage, CallMessage
{
    protected string $type = 'video';
    protected array $attributes;

    /**
     * Video constructor.
     * @param array|string $mediaId
     * @param string $thumbMediaId
     * @param string $title
     * @param string $description
     */
    public function __construct($mediaId, string $thumbMediaId = null, string $title = null, string $description = null)
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