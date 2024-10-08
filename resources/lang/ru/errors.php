<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => 'Обнаружен доступ с неизвестного IP или прокси, доступ запрещен',
        'bots' => 'Обнаружен доступ бота, доступ запрещен',
        'china' => 'Обнаружен доступ с IP или прокси из Китая, доступ запрещен',
        'oversea' => 'Обнаружен доступ с зарубежного IP или прокси, доступ запрещен',
        'redirect' => 'Обнаружен (:ip :url) доступ через ссылку подписки, выполняется принудительное перенаправление',
        'unknown' => 'Неизвестный режим запрета доступа! Пожалуйста, измените [Режим запрета доступа] в настройках системы!',
    ],
    'get_ip' => 'Ошибка при получении информации об IP',
    'log' => 'Журнал',
    'refresh' => 'Обновить',
    'refresh_page' => 'Пожалуйста, обновите страницу и попробуйте снова',
    'report' => 'Ошибка содержала отчет: ',
    'safe_code' => 'Пожалуйста, введите код безопасности',
    'safe_enter' => 'Безопасный вход',
    'subscribe' => [
        'banned_until' => 'Аккаунт заблокирован до :time, пожалуйста, дождитесь разблокировки!',
        'expired' => 'Срок действия аккаунта истек! Пожалуйста, продлите подписку!',
        'none' => 'Нет доступных узлов',
        'out' => 'Трафик исчерпан! Пожалуйста, купите больше или сбросьте лимит!',
        'question' => 'Проблемы с аккаунтом! Посетите веб-сайт для получения подробностей',
        'sub_banned' => 'Подписка заблокирована! Посетите веб-сайт для получения подробностей',
        'unknown' => 'Недействительная ссылка подписки! Пожалуйста, получите новую!',
        'user' => 'Недействительный URL, аккаунт не существует!',
        'user_disabled' => 'Аккаунт отключен! Свяжитесь с поддержкой!',
    ],
    'title' => '⚠️ Возникла ошибка',
    'unsafe_enter' => 'Небезопасный вход',
    'visit' => 'Пожалуйста, посетите',
    'whoops' => 'Упс!',
];
