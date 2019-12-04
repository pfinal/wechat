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
 *   Date:   2019/11/8
 *   IDE:    PhpStorm
 *   Desc:
 */
declare(strict_types=1);

namespace yanlongli\wechat;


use yanlongli\wechat\service\AccessTokenService;

/**
 * Class App
 * @package yanlongli\wechat
 */
abstract class App
{
    /**
     * 应用id
     * @var string
     */
    public string $appId;

    /**
     * 原始ID
     * @var string
     */
    public string $id;

    //加密类型
    const ENCRYPT_TYPE_RAW = 'raw';
    const ENCRYPT_TYPE_AES = 'aes';

    /**
     * @var string
     */
    public string $appSecret;
    /**
     * @var string 服务器配置令牌
     */
    public string $token;
    /**
     * @var string 消息加解密密钥
     */
    public string $encodingAesKey;
    public string $encodingAesKeyLast;
    public string $middleUrl;

    public string $accessToken;

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * 应用名称
     * @var string
     */
    public string $name;


    /**
     * 构造方法 可以使用一个数组作为参数
     * @param array|string $appId
     * @param string|null $appSecret
     * @param string|null $token
     * @param string|null $encodingAesKey
     * @param string|null $encodingAesKeyLast
     * @param string|null $middleUrl
     */
    public function __construct($appId, string $appSecret = null, string $token = null, string $encodingAesKey = null, string $encodingAesKeyLast = null, string $middleUrl = null)
    {
        if (is_array($appId)) {
            extract($appId, EXTR_OVERWRITE);
        }

        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->encodingAesKeyLast = $encodingAesKeyLast;
        $this->middleUrl = $middleUrl;

        //注册到 Wechat 中
        Wechat::addApp($this);

    }

    /**
     * @param bool $useCache
     * @return string
     * @throws WechatException
     */
    public function getAccessToken(bool $useCache = true)
    {
        if ($useCache && $this->accessToken) {
            return $this->accessToken;
        }
        return AccessTokenService::getAccessToken($this, $useCache);
    }


    public function __get($name)
    {
        return $this->services[$name] ?? null;
    }

    public function __set($name, $service)
    {
        $this->services[$name] = $service;
    }

}