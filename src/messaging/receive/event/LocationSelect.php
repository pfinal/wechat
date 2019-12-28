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
 * 弹出地图选择器
 * Class LocationSelect
 * @package yanlongli\wechat\messaging\receive\event
 * @property array SendLocationInfo
 * @property string _Location_X
 * @property string _Location_Y
 * @property string _Scale
 * @property string _Label
 * @property string _Poiname
 */
class LocationSelect extends Click
{
    const EVENT = 'location_select';
}
