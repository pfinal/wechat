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
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\receive\event;

/**
 * 扫码推送
 * Class ScanCodePush
 * @package yanlongli\wechat\messaging\receive\event
 * @property array[] $ScanCodeInfo 扫描信息
 * @property $ScanType 扫描类型，一般是qrcode
 * @property $ScanResult 扫描结果，即二维码对应的字符串信息
 */
class ScanCodeWaitMsg extends Click
{
    const EVENT = 'scancode_waitmsg';
}
