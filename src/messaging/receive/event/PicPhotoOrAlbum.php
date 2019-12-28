<?php
/**
 * Copyright (c) [2019] Yanlongli<ahlyl94@gmail.com>
 * [Software Name] is licensed under the Mulan PSL v1.
 * You can use this software according to the terms and conditions of the Mulan PSL v1.
 * You may obtain a copy of Mulan PSL v1 at:
 *     http://license.coscl.org.cn/MulanPSL
 * THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT, MERCHANTABILITY OR FIT FOR A PARTICULAR
 * PURPOSE.
 * See the Mulan PSL v1 for more details.
 *
 *   Author: Yanlongli <jobs@yanlongli.com>
 *   Date:   2019/11/14
 *   IDE:    PhpStorm
 *   Desc:   系统拍照发图
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\receive\event;

/**
 * Class PicSysPhoto
 * @package yanlongli\wechat\messaging\receive\event
 * @property string EventKey menu_key 自定义菜单的 key
 * @property array $SendPicsInfo 发送的图片数量  图片列表  图片的MD5值，开发者若需要，可用于验证接收到图片
 * @property int $_Count 数量
 * @property array $_PicList 照片列表
 * @property string $__PicMd5Sum 照片的md5值
 */
class PicPhotoOrAlbum extends Click
{
    const EVENT = 'pic_photo_or_album';
}
