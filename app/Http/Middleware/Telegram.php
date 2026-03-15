<?php

namespace App\Http\Middleware;

use Closure;

class Telegram
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = sysConfig('telegram_token');
        $access_token = $request->header('x-telegram-bot-api-secret-token');

        // 根据用户 language_code 设置语言
        $this->setLocaleFromRequest($request);

        if (isset($token, $access_token) && hash_equals(hash('sha256', explode(':', $token)[1]), $access_token)) {
            return $next($request);
        }

        abort(500, 'authentication failed');
    }

    /**
     * 从 Telegram 请求中提取 language_code 并设置语言.
     */
    private function setLocaleFromRequest($request): void
    {
        $data = $request->all();

        if (! isset($data['message']['from']['language_code'])) {
            return;
        }

        $languageCode = $data['message']['from']['language_code'];
        $locale = $this->getLocaleFromLanguageCode($languageCode);

        app()->setLocale($locale);
    }

    /**
     * Telegram language_code 转换为 Laravel locale.
     */
    private function getLocaleFromLanguageCode(string $languageCode): string
    {
        // 常见 Telegram 语言代码映射
        $mapping = [
            'zh-hans' => 'zh_CN',      // 简体中文
            'zh-hant' => 'zh_CN',      // 繁體中文
            'ja' => 'ja',              // 日本語
            'ko' => 'ko',              // 한국어
            'ru' => 'ru',              // Русский
            'de' => 'de',              // Deutsch
            'vi' => 'vi',              // Tiếng Việt
            'en' => 'en',
            'fa' => 'fa',
        ];

        return $mapping[$languageCode] ?? config('app.locale', 'en');
    }
}
