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
 *   Date:   2019/11/14
 *   IDE:    PhpStorm
 *   Desc:  收到消息基础
 */
declare(strict_types=1);

namespace yanlongli\wechat\messaging\receive;


use yanlongli\wechat\messaging\contract\ReplyMessage;
use yanlongli\wechat\messaging\contract\Message;

/**
 * Class Receive
 * @package yanlongli\wechat\messaging\receive
 * @property string $FromUserName 发送方帐号(OpenID)
 * @property string $ToUserName 公众号原始id
 * @property string $CreateTime 消息创建时间(整型)
 * @property string $MsgType 消息类型
 */
class ReceiveMessage implements Message
{

    #region 回复相关
    /**
     * @var ReplyMessage
     */
    protected ?ReplyMessage $replyMessage;
    /**
     * 是否停止继续传播
     * @var bool
     * @see 标记为 true 后，后续的handle将不会被触发
     */
    protected bool $propagationStopped = false;

    /**
     * 是否已被处理
     * @var bool
     * @see 标记为 true 后，后续的handle将无法再次回复，可以重新标记为 false 用于强制覆盖已准备回复的内容
     */
    protected bool $processed = false;


    /**
     * 标记为停止继续传播
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * 是否被标记为停止继续传播
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * 标记为已经处理回复
     */
    public function alreadyProcessed(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * 是否已标记为回复
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * 回复消息
     * @param ReplyMessage $message
     * @see 注意并非实时回复，而是等待流程结束后回复，即有可能被撤回发送
     */
    public function sendMessage(?ReplyMessage $message): void
    {
        //保存回复的消息
        $this->replyMessage = $message;
        //将事件标记为已处理回复
        $this->alreadyProcessed();
    }

    /**
     * 撤回准备发送的消息，撤回成功返回 true ,没有等待发送的消息返回 false
     * @return bool
     */
    public function WithdrawMessage(): bool
    {
        if ($this->replyMessage) {
            unset($this->replyMessage);
            return true;
        }
        return false;
    }

    /**
     * 获取回复消息
     * @return ReplyMessage
     */
    public function getReplyMessage()
    {
        return $this->replyMessage;
    }

    #endregion

    #region 收到消息的原始属性
    protected array $attribute = [];

    public function __get($name)
    {
        return $this->attribute[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attribute[$name] = $value;
    }

    /**
     * 批量设置属性
     * @param array $attr
     */
    public function setAttr(array $attr)
    {
        $this->attribute = $attr;
    }

    #endregion

}