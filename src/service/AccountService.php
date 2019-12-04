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
use yanlongli\wechat\WechatException;

class AccountService extends BaseService
{
    /**
     * 新增客服
     * @param App $app
     * @param $account
     * @param $nickname
     * @param $password
     * @return array
     * @throws WechatException
     */
    public static function create(App $app, string $account, string $nickname, string $password)
    {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/add?access_token=ACCESS_TOKEN';
        return parent::request($url, $app, compact('account', 'nickname', 'password'));
    }

    /**
     * 更新客服资料
     * @param App $app
     * @param $account
     * @param $nickname
     * @param $password
     * @return array
     * @throws WechatException
     */
    public static function update(App $app, string $account, string $nickname, string $password)
    {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/update?access_token=ACCESS_TOKEN';
        return parent::request($url, $app, compact('account', 'nickname', 'password'));
    }

    /**
     * 设置客服帐号的头像
     * @param App $app
     * @param string $account
     * @param string $filename 头像图片文件必须是jpg格式，推荐使用640*640大小的图片以达到最佳效果
     * @return array
     * @throws WechatException
     */
    public static function avatar(App $app, string $account, string $filename)
    {
        $url = 'http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=ACCESS_TOKEN&kf_account=' . $account;

        $filename = realpath($filename);

        if (class_exists('\CURLFile')) {
            $data['media'] = new \CURLFile($filename);
        } else {
            $data['media'] = '@' . $filename;
        }

        return parent::request($url, $app, $data, false);
    }

    /**
     * 获取所有客服账号
     * @param App $app
     * @return array
     * @throws WechatException
     */
    public static function all(App $app)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=ACCESS_TOKEN';

        $result = parent::request($url, $app);
        return $result['kf_list'];
    }

}
