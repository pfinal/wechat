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
 *   Author: Yanlongli <jobs@yanlongli.com>
 *   Date:   2019/11/20
 *   IDE:    PhpStorm
 *   Desc:   _
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\receive;

use yanlongli\wechat\messaging\receive\general\Image;
use yanlongli\wechat\messaging\receive\general\Link;
use yanlongli\wechat\messaging\receive\general\Location;
use yanlongli\wechat\messaging\receive\general\ShortVideo;
use yanlongli\wechat\messaging\receive\general\Text;
use yanlongli\wechat\messaging\receive\general\Video;
use yanlongli\wechat\messaging\receive\general\Voice;
use yanlongli\wechat\WechatException;

/**
 * Class GeneralMessage
 * @package yanlongli\wechat\messaging\contract
 * @property int $MsgId 消息id
 */
class GeneralMessage extends ReceiveMessage
{
    #region
    protected static array $bind = [
        Image::TYPE => Image::class,
        Link::TYPE => Link::class,
        Location::TYPE => Location::class,
        ShortVideo::TYPE => ShortVideo::class,
        Text::TYPE => Text::class,
        Video::TYPE => Video::class,
        Voice::TYPE => Voice::class
    ];


    /**
     * @param string $MsgType
     * @return self
     * @throws WechatException
     */
    public static function build(string $MsgType)
    {
        if (isset(self::$bind))
            return new self::$bind[$MsgType];
        throw new WechatException("无法识别的消息类型");
    }
    #endregion

}