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

use yanlongli\wechat\officialAccount\OfficialAccount;
use yanlongli\wechat\WechatException;

class MenuService extends BaseService
{
    /**
     * 自定义菜单查询接口 仅能查询到使用API设置的菜单配置
     * http://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141014&token=&lang=zh_CN
     *
     * 在设置了个性化菜单后，使用本自定义菜单查询接口可以获取默认菜单和全部个性化菜单信息
     *
     * 类型为click、scancode_push、scancode_waitmsg、pic_sysphoto、pic_photo_or_album、 pic_weixin、location_select：保存值到key；
     * 类型为view：保存链接到url
     *
     * @param OfficialAccount $app
     * @return array 例如
     * array(
     *     'menu' => array(
     *         'button' => array(
     *             array('type' => 'click', 'name' => '今日歌曲', 'key' => 'V1001_TODAY_MUSIC', 'sub_button' => array()),
     *             array('type' => 'click', 'name' => '歌手简介', 'key' => 'V1001_TODAY_SINGER', 'sub_button' => array()),
     *             array('name' => '菜单', 'sub_button' => array(
     *                 array('type' => 'view', 'name' => '搜索', 'url' => 'http://www.soso.com/', 'sub_button' => array(),),
     *                 array('type' => 'view', 'name' => '视频', 'url' => 'http://v.qq.com/', 'sub_button' => array(),),
     *                 array('type' => 'click', 'name' => '赞一下我们', 'key' => 'V1001_GOOD', 'sub_button' => array(),),),
     *             )
     *         )
     *     )
     * )
     * @throws WechatException
     */
    public static function all(OfficialAccount $app)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=ACCESS_TOKEN';
        return parent::request($url, $app);
    }

    /**
     * 获取自定义菜单配置
     * http://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1434698695&token=&lang=zh_CN
     *
     * 本接口将会提供公众号当前使用的自定义菜单的配置，如果公众号是通过API调用设置的菜单，则返回菜单的开发配置，
     * 而如果公众号是在公众平台官网通过网站功能发布菜单，则本接口返回运营者设置的菜单配置
     * 1、第三方平台开发者可以通过本接口，在旗下公众号将业务授权给你后，立即通过本接口检测公众号的自定义菜单配置，并通过接口再次给公众号设置好自动回复规则，以提升公众号运营者的业务体验。
     * 2、本接口与自定义菜单查询接口的不同之处在于，本接口无论公众号的接口是如何设置的，都能查询到接口，而自定义菜单查询接口则仅能查询到使用API设置的菜单配置。
     * 3、认证/未认证的服务号/订阅号，以及接口测试号，均拥有该接口权限。
     * 4、从第三方平台的公众号登录授权机制上来说，该接口从属于消息与菜单权限集。
     * 5、本接口中返回的图片/语音/视频为临时素材（临时素材每次获取都不同，3天内有效，通过素材管理-获取临时素材接口来获取这些素材），本接口返回的图文消息为永久素材素材（通过素材管理-获取永久素材接口来获取这些素材）。
     *
     * 如果公众号是在公众平台官网通过网站功能发布菜单,type有可能为news、video、text、img
     *
     * @param OfficialAccount $app
     * @return array
     *
     * array(
     *     'is_menu_open'=>1
     *     'selfmenu_info' => array(
     *         'button' => array(
     *             array('type' => 'click', 'name' => '今日歌曲', 'key' => 'V1001_TODAY_MUSIC', 'sub_button' => array()),
     *             array('type' => 'click', 'name' => '歌手简介', 'key' => 'V1001_TODAY_SINGER', 'sub_button' => array()),
     *             array('name' => '菜单', 'sub_button' => array(
     *                  'list'=>array(
     *                         array('type' => 'view', 'name' => '搜索', 'url' => 'http://www.soso.com/', 'sub_button' => array(),),
     *                         array('type' => 'view', 'name' => '视频', 'url' => 'http://v.qq.com/', 'sub_button' => array(),),
     *                         array('type' => 'click', 'name' => '赞一下我们', 'key' => 'V1001_GOOD', 'sub_button' => array(),),),
     *                     )
     *             )
     *         )
     *     )
     * )
     * @throws WechatException
     */
    public static function current(OfficialAccount $app)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=ACCESS_TOKEN';
        return parent::request($url, $app);
    }

    /**
     * 创建菜单
     * @param OfficialAccount $app
     * @param array $data 菜单数据,例如:
     *
     * $data = array(
     *       array(
     *            "type"=>"click",
     *            "name"=>"今日歌曲",
     *            "key"=>"V1001_TODAY_MUSIC",
     *       ),
     *       array(
     *            "name"=>"菜单",
     *            "sub_button"=>array(
     *                  array(
     *                      "type"=>"view",
     *                      "name"=>"搜索",
     *                      "url"=>"http://www.soso.com/"
     *                  ),
     *                  array(
     *                        "type"=>"view",
     *                        "name"=>"视频",
     *                        "url"=>"http://v.qq.com/"
     *                  ),
     *                  array(
     *                      "type"=>"click",
     *                      "name"=>"赞一下我们",
     *                      "key"=>"V1001_GOOD"
     *                  ),
     *              ),
     *       )
     * );
     * @return array {"errcode":0,"errmsg":"ok"}
     * @throws WechatException
     */
    public static function create(OfficialAccount $app, array $data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN';
        $data = array('button' => $data);
        return parent::request($url, $app, $data);
    }

    /**
     * 删除菜单
     * @param OfficialAccount $app
     * @return array ["errcode"=>0,"errmsg"=>"ok"]
     * @throws WechatException
     */
    public static function delete(OfficialAccount $app)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN';
        return parent::request($url, $app);
    }

    /**
     * 创建个性化菜单
     * @param OfficialAccount $app
     * @param array $data
     * @param array $matchRule 菜单匹配规则
     * [
     *      group_id":"2",
     *      "sex":"1",
     *      "country":"中国",
     *      "province":"广东",
     *      "city":"广州",
     *      "client_platform_type":"2"
     *      "language":"zh_CN"
     * ]
     * @return string 返回个性化菜单id
     * @throws WechatException
     */
    public static function createConditional(OfficialAccount $app, array $data, array $matchRule)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=ACCESS_TOKEN';
        $data = array('button' => $data, 'matchrule' => $matchRule);
        $result = parent::request($url, $app, $data);
        return $result['menuid'];
    }

    /**
     * 删除个个化菜单，失败抛出异常
     * @param OfficialAccount $app
     * @param $menuId
     * @throws WechatException
     */
    public static function deleteConditional(OfficialAccount $app, $menuId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token=ACCESS_TOKEN';
        $data = array('menuid' => $menuId,);
        parent::request($url, $app, $data);
    }

    /**
     * 测试个性化菜单匹配结果
     * @param OfficialAccount $app
     * @param string $openid
     * @return array 返回菜单数据
     * @throws WechatException
     */
    public static function tryMatch(OfficialAccount $app, string $openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token=ACCESS_TOKEN';
        $data = array('user_id' => $openid,);
        return parent::request($url, $app, $data);
    }

}