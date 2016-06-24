<?php

namespace PFinal\Wechat\Message;
use PFinal\Wechat\Contract\Message;

/**
 * 接收微信平台推送的消息
 *
 * @property string $FromUserName 发送方帐号(OpenID)
 * @property string $ToUserName 公众号原始id
 * @property string $CreateTime 消息创建时间(整型)
 * @property string $MsgType 消息类型
 * @property string $MsgId 消息id，64位整型
 * @property string $Content 内容
 * @property string $PicUrl 图片链接
 * @property string $MediaId 图片消息媒体id
 * @property string $Format  语音格式
 * @property string $ThumbMediaId 视频消息缩略图的媒体id
 * @property string $Location_X 地理位置维度
 * @property string $Location_Y 地理位置维度
 * @property string $Scale 地图缩放大小
 * @property string $Label 地理位置信息
 * @property string $Title 消息标题
 * @property string $Description 消息描述
 * @property string $Url 消息链接
 * @property string $Event 事件类型，subscribe(订阅)、unsubscribe(取消订阅)等
 * @property string $EventKey 事件KEY值，qrscene_为前缀，后面为二维码的参数值
 * @property string $Ticket 二维码的ticket，可用来换取二维码图片
 * @property string $Latitude 地理位置纬度
 * @property string $Longitude 地理位置纬度
 * @property string $Precision 地理位置经度
 * @property string $Recognition 语音识别结果，UTF8编码
 *
 * 群发推送结果:
 * @property string $MsgID 注意大小写，不是MsgId(消息id)
 * @property string $Status
 * @property string $TotalCount
 * @property string $FilterCount
 * @property string $SentCount
 * @property string $ErrorCount
 */
class Receive implements Message
{
    //消息类型 对应MsgType字段
    const TYPE_TEXT = 'text';                 //文本消息
    const TYPE_VOICE = 'voice';               //语音
    const TYPE_IMAGE = 'image';               //图片
    const TYPE_LOCATION = 'location';         //位置消息(聊天窗口发送的位置)
    const TYPE_LINK = 'link';                 //链接
    const TYPE_VIDEO = 'video';               //视频
    const TYPE_SHORT_VIDEO = 'shortvideo';    //小视频

    //事件类型 当MsgType为event时,细分具体事件类型，对应Event字段

    const TYPE_EVENT_SUBSCRIBE = 'event.subscribe';                      //关注事件
    const TYPE_EVENT_UNSUBSCRIBE = 'event.unsubscribe';                  //关注事件
    const TYPE_EVENT_CLICK = 'event.CLICK';                              //点击菜单
    const TYPE_EVENT_VIEW = 'event.VIEW';                                //菜单跳转
    const TYPE_EVENT_SCAN_CODE_WAIT_MSG = 'event.scancode_waitmsg';      //扫码推事件且弹出“消息接收中”提示框的事件推送
    const TYPE_EVENT_PIC_SYSPHOTO = 'event.pic_sysphoto';                //弹出系统拍照发图的事件推送
    const TYPE_EVENT_PIC_PHOTO_OR_ALBUM = 'event.pic_photo_or_album';    //弹出拍照或者相册发图的事件推送
    const TYPE_EVENT_PIC_WEIXIN = 'event.pic_weixin';                    //弹出微信相册发图器的事件推送
    const TYPE_EVENT_LOCATION_SELECT = 'event.location_select';          //弹出地理位置选择器的事件推送

    const TYPE_EVENT_SCAN = 'event.SCAN';                                //扫描二唯码
    const TYPE_EVENT_LOCATION = 'event.LOCATION';                        //上报地理位置事件 同意后，每次进入公众号会话时，都会在上报地理位置

    const TYPE_EVENT_MASS_SEND_JOB_FINISH = 'event.MASSSENDJOBFINISH';   //群发消息结果

    const TYPE_EVENT_CARD_PASS_CHECK = 'event.card_pass_check';           //卡券通过审核
    const TYPE_EVENT_CARD_NOT_PASS_CHECK = 'event.card_not_pass_check';   //卡券审核不通过
    const TYPE_EVENT_USER_GET_CARD = 'event.user_get_card';               //用户在领取卡券
    const TYPE_EVENT_USER_DEL_CARD = 'event.user_del_card';               //用户在删除卡券
    const TYPE_EVENT_USER_VIEW_CARD = 'event.user_view_card';             //用户在进入会员卡

    /**
     * @return string
     */
    public function type()
    {
        return $this->MsgType;
    }
}