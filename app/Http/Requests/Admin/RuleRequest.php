<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RuleRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string',
            'pattern' => 'required|string',
        ];

        if (! in_array($this->method(), ['PATCH', 'PUT'], true)) {
            $rules += ['type' => 'required|numeric|between:1,4'];
        }

        // reg 规则被两个后端共享：VNET(type=4) 用 Go RE2，SSR(type=1) 用 Python re。
        // RE2 不支持的构造在 Go 侧只会静默跳过（规则失效不报错），而 RE2 兼容的子集 Python 同样能跑，
        // 所以保存时就挡下不兼容写法，两个后端都安全
        $type = (int) ($this->input('type') ?? $this->route('rule')?->type ?? 0);
        if ($type === 1 && $this->filled('pattern')) {
            $rules['pattern'] = [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! self::re2Compatible((string) $value)) {
                        $fail('正则含 Go RE2 不支持的构造（前瞻/后顾、原子组、条件或反向引用），VNET 节点会静默跳过该规则；请改写为 RE2 兼容语法，Python 后端同样能跑。');
                    }
                },
            ];
        }

        return $rules;
    }

    public static function re2Compatible(string $pattern): bool
    {
        // 前瞻/后顾、原子组、条件、反向引用、\G —— Go RE2 全部拒绝编译
        return ! preg_match('/\(\?=|\(\?!|\(\?<=|\(\?<!|\(\?>|\(\?\(|\\\\[1-9]|\\\\G/', $pattern);
    }
}
