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
 *   Desc:  客服选项卡菜单 需要服务号 不支持订阅号 不支持测试账号
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\message;


use yanlongli\wechat\messaging\contract\CallMessage;

/**
 * Class MsgMenu
 * @package yanlongli\wechat\messaging\message
 */
class MsgMenu implements CallMessage
{

    protected $type = 'msgmenu';
    protected $tail;
    protected $list;
    protected $title;

    /**
     * Menu constructor.
     * @param string $title
     * @param array $list
     * @param string $tail
     */
    public function __construct($title, $list, $tail)
    {
        $this->title = $title;
        $this->list = $list;
        $this->tail = $tail;
    }

    public function type()
    {
        return $this->type;
    }

    /**
     * @param string $id
     * @param string $title
     * @return array
     */
    public static function option($id, $title)
    {
        return ['id' => $id, 'title' => $title];
    }

    /**
     * @inheritDoc
     */
    public function jsonData()
    {
        return [
            'msgmenu' => [
                'head_content' => $this->title,
                'list' => $this->list,
                'tail_content' => $this->tail,
            ]
        ];
    }
}