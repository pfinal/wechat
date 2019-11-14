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

class Music implements ReplyMessage, CallMessage
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