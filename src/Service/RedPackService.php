<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Kernel;
use PFinal\Wechat\SDK\Redpack\CommonUtil;
use PFinal\Wechat\Support\Log;

class RedPackService extends BaseService
{
    /**
     * 给指定用户发红包
     * 请确保您的libcurl版本是否支持双向认证，版本高于7.20.1，通过 curl_version() 查看
     *
     * @param $openid
     * @param float $money 红包金额，单位元
     * @param string $actName
     * @param string $sendName
     * @param string $remark
     * @param string $wishing
     * @return array
     * @throws \Exception
     */
    public static function send($openid, $money, $actName = '抢红包活动', $sendName = '商家', $remark = '祝您生活愉快', $wishing = '恭喜发财')
    {
        $helper = Kernel::getRedpackHelper();

        $money = $money * 100;//红包金额，转为单位分
        $money = number_format($money, 0, '.', '');//去掉小数
        if ($money <= 0) {
            throw new \Exception('金额不能为0');
        }

        $commonUtil = new CommonUtil();

        $helper->setParameter("nonce_str", $commonUtil->create_noncestr());//随机字符串，长于 32 位
        $helper->setParameter("mch_billno", $helper->mchId . date('YmdHis') . rand(1000, 9999));//订单号
        $helper->setParameter("mch_id", $helper->mchId);//商户号
        $helper->setParameter("wxappid", $helper->appId);
        $helper->setParameter("send_name", $sendName);//商户名称  红包发送者名称
        $helper->setParameter("re_openid", $openid);//接受收红包的用户 用户在wxappid下的openid
        $helper->setParameter("total_amount", $money);//付款金额，单位分
        $helper->setParameter("total_num", 1);//红包发放总人数
        $helper->setParameter("wishing", $wishing);//红包祝福语
        $helper->setParameter("client_ip", '127.0.0.1');//调用接口的机器 Ip 地址
        $helper->setParameter("act_name", $actName);//活动名称
        $helper->setParameter("remark", $remark);//备注信息
        $postXml = $helper->create_hongbao_xml();

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';

        $responseXml = $helper->curl_post_ssl($url, $postXml);

        if ($responseXml === false) {
            throw new \Exception("curl失败");
        }

        //<xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[发放成功]]></return_msg> <result_code><![CDATA[SUCCESS]]></result_code> <mch_billno><![CDATA[1284519901201606210833284760]]></mch_billno> <mch_id>1284519901</mch_id> <wxappid><![CDATA[wxf26a72acf056aba7]]></wxappid> <re_openid><![CDATA[o0N6btzkUNg84Ys9OmUXQexCMz5s]]></re_openid> <total_amount>101</total_amount> <send_listid><![CDATA[0010819155201606210907873331]]></send_listid> <send_time><![CDATA[20160621143330]]></send_time> </xml>
        Log::debug($responseXml);

        /*
        https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_5
        错误码

        NO_AUTH	发放失败，此请求可能存在风险，已被微信拦截	用户账号异常，被拦截	请提醒用户检查自身帐号是否异常。使用常用的活跃的微信号可避免这种情况。
        SENDNUM_LIMIT	该用户今日领取红包个数超过限制	该用户今日领取红包个数超过你在微信支付商户平台配置的上限	如有需要、请在微信支付商户平台【api安全】中重新配置 【每日同一用户领取本商户红包不允许超过的个数】。
        ILLEGAL_APPID	非法appid，请确认是否为公众号的appid，不能为APP的appid	错误传入了app的appid	接口传入的所有appid应该为公众号的appid（在mp.weixin.qq.com申请的），不能为APP的appid（在open.weixin.qq.com申请的）。
        MONEY_LIMIT	红包金额发放限制	发送红包金额不再限制范围内	每个红包金额必须大于1元，小于200元（可联系微信支付wxhongbao@tencent.com申请调高额度）
        SEND_FAILED	红包发放失败,请更换单号再重试	该红包已经发放失败	如果需要重新发放，请更换单号再发放
        FATAL_ERROR	openid和原始单参数不一致	更换了openid，但商户单号未更新	请商户检查代码实现逻辑
        金额和原始单参数不一致	更换了金额，但商户单号未更新	请商户检查代码实现逻辑	请检查金额、商户订单号是否正确
        CA_ERROR	CA证书出错，请登录微信支付商户平台下载证书	请求携带的证书出错	到商户平台下载证书，请求带上证书后重试
        SIGN_ERROR	签名错误	1、没有使用商户平台设置的商户API密钥进行加密（有可能之前设置过密钥，后来被修改了，没有使用新的密钥进行加密）。
        2、加密前没有按照文档进行参数排序（可参考文档）
        3、把值为空的参数也进行了签名。可到（http://mch.weixin.qq.com/wiki/tools/signverify/ ）验证。
        4、如果以上3步都没有问题，把请求串中(post的数据）里面中文都去掉，换成英文，试下，看看是否是编码问题。（post的数据要求是utf8）	1. 到商户平台重新设置新的密钥后重试
        2. 检查请求参数把空格去掉重试
        3. 中文不需要进行encode，使用CDATA
        4. 按文档要求生成签名后再重试
        在线签名验证工具：http://mch.weixin.qq.com/wiki/tools/signverify/
        SYSTEMERROR	请求已受理，请稍后使用原单号查询发放结果	系统无返回明确发放结果	使用原单号调用接口，查询发放结果，如果使用新单号调用接口，视为新发放请求
        XML_ERROR	输入xml参数格式错误	请求的xml格式错误，或者post的数据为空	检查请求串，确认无误后重试
        FREQ_LIMIT	超过频率限制,请稍后再试	受频率限制	请对请求做频率控制（可联系微信支付wxhongbao@tencent.com申请调高）
        NOTENOUGH	帐号余额不足，请到商户平台充值后再重试	账户余额不足	充值后重试
        OPENID_ERROR	openid和appid不匹配	openid和appid不匹配	发红包的openid必须是本appid下的openid
        PARAM_ERROR	act_name字段必填,并且少于32个字符	请求的act_name字段填写错误	填写正确的act_name后重试
        发放金额、最小金额、最大金额必须相等	请求的金额相关字段填写错误	按文档要求填写正确的金额后重试
        红包金额参数错误	红包金额过大	修改金额重试
        appid字段必填,最长为32个字符	请求的appid字段填写错误	填写正确的appid后重试
        订单号字段必填,最长为28个字符	请求的mch_billno字段填写错误	填写正确的billno后重试
        client_ip必须是合法的IP字符串	请求的client_ip填写不正确	填写正确的IP后重试
        输入的商户号有误	请求的mchid字段非法（或者没填）	填写对应的商户号再重试
        找不到对应的商户号	请求的mchid字段填写错误	填写正确的mchid字段后重试
        nick_name字段必填，并且少于16字符	请求的nick_name字段错误	按文档填写正确的nick_name后重试
        nonce_str字段必填,并且少于32字符	请求的nonce_str字段填写不正确	按文档要求填写正确的nonce_str值后重试
        re_openid字段为必填并且少于32个字符	请求的re_openid字段非法	填写对re_openid后重试
        remark字段为必填,并且少于256字符	请求的remark字段填写错误	填写正确的remark后重试
        send_name字段为必填并且少于32字符	请求的send_name字段填写不正确	按文档填写正确的send_name字段后重试
        total_num必须为1	total_num字段值不为1	修改total_num值为1后重试
        wishing字段为必填,并且少于128个字符	缺少wishing字段	填写wishing字段再重试
        商户号和wxappid不匹配	商户号和wxappid不匹配	请修改Mchid或wxappid参数
        */
        $responseObj = @simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);


        if ($responseObj === false) {
            throw new \Exception("解析xml失败");
        }

        if ("$responseObj->result_code" === "SUCCESS") {
            return (array)$responseObj;
        }

        throw new \Exception($responseObj->err_code . ' ' . $responseObj->return_msg);
    }
}
