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

namespace yanlongli\wechat\messaging\message;


use yanlongli\wechat\messaging\contract\TemplateMessage;

class Template implements TemplateMessage
{
    protected string $templateId;
    protected array $date;
    protected string $url;
    protected string $topColor;

    /**
     * Template constructor.
     * @param string $templateId
     * @param array $data
     * @param string $url
     * @param string $topColor
     * @param string $defaultItemColor
     */
    public function __construct(string $templateId, array $data, string $url = '', string $topColor = '#FF0000', string $defaultItemColor = '#173177')
    {
        $this->templateId = $templateId;

        foreach ($data as $key => $val) {
            if (!is_array($val)) {
                $data[$key] = array(
                    'value' => "$val",
                    'color' => "$defaultItemColor",
                );
            }
        }

        $this->date = $data;
        $this->url = $url;
        $this->topColor = $topColor;

    }

    /**
     * @inheritDoc
     */
    public function jsonData()
    {
        return [
            'template_id' => $this->templateId,
            'url' => $this->url,
            'topcolor' => $this->topColor,
            'data' => $this->date,
        ];
    }
}