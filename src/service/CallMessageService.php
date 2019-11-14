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
 *   Author: Yanlongli <jobs@yanlongli.com>、Zou Yiliang<>
 *   Date:   2019/11/13
 *   IDE:    PhpStorm
 *   Desc:
 */
declare(strict_types=1);

namespace yanlongli\wechat\service;

use yanlongli\wechat\App;
use yanlongli\wechat\messaging\contract\CallMessage;
use yanlongli\wechat\messaging\contract\MassMessage;
use yanlongli\wechat\WechatException;

/**
 * Trait CallMessageService
 * @package yanlongli\wechat\service
 */
class CallMessageService extends BaseService
{

    /**
     * 客服消息接口，主动给粉丝发消息。当用户和公众号产生特定动作的交互的48小时内有效。
     * @param App $app
     * @param string $openid
     * @param CallMessage $message
     * @param null $account 客服帐号(显示客服自定义头像)
     * @return array
     * @throws WechatException
     */
    public static function send(App $app, string $openid, CallMessage $message, $account = null)
    {
        $data = $message->jsonData();
        $type = $message->type();

        $message = array(
            'touser' => $openid,
            'msgtype' => $type,
        );

        $data = array_merge($message, $data);

        if ($account != null) {
            $data['customservice'] = array('kf_account' => $account);
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=ACCESS_TOKEN';

        return parent::request($url, $app, $data);
    }

    /**
     * 高级群发 根据分组进行群发 【订阅号与服务号认证后均可用】
     * 对于认证订阅号，群发接口每天可成功调用1次，此次群发可选择发送给全部用户或某个分组；
     * 对于认证服务号虽然开发者使用高级群发接口的每日调用限制为100次，但是用户每月只能接收4条，无论在公众平台网站上，
     * 还是使用接口群发，用户每月只能接收4条群发消息，多于4条的群发将对该用户发送失败；
     * 具备微信支付权限的公众号，在使用群发接口上传、群发图文消息类型时，可使用<a>标签加入外链；
     * 可以使用预览接口校对消息样式和排版，通过预览接口可发送编辑好的消息给指定用户校验效果。
     *
     * 关于群发时使用is_to_all为true使其进入公众号在微信客户端的历史消息列表：
     * 1、使用is_to_all为true且成功群发，会使得此次群发进入历史消息列表。2、为防止异常，认证订阅号在一天内，只能使用is_to_all为true进行群发一次，或者在公众平台官网群发（不管本次群发是对全体还是对某个分组）一次。以避免一天内有2条群发进入历史消息列表。
     * 3、类似地，服务号在一个月内，使用is_to_all为true群发的次数，加上公众平台官网群发（不管本次群发是对全体还是对某个分组）的次数，最多只能是4次。
     * 4、设置is_to_all为false时是可以多次群发的，但每个用户只会收到最多4条，且这些群发不会进入历史消息列表。
     *
     * @param App $app
     * @param string $tagId 用户管理中用户分组接口 $is_to_all为true时，此参数传入null即可
     * @param MassMessage $message
     * @param bool $isToAll 选择true该消息群发给所有用户
     * @return array
     * @throws WechatException
     */
    public static function sendAll(App $app, $tagId, MassMessage $message, bool $isToAll = false)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=ACCESS_TOKEN';
        $data = array(
            'filter' => array('is_to_all' => (bool)$isToAll, 'tag_id' => (string)$tagId),
        );
        if ($isToAll) {
            unset($data['filter']['tag_id']);
        }

        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return BaseService::request($url, $app, $data);
    }

    /**
     * 根据OpenID列表群发
     * @param App $app
     * @param array $openIds
     * @param MassMessage $message
     * @return array
     * @throws WechatException
     */
    public static function sendAllWithOpenIds(App $app, array $openIds, MassMessage $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=ACCESS_TOKEN';
        $data = array(
            'touser' => $openIds,
        );
        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return BaseService::request($url, $app, $data);
    }

    /**
     * 删除群发
     * @param App $app
     * @param $msgId
     * @return array
     * @throws WechatException
     */
    public static function delete(App $app, $msgId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/delete?access_token=ACCESS_TOKEN';
        return BaseService::request($url, $app, array('msg_id' => $msgId));
    }

    /**
     * 预览(发给某个openid)
     * @param App $app
     * @param $openId
     * @param MassMessage $message
     * @return array
     * @throws WechatException
     */
    public static function previewWithOpenId(App $app, $openId, MassMessage $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=ACCESS_TOKEN';

        $data = array(
            'touser' => $openId,
        );

        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return BaseService::request($url, $app, $data);
    }

    /**
     * 预览(发给某个微信号)
     * @param App $app
     * @param $wxname
     * @param MassMessage $message
     * @return array
     * @throws WechatException
     */
    public static function previewWithWxname(App $app, $wxname, MassMessage $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=ACCESS_TOKEN';
        $data = array(
            'towxname' => $wxname,
        );

        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return BaseService::request($url, $app, $data);
    }

    /**
     * 查询群发消息发送状态
     * @param App $app
     * @param $msgId
     * @return array
     * @throws WechatException
     */
    public static function status(App $app, $msgId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/get?access_token=ACCESS_TOKEN';
        return BaseService::request($url, $app, array('msg_id' => $msgId));
    }

}