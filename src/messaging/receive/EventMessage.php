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
 *   Date:   2019/11/14
 *   IDE:    PhpStorm
 *   Desc:
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\receive;

use yanlongli\wechat\messaging\receive\event\Click;
use yanlongli\wechat\messaging\receive\event\Location;
use yanlongli\wechat\messaging\receive\event\QRScene;
use yanlongli\wechat\messaging\receive\event\Scan;
use yanlongli\wechat\messaging\receive\event\Subscribe;
use yanlongli\wechat\messaging\receive\event\Unsubscribe;
use yanlongli\wechat\messaging\receive\event\View;
use yanlongli\wechat\WechatException;

/**
 * Interface EventMessage
 * @package yanlongli\wechat\messaging\contract
 * @property string $FromUserName 发送方帐号(OpenID)
 * @property string $ToUserName 公众号原始id
 * @property string $CreateTime 消息创建时间(整型)
 * @property string $MsgType 消息类型
 * @property string $Event 事件类型
 */
class EventMessage extends ReceiveMessage
{
    const EVENT = '';
    const TYPE = 'event';


    #region

    protected static array $bind = [
        Click::EVENT => Click::class,
        Location::EVENT => Location::class,
//        QRScene::EVENT => QRScene::class, qrscene和订阅捆绑在一起，需要额外处理
        Scan::EVENT => Scan::class,
        Subscribe::EVENT => Subscribe::class,
        Unsubscribe::EVENT => Subscribe::class,
        View::EVENT => View::class
    ];

    /**
     * @param string $Event
     * @param string $EventKey
     * @return mixed
     * @throws WechatException
     */
    public static function build(string $Event, string $EventKey)
    {
        if (isset(self::$bind)) {
            if (QRScene::EVENT === $EventKey) {
                return new QRScene();
            }
            return new self::$bind[$Event];
        }
        throw new WechatException("无法识别的消息类型");
    }
    #endregion
}