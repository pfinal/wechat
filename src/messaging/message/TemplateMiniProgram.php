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

namespace yanlongli\wechat\message;


use yanlongli\wechat\messaging\contract\TemplateMessage;

class TemplateMiniProgram implements TemplateMessage
{
    protected string $templateId;
    protected array $date;
    protected string $page;
    protected string $fromId;
    protected string $emphasis_keyword;

    /**
     * Template constructor.
     * @param string $templateId
     * @param string $fromId
     * @param string $page
     * @param array $data
     * @param string $emphasis_keyword
     */
    public function __construct(string $templateId, string $fromId, string $page = '', array $data = [], $emphasis_keyword = '')
    {
        $this->templateId = $templateId;
        $this->date = $data;
        $this->page = $page;
        $this->fromId = $fromId;
        $this->emphasis_keyword = $emphasis_keyword;

    }

    /**
     * @inheritDoc
     */
    public function jsonData()
    {
        return [
            'template_id' => $this->templateId,
            'page' => $this->page,
            'form_id' => $this->fromId,
            'data' => $this->date,
            'emphasis_keyword' => $this->emphasis_keyword,
        ];
    }
}