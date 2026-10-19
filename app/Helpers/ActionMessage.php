<?php

namespace App\Helpers;

/**
 * 后台动作文案的唯一构造点：成功/失败提示与异常日志行都在这里拼，控制器与 Service 都调它。
 *
 * 传入的都是翻译键（如 common.add、model.node.attribute），不是已翻译的文本。
 */
class ActionMessage
{
    public static function success(string $actionKey): string
    {
        return trans('common.success_item', ['attribute' => trans($actionKey)]);
    }

    public static function failed(string $actionKey): string
    {
        return trans('common.failed_item', ['attribute' => trans($actionKey)]);
    }

    /**
     * 异常日志行；$reason 是异常原文，只进日志，不进响应（响应会被前台按 HTML 插入）.
     */
    public static function failureLog(string $actionKey, string $attributeKey, string $reason): string
    {
        return trans('common.error_action_item', ['action' => trans($actionKey), 'attribute' => trans($attributeKey)]).': '.$reason;
    }
}
