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
 *   Date:   2019/11/7
 *   IDE:    PhpStorm
 *   Desc:   微信管理SDK核心
 */
declare(strict_types=1);

namespace yanlongli\wechat;


/**
 * Class Wechat
 * @package yanlongli\wechat\core
 */
class Wechat
{
    /**
     * 实例
     * @var array[App]
     */
    protected static $apps = [];

    public function __get($name)
    {
        var_dump($name);
    }

    /**
     * @return array
     */
    public function getApps(): array
    {
        return self::$apps;
    }

    /**
     * @param string $appId
     * @return App|null
     */
    public static function getApp(string $appId)
    {
        return self::$apps[$appId] ?? null;
    }

    /**
     * @param App $app
     */
    public static function addApp(App $app): void
    {
        self::$apps[$app->appId] = $app;
    }
}