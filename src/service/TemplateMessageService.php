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
 *   Date:   2019/11/8
 *   IDE:    PhpStorm
 *   Desc:
 */
declare(strict_types=1);

namespace yanlongli\wechat\service;


use yanlongli\wechat\messaging\message\Template;
use yanlongli\wechat\miniProgram\MiniProgram;
use yanlongli\wechat\officialAccount\OfficialAccount;
use yanlongli\wechat\WechatException;

/**
 * Class TemplateMessageService
 * @package yanlongli\wechat\service
 */
class TemplateMessageService extends BaseService
{

    /**
     * 发送公众号模板消息
     *
     * @param OfficialAccount|MiniProgram $app
     * @param string $openid
     * @param Template $template
     * @return array
     * @throws WechatException
     */
    public function send(OfficialAccount $app, string $openid, Template $template)
    {
        if ($app instanceof OfficialAccount) {

            $apiUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=ACCESS_TOKEN';
            $postData = array_merge([
                'touser' => $openid
            ], $template->jsonData());
        } elseif ($app instanceof MiniProgram) {
            $apiUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=ACCESS_TOKEN';

            $postData = array_merge([
                'touser' => $openid
            ], $template->jsonData());

        } else {
            throw new WechatException('不支持的APP类型');
        }

        return BaseService::request($apiUrl, $app, $postData);
    }

    /**
     * 获取公众号消息模板列表
     * @param OfficialAccount $app
     * @return array {
     * "template_list": [{
     * "template_id": "iPk5sOIt5X_flOVKn5GrTFpncEYTojx6ddbt8WYoV5s",
     * "title": "领取奖金提醒",
     * "primary_industry": "IT科技",
     * "deputy_industry": "互联网|电子商务",
     * "content": "{ {result.DATA} }\n\n领奖金额:{ {withdrawMoney.DATA} }\n领奖  时间:    { {withdrawTime.DATA} }\n银行信息:{ {cardInfo.DATA} }\n到账时间:  { {arrivedTime.DATA} }\n{ {remark.DATA} }",
     * "example": "您已提交领奖申请\n\n领奖金额：xxxx元\n领奖时间：2013-10-10 12:22:22\n银行信息：xx银行(尾号xxxx)\n到账时间：预计xxxxxxx\n\n预计将于xxxx到达您的银行卡"
     * }]
     * }
     * @throws WechatException
     */
    public static function templateList(OfficialAccount $app)
    {
        $apiUrl = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=ACCESS_TOKEN';

        return parent::request($apiUrl, $app);
    }
}