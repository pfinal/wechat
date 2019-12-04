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
 *   Date:   2019/11/22
 *   IDE:    PhpStorm
 *   Desc:   不进行回复
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\message;


use yanlongli\wechat\messaging\contract\ReplyMessage;

class NoReply implements ReplyMessage
{

    protected string $type = 'NO-REPLY';

    /**
     * @inheritDoc
     */
    public function jsonData()
    {
    }

    public function type()
    {
    }
}