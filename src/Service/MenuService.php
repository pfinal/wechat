<?php

namespace PFinal\Wechat\Service;

class MenuService extends BaseService
{
    /**
     * 查询菜单
     * @return array
     */
    public static function all()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=ACCESS_TOKEN';
        return parent::request($url);
    }

    /**
     * 创建菜单
     * @param array $data
     * @return array
     */
    public static function create(array $data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN';
        $data = array('button' => $data);
        return parent::request($url, $data);
    }

    /**
     * 删除菜单
     * @return array
     */
    public function delete()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN';
        return parent::request($url);
    }

    /**
     * 创建个性化菜单
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
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function createConditional(array $data, array $matchRule)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=ACCESS_TOKEN';
        $data = array('button' => $data, 'matchrule' => $matchRule);
        $result = parent::request($url, $data);
        return $result['menuid'];
    }

    /**
     * 删除个个化菜单，失败抛出异常
     * @param $menuId
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function deleteConditional($menuId)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token=ACCESS_TOKEN';
        $data = array('menuid' => $menuId,);
        parent::request($url, $data);
    }

    /**
     * 测试个性化菜单匹配结果
     * @param $openid
     * @return array 返回菜单数据
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function tryMatch($openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token=ACCESS_TOKEN';
        $data = array('user_id' => $openid,);
        return parent::request($url, $data);
    }

    /**
     * 获取自定义菜单配置
     * 本接口将会提供公众号当前使用的自定义菜单的配置，如果公众号是通过API调用设置的菜单，则返回菜单的开发配置，
     * 而如果公众号是在公众平台官网通过网站功能发布菜单，则本接口返回运营者设置的菜单配置
     * @return array
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function current()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=ACCESS_TOKEN';
        return parent::request($url);
    }
}