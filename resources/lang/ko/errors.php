<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => '알 수 없는 IP 또는 프록시 접근이 감지되었습니다. 접근이 거부되었습니다.',
        'bots' => '봇 접근이 감지되었습니다. 접근이 거부되었습니다.',
        'china' => '중국 IP 또는 프록시 접근이 감지되었습니다. 접근이 거부되었습니다.',
        'oversea' => '해외 IP 또는 프록시 접근이 감지되었습니다. 접근이 거부되었습니다.',
        'unknown' => '알 수 없는 금지 액세스 모드입니다! 시스템 설정에서 [접근 제한 모드]를 수정하십시오!',
        'redirect' => '(:ip :url) 구독 링크를 통해 접근이 감지되어 강제 리디렉션 중입니다.',
    ],
    'log' => '로그',
    'refresh' => '새로 고침',
    'refresh_page' => '페이지를 새로 고친 후 다시 시도해 주세요',
    'report' => '오류 보고서 포함: ',
    'safe_enter' => '안전한 입구',
    'safe_code' => '안전 코드를 입력해 주세요',
    'title' => '⚠️ 오류 발생',
    'unsafe_enter' => '안전하지 않은 입구',
    'visit' => '방문해 주세요',
    'whoops' => '이런!',
    'get_ip' => 'IP 정보를 가져오지 못했습니다',
    'subscribe' => [
        'unknown' => '잘못된 구독 링크입니다! 새 링크를 받아 주세요!',
        'sub_banned' => '구독이 차단되었습니다! 자세한 내용은 웹사이트를 방문해 주세요',
        'user' => '잘못된 URL입니다. 계정이 존재하지 않습니다!',
        'user_disabled' => '계정이 비활성화되었습니다! 지원팀에 문의해 주세요!',
        'banned_until' => '계정이 :time까지 차단되었습니다. 잠금 해제될 때까지 기다려 주세요!',
        'out' => '데이터가 소진되었습니다! 더 구매하거나 데이터를 재설정해 주세요!',
        'expired' => '계정이 만료되었습니다! 구독을 갱신해 주세요!',
        'question' => '계정에 문제가 있습니다! 자세한 내용은 웹사이트를 방문해 주세요',
        'none' => '사용 가능한 노드가 없습니다',
    ],
];
