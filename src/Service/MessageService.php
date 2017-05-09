<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Contract\MassMessage;
use PFinal\Wechat\Contract\SendMessage;
use PFinal\Wechat\WechatException;

class MessageService extends BaseService
{
    /**
     * 客服消息接口，主动给粉丝发消息。当用户和公众号产生特定动作的交互的48小时内有效。
     * @param $openid
     * @param $message
     * @param null $account 客服帐号(显示客服自定义头像)
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function send($openid, SendMessage $message, $account = null)
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

        return parent::request($url, $data);
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
     * @param string $tagId 用户管理中用户分组接口 $is_to_all为true时，此参数传入null即可
     * @param string $mediaId 媒体id,如果发送文字，则此参数为需要发送的字符串
     * @param $type
     *      text
     *      mpnews 图文消息
     *      voice
     *      image
     *      wxcard
     *      mpvideo 视频 此处视频的media_id需通过 self::convertMediaIdForSendAll() 方法转换得到
     *
     * @param bool $isToAll 选择true该消息群发给所有用户
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function sendAll($tagId, MassMessage $message, $isToAll = false)
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

        return parent::request($url, $data);
    }

    /**
     * 根据OpenID列表群发
     * @param array $openids OpenID最少2个，最多10000个
     * @param MassMessage $message
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function sendAllWithOpenids(array $openids, MassMessage $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=ACCESS_TOKEN';
        $data = array(
            'touser' => $openids,
        );
        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return parent::request($url, $data);
    }

    /**
     * 删除群发
     * @param $msgId
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function delete($msgId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/delete?access_token=ACCESS_TOKEN';
        return parent::request($url, array('msg_id' => $msgId));
    }

    /**
     * 预览(发给某个openid)
     * @param $openid
     * @param MassMessage $message
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function previewWithOpenid($openid, MassMessage $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=ACCESS_TOKEN';

        $data = array(
            'touser' => $openid,
        );

        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return parent::request($url, $data);
    }

    /**
     * 预览(发给某个微信号)
     * @param $wxname
     * @param MassMessage $message
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function previewWithWxname($wxname, MassMessage $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=ACCESS_TOKEN';
        $data = array(
            'towxname' => $wxname,
        );

        $data = array_merge($data, $message->jsonData());
        $data['msgtype'] = $message->type();

        return parent::request($url, $data);
    }

    /**
     * 查询群发消息发送状态
     * @param $msgId
     * @return array
     * @throws WechatException
     * @throws \Exception
     */
    public static function status($msgId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/get?access_token=ACCESS_TOKEN';
        return parent::request($url, array('msg_id' => $msgId));
    }

    /**
     * 发送公众号模板消息[小程序请用templateWxapp()方法]
     *
     * @param string $openid
     * @param string $templateId 模板ID
     * @param array $data 详细内容
     *
     * 比如：保养过期通知 详细内容如下：
     * {{first.DATA}}
     *    保养到期时间：{{keynote1.DATA}}
     *    上次保养时间：{{keynote2.DATA}}
     *    上次保养里程：{{keynote3.DATA}}
     * {{remark.DATA}}
     *
     * 对应data数据为:
     * $data = array(
     *    'first' => '尊敬的车主，您的爱车保养以过期'
     *    'keynote1'=> '2014年12月12日',
     *    'keynote2'=> '2013年12月12日',
     *    'keynote3'=> '555KM',
     *    'remark'=> '点击保养，惊喜不断！',
     * );
     *
     * 如果需要指定每项颜色:
     * $data = array(
     *    'first' => array(
     *         'value' => '尊敬的车主，您的爱车保养以过期'
     *         'color' => '#FC5C48'
     *      ),
     *    'keynote1' => array(
     *         'value' => '2014年12月12日'
     *         'color' => '#173177'
     *      ),
     *    'keynote2' => array(
     *         'value' => '2014年12月12日'
     *         'color' => '#173177'
     *      ),
     *    'keynote3' => array(
     *         'value' => '2013年12月12日'
     *         'color' => '#173177'
     *      ),
     *    'remark' => array(
     *         'value' => '点击保养，惊喜不断！'
     *         'color' => '#173177'
     *      ),
     * );
     *
     * @param string $url
     * @param string $topColor
     * @param string $defaultItemColor
     * @return array
     * array(
     *      'errcode' => int 0
     *      'errmsg' => string 'ok' (length=2)
     *      'msgid' => int 413100638
     * )
     */
    public static function template($openid, $templateId, array $data, $url = '', $topColor = '#FF0000', $defaultItemColor = '#173177')
    {
        $apiUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=ACCESS_TOKEN';

        foreach ($data as $key => $val) {
            if (!is_array($val)) {
                $data[$key] = array(
                    'value' => "$val",
                    'color' => "$defaultItemColor",
                );
            }
        }

        $postData = array(
            'touser' => $openid,
            'template_id' => $templateId,
            'url' => $url,
            'topcolor' => $topColor,
            'data' => $data,
        );

        return parent::request($apiUrl, $postData);
    }

    /**
     * 小程序模板消息 [公众号请用template()方法]
     * @param $openid
     * @param $templateId
     * @param $formId
     * @param array $data
     * @param string $page
     * @param string $defaultItemColor
     * @return array
     */
    public static function templateWxapp($openid, $templateId, $formId, array $data, $page = '', $defaultItemColor = '#173177')
    {
        $apiUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=ACCESS_TOKEN';

        foreach ($data as $key => $val) {
            if (!is_array($val)) {
                $data[$key] = array(
                    'value' => "$val",
                    'color' => "$defaultItemColor",
                );
            }
        }

        $postData = array(
            'touser' => $openid,
            'template_id' => $templateId,
            'page' => $page,
            'form_id' => $formId,
            'data' => $data,
        );

        return parent::request($apiUrl, $postData);
    }

}