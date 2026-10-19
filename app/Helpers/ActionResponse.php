<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;

/**
 * 后台动作的统一响应.
 *
 * 控制器里「执行 → 成功/失败 → 异常记日志」这套流程原本在每个动作里逐处重复，
 * 这里收敛到一处，同时保持既有的响应格式：
 * - JSON 动作 → ['status' => 'success'|'fail', 'message' => trans(...)]
 * - 表单动作 → 成功跳转 / 失败回退到来源页并保留输入
 */
trait ActionResponse
{
    /**
     * 既有的成功 JSON 信封，供不走 $callback 流程的动作直接返回（如批量导出、开关切换）.
     */
    protected function successItem(string $actionKey): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => ActionMessage::success($actionKey)]);
    }

    /**
     * 既有的失败 JSON 信封.
     */
    protected function failItem(string $actionKey): JsonResponse
    {
        return response()->json(['status' => 'fail', 'message' => ActionMessage::failed($actionKey)]);
    }

    /**
     * 执行动作并按既有格式返回 JSON.
     *
     * @param  string  $actionKey  动作的翻译键，如 common.add
     * @param  string  $attributeKey  对象的翻译键，如 model.goods.category
     * @param  callable  $callback  返回真值代表成功
     */
    protected function actionResponse(string $actionKey, string $attributeKey, callable $callback): JsonResponse
    {
        try {
            if ($callback()) {
                return $this->successItem($actionKey);
            }
        } catch (Exception $e) {
            Log::error(ActionMessage::failureLog($actionKey, $attributeKey, $e->getMessage()));

            // 原文只进日志：这条 message 会被前台按 HTML 插入（SweetAlert2 与 alert 组件都是）
            return $this->failItem($actionKey);
        }

        return $this->failItem($actionKey);
    }

    /**
     * 执行表单动作并返回重定向响应.
     *
     * 成功时的跳转目标与提示逐处不同，因此由 $callback 自行返回 RedirectResponse；
     * 失败与异常路径统一回退来源页并保留输入。
     *
     * @param  string  $actionKey  动作的翻译键，如 common.add
     * @param  string  $attributeKey  对象的翻译键，如 model.goods.attribute
     * @param  callable  $callback  返回 RedirectResponse 代表成功，返回空值代表失败
     */
    protected function redirectAction(string $actionKey, string $attributeKey, callable $callback): RedirectResponse
    {
        try {
            if ($redirect = $callback()) {
                return $redirect;
            }
        } catch (Exception $e) {
            Log::error(ActionMessage::failureLog($actionKey, $attributeKey, $e->getMessage()));

            // 原文只进日志：错误袋会被 components/alert.blade.php 以 {!! !!} 渲染
            return redirect()->back()->withInput()->withErrors(ActionMessage::failed($actionKey));
        }

        return redirect()->back()->withInput()->withErrors(ActionMessage::failed($actionKey));
    }
}
