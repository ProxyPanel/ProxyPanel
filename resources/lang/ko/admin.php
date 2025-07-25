<?php

declare(strict_types=1);

return [
    'action' => [
        'add_item' => ':attribute 추가',
        'edit_item' => ':attribute 편집',
    ],
    'aff' => [
        'apply_counts' => '총 <code>:num</code> 출금 신청',
        'commission_counts' => '이 신청에는 총 <code>:num</code> 주문이 포함됩니다',
        'commission_title' => '출금 신청 세부 사항',
        'counts' => '총 <code>:num</code> 기록',
        'rebate_title' => '리베이트 기록',
        'referral' => '추천 리베이트',
        'title' => '출금 신청 목록',
    ],
    'article' => [
        'category_hint' => '동일한 카테고리는 동일한 폴더에 분류됩니다',
        'counts' => '총 <code>:num</code> 기사',
        'logo_placeholder' => '또는 로고 URL을 입력하세요',
        'title' => '기사 목록',
        'type' => [
            'announcement' => '공지사항',
            'knowledge' => '기사',
        ],
    ],
    'clear' => '지우기',
    'clone' => '복제',
    'confirm' => [
        'continues' => '이 작업을 계속 진행하시겠습니까?',
        'delete' => [0 => '정말로 :attribute', 1 => '를 삭제하시겠습니까?'],
        'export' => '모두 내보내기를 진행하시겠습니까?',
    ],
    'coupon' => [
        'counts' => '총 <code>:num</code> 쿠폰',
        'created_days_hint' => '계정 생성 후 <code>:day</code>일',
        'discount' => '할인',
        'export_title' => '쿠폰 내보내기',
        'groups_hint' => '선택한 사용자 그룹에만 사용 가능',
        'info_title' => '쿠폰 정보',
        'levels_hint' => '선택한 사용자 레벨에만 사용 가능',
        'limit_hint' => '이 항목의 각 조건은 <strong>AND</strong> 관계를 가지므로 적절히 사용하세요',
        'minimum_hint' => '결제 금액이 <strong>:num</strong>을 초과해야 사용 가능',
        'name_hint' => '전면 표시용',
        'newbie' => [
            'created_days' => '계정 생성 일수',
            'first_discount' => '첫 구매 할인',
            'first_order' => '첫 주문',
        ],
        'priority_hint' => '최고 우선 순위 쿠폰이 먼저 사용됩니다. 최대 255',
        'services_blacklist_hint' => '블랙리스트에 있는 상품에는 사용 불가, 사용하지 않으려면 비워 두십시오',
        'services_placeholder' => '상품 ID를 입력하고 Enter를 누르세요',
        'services_whitelist_hint' => '화이트리스트에 있는 상품에만 사용 가능, 사용하지 않으려면 비워 두십시오',
        'single_use' => '일회용',
        'sn_hint' => '8자리 랜덤 코드로 기본 설정',
        'title' => '쿠폰 목록',
        'type' => [
            'charge' => '충전 쿠폰',
            'discount' => '할인 쿠폰',
            'voucher' => '바우처',
        ],
        'type_hint' => '감액: 상품 금액 차감, 할인: 상품 퍼센트 할인, 충전: 계정 잔액 추가',
        'used_hint' => '사용자는 이 쿠폰을 최대 <strong>:num</strong>회 사용할 수 있습니다',
        'user_whitelist_hint' => '화이트리스트에 있는 사용자는 사용할 수 있습니다. 사용하지 않으려면 비워 두십시오',
        'users_blacklist_hint' => '블랙리스트에 있는 사용자는 사용할 수 없습니다. 사용하지 않으려면 비워 두십시오',
        'users_placeholder' => '사용자 ID를 입력하고 Enter를 누르세요',
        'value' => '{1} ➖ :num|{2} :num% 할인|{3} ➕ :num',
        'value_hint' => '범위는 1% ~ 99%',
    ],
    'creating' => '추가 중...',
    'dashboard' => [
        'abnormal_users' => '최근 1시간 내 비정상적인 트래픽을 사용한 사용자',
        'active_days_users' => ':days일 이내 활성 사용자',
        'available_users' => '활성 사용자 수',
        'credit' => '총 잔액',
        'current_month_traffic_consumed' => '이번 달 사용된 데이터',
        'days_traffic_consumed' => ':days일 내 사용된 데이터',
        'expiring_users' => '곧 만료될 사용자',
        'inactive_days_users' => ':days일 이상 비활성화된 사용자',
        'maintaining_nodes' => '유지보수 중인 노드',
        'nodes' => '노드 수',
        'online_orders' => '온라인 결제 주문 수',
        'online_users' => '현재 온라인 사용자',
        'orders' => '총 주문 수',
        'overuse_users' => '데이터 사용량이 90% 이상인 사용자',
        'paid_users' => '유료 사용자 수',
        'succeed_orders' => '결제 완료된 주문 수',
        'users' => '총 사용자 수',
        'withdrawing_commissions' => '출금 대기 중인 커미션',
        'withdrawn_commissions' => '출금된 커미션',
    ],
    'end_time' => '종료 시간',
    'goods' => [
        'counts' => '총 <code>:num</code> 상품',
        'info' => [
            'available_date_hint' => '만료 후 총 데이터에서 해당 데이터를 자동으로 차감합니다',
            'desc_placeholder' => '상품의 간단한 설명',
            'limit_num_hint' => '사용자가 이 상품을 구매할 수 있는 최대 횟수, 0은 무제한',
            'list_hint' => '각 행의 내용은 <code>&lt;li&gt;</code>로 시작하고 <code>&lt;/li&gt;</code>로 끝나야 합니다',
            'list_placeholder' => '상품의 사용자 정의 목록 추가',
            'period_hint' => '플랜의 데이터는 N일마다 초기화됩니다',
            'type_hint' => '플랜은 계정 만료와 관련이 있으며, 패키지는 데이터만 차감하고 만료에 영향을 미치지 않습니다',
        ],
        'sell_and_used' => '사용/판매',
        'status' => [
            'no' => '판매 중지',
            'yes' => '판매 중',
        ],
        'title' => '상품 목록',
        'type' => [
            'package' => '데이터 패키지',
            'plan' => '구독 플랜',
            'top_up' => '충전',
        ],
    ],
    'hint' => '힌트',
    'logs' => [
        'ban' => [
            'ban_time' => '차단 시간',
            'last_connect_at' => '마지막 로그인 시간',
            'reason' => '이유',
            'time' => '기간',
            'title' => '사용자 차단 기록',
        ],
        'callback' => '콜백 로그 <small>(결제)</small>',
        'counts' => '총 <code>:num</code> 기록',
        'credit_title' => '잔액 변동 기록',
        'ip_monitor' => '온라인 IP 모니터링 <small>실시간 2분</small>',
        'notification' => '이메일 로그',
        'order' => [
            'is_coupon' => '쿠폰 사용 여부',
            'is_expired' => '만료 여부',
            'title' => '주문 목록',
            'update_conflict' => '업데이트 실패: 주문 충돌',
        ],
        'rule' => [
            'clear_all' => '모든 기록 지우기',
            'clear_confirm' => '모든 트리거 기록을 지우시겠습니까?',
            'created_at' => '트리거 시간',
            'name' => '트리거된 규칙 이름',
            'reason' => '트리거 이유',
            'tag' => '✅ 허가되지 않은 콘텐츠 접근',
            'title' => '규칙 트리거 기록',
        ],
        'subscribe' => '구독 목록',
        'user_data_modify_title' => '데이터 변경 기록',
        'user_ip' => [
            'connect' => '연결된 IP',
            'title' => '사용자 온라인 IP 목록 <small>최근 10분</small>',
        ],
        'user_traffic' => [
            'choose_node' => '노드 선택',
            'title' => '데이터 사용 기록',
        ],
    ],
    'marketing' => [
        'counts' => '총 <code>:num</code> 메시지',
        'email' => [
            'ever_paid' => '결제됨',
            'expired_date' => '만료된 날짜',
            'filters' => '필터',
            'loading_statistics' => '통계 정보를 로드 중...',
            'never_paid' => '결제하지 않음',
            'paid_servicing' => '유료 서비스',
            'previously_paid' => '이전에 결제됨',
            'recent_traffic_abnormal' => '최근 1시간 내 트래픽 이상',
            'recently_active' => '최근 활동',
            'targeted_users_count' => '대상 사용자 수',
            'traffic_usage_over' => '트래픽 사용량이 N%를 초과함',
            'will_expire_date' => '만료될 날짜',
        ],
        'email_send' => '이메일 그룹 발송',
        'error_message' => '오류 메시지',
        'processed' => '요청 처리됨',
        'push_send' => '푸시 메시지 발송',
        'send_status' => '발송 상태',
        'send_time' => '발송 시간',
        'targeted_users_not_found' => '대상 사용자를 찾을 수 없음',
        'unknown_sending_type' => '알 수 없는 발송 유형',
    ],
    'massive_export' => '대량 내보내기',
    'menu' => [
        'analysis' => [
            'accounting' => '회계',
            'attribute' => '데이터 분석',
            'node_flow' => '노드 트래픽 분석',
            'site_flow' => '사이트 트래픽',
            'user_flow' => '사용자 트래픽',
        ],
        'customer_service' => [
            'article' => '기사 관리',
            'attribute' => '고객 서비스 시스템',
            'marketing' => '메시지 방송',
            'ticket' => '서비스 티켓',
        ],
        'dashboard' => '관리 센터',
        'log' => [
            'attribute' => '로그 시스템',
            'notify' => '알림 기록',
            'online_logs' => '온라인 기록',
            'online_monitor' => '온라인 모니터링',
            'payment_callback' => '결제 콜백',
            'service_ban' => '차단 기록',
            'system' => '시스템 로그',
            'traffic' => '트래픽 사용',
            'traffic_flow' => '트래픽 변동',
        ],
        'node' => [
            'attribute' => '노드 시스템',
            'auth' => '노드 인증',
            'cert' => '인증서 목록',
            'list' => '노드 관리',
        ],
        'promotion' => [
            'attribute' => '프로모션',
            'invite' => '초대 관리',
            'rebate_flow' => '리베이트 기록',
            'withdraw' => '출금 관리',
        ],
        'rbac' => [
            'attribute' => '권한 시스템',
            'permission' => '권한 관리',
            'role' => '역할 목록',
        ],
        'rule' => [
            'attribute' => '감사 규칙',
            'group' => '규칙 그룹',
            'list' => '규칙 목록',
            'trigger' => '트리거 기록',
        ],
        'setting' => [
            'attribute' => '시스템 설정',
            'email_suffix' => '이메일 접미사 관리',
            'system' => '시스템 매개변수',
            'universal' => '일반 설정',
        ],
        'shop' => [
            'attribute' => '상품 시스템',
            'coupon' => '쿠폰 관리',
            'goods' => '상품 관리',
            'order' => '상품 주문',
        ],
        'tools' => [
            'analysis' => '로그 분석',
            'attribute' => '도구 모음',
            'convert' => '포맷 변환',
            'decompile' => '디컴파일',
            'import' => '데이터 가져오기',
        ],
        'user' => [
            'attribute' => '사용자 시스템',
            'credit_log' => '잔액 변동 기록',
            'group' => '사용자 그룹',
            'list' => '사용자 관리',
            'oauth' => '제3자 인증',
            'subscribe' => '구독 관리',
        ],
    ],
    'minute' => '분',
    'monitor' => [
        'daily_chart' => '일일 트래픽 사용량',
        'hint' => '<strong>힌트:</strong> 데이터가 없으면 예약 작업을 확인하십시오',
        'monthly_chart' => '월간 트래픽 사용량',
        'node' => '노드 트래픽',
        'user' => '사용자 트래픽',
    ],
    'no' => '아니요',
    'node' => [
        'auth' => [
            'counts' => '총 <code>:num</code> 인증 기록',
            'deploy' => [
                'attribute' => '백엔드 배포',
                'command' => '명령어',
                'real_time_logs' => '실시간 로그',
                'recent_logs' => '최근 로그',
                'restart' => '재시작',
                'same' => '위와 동일',
                'start' => '시작',
                'status' => '상태',
                'stop' => '중지',
                'title' => ':type_label 백엔드 배포',
                'trojan_hint' => '먼저 <a href=":url" target="_blank">노드 도메인</a>을 입력하고 해당 IP로 해석하십시오',
                'uninstall' => '제거',
                'update' => '업데이트',
            ],
            'empty' => '권한 생성을 필요로 하는 노드가 없습니다',
            'generating_all' => '모든 노드에 대해 인증 키를 생성하시겠습니까?',
            'reset_auth' => '인증 키 재설정',
            'title' => '노드 인증 <small>WEBAPI</small>',
        ],
        'cert' => [
            'counts' => '총 <code>:num</code> 도메인 인증서',
            'key_placeholder' => '인증서의 KEY 값, 비워둘 수 있으며 VNET-V2Ray 백엔드는 자동 발급을 지원합니다',
            'pem_placeholder' => '인증서의 PEM 값, 비워둘 수 있으며 VNET-V2Ray 백엔드는 자동 발급을 지원합니다',
            'title' => '도메인 인증서 <small>(V2Ray 노드 위장용)</small>',
        ],
        'connection_test' => '연결성 테스트',
        'counts' => '총 <code>:num</code> 노드',
        'info' => [
            'additional_ports_hint' => '활성화된 경우, 서버의 <span class="red-700"><a href="javascript:showTnc();">additional_ports</a></span> 정보를 설정하십시오',
            'basic' => '기본 정보',
            'data_rate_hint' => '예: 0.1은 100M를 10M로 계산, 5는 100M를 500M로 계산',
            'ddns_hint' => '동적 IP 노드는 <a href="https://github.com/NewFuture/DDNS" target="_blank">DDNS 설정</a>이 필요합니다. 이 유형의 노드는 도메인 이름을 통해 노드 차단 감지를 수행합니다.',
            'detection' => [
                'all' => '모두 감지',
                'hint' => '30~60분마다 무작위로 노드 차단 감지',
                'icmp' => 'ICMP 연결성만 감지',
                'tcp' => 'TCP 연결성만 감지',
            ],
            'display' => [
                'all' => '완전히 보임',
                'hint' => '사용자가 이 노드를 구독/볼 수 있는지 여부',
                'invisible' => '완전히 보이지 않음',
                'node' => '【노드】 페이지에만 표시',
                'sub' => '【구독】에만 표시',
            ],
            'domain_hint' => '시스템에서 【DDNS 모드】를 활성화하면 도메인이 아래의 IP와 자동으로 연결됩니다. 도메인 등록자 웹사이트에서 IP 정보를 수정할 필요가 없습니다.',
            'domain_placeholder' => '서버 도메인 주소, 입력 시 도메인 주소가 우선 사용됩니다',
            'extend' => '확장 정보',
            'hint' => '<strong>주의:</strong> 노드 추가 후 자동 생성된 <code>ID</code>는 ShadowsocksR Python 버전 백엔드의 <code>usermysql.json</code> 파일의 <code>node_id</code> 값이며, V2Ray 백엔드의 <code>nodeId</code> 값입니다.',
            'ipv4_hint' => '여러 IP는 영어 쉼표로 구분합니다. 예: 1.1.1.1,8.8.8.8',
            'ipv4_placeholder' => '서버 IPv4 주소',
            'ipv6_hint' => '여러 IP는 영어 쉼표로 구분합니다. 예: 1.1.1.1,8.8.8.8',
            'ipv6_placeholder' => '서버 IPv6 주소',
            'level_hint' => '레벨: 0 - 레벨 제한 없음, 모두 보임',
            'obfs_param_hint' => '혼란이 [plain]이 아닐 때 트래픽 위장을 위한 매개변수를 입력합니다. &#13;&#10;혼란이 [http_simple]일 때, 포트 80을 권장합니다. &#13;&#10;혼란이 [tls]일 때, 포트 443을 권장합니다.',
            'push_port_hint' => '필수 항목이며 방화벽 포트를 열어야 합니다. 그렇지 않으면 메시지 푸시가 비정상적으로 동작합니다.',
            'single_hint' => '권장 포트는 80 또는 443입니다. 서버는 <br> 엄격 모드 구성: 사용자는 지정된 포트를 통해서만 연결할 수 있습니다. (<a href="javascript:showPortsOnlyConfig();">구성 방법</a>)',
            'v2_cover' => [
                'dtls' => 'DTLS1.2 데이터 패킷',
                'http' => 'HTTP 데이터 스트림',
                'none' => '위장 없음',
                'srtp' => '비디오 통화 데이터 (SRTP)',
                'utp' => 'BT 다운로드 데이터 (uTP)',
                'wechat' => '위챗 비디오 통화 데이터',
                'wireguard' => 'WireGuard 데이터 패킷',
            ],
            'v2_host_hint' => 'http 위장 시 여러 도메인은 쉼표로 구분하고, WebSocket은 단일 도메인만 허용합니다.',
            'v2_method_hint' => 'WebSocket 전송 프로토콜은 none 암호화 방식을 사용하지 마십시오.',
            'v2_net_hint' => 'WebSocket을 위해 TLS를 활성화하십시오',
            'v2_tls_provider_hint' => '다른 백엔드는 다른 구성을 가집니다:',
        ],
        'proxy_info' => '*SS 프로토콜과 호환',
        'proxy_info_hint' => '호환성을 위해 서버 구성에서 프로토콜과 혼란에 <span class="red-700">_compatible</span>을 추가하십시오',
        'refresh_geo' => '지리 정보 새로고침',
        'refresh_geo_all' => '【모든】 노드 지리 정보 새로고침',
        'reload' => '백엔드 다시 로드',
        'reload_all' => '【모든】 백엔드 다시 로드',
        'reload_confirm' => '노드 백엔드를 다시 로드하시겠습니까?',
        'traffic_monitor' => '트래픽 통계',
    ],
    'oauth' => [
        'counts' => '총 <code>:num</code> 인증 기록',
        'title' => '제3자 인증',
    ],
    'optional' => '선택 사항',
    'permission' => [
        'counts' => '총 <code>:num</code> 권한 행동',
        'description_hint' => '설명 작성, 예: [A 시스템] A 편집',
        'name_hint' => '라우트 이름 작성, 예: admin.permission.create,update',
        'title' => '권한 행동 목록',
    ],
    'query' => '쿼리',
    'report' => [
        'annually_accounting' => '연간 거래',
        'annually_site_flow' => '연간 트래픽 소비',
        'avg_traffic_30d' => '30일 평균 일일 트래픽',
        'current_month' => '이번 달',
        'current_year' => '올해',
        'daily_accounting' => '일일 거래',
        'daily_distribution' => '일일 분포',
        'daily_site_flow' => '일일 트래픽 소비',
        'daily_traffic' => '일일 트래픽',
        'hourly_traffic' => '시간별 트래픽',
        'last_month' => '지난 달',
        'last_year' => '작년',
        'monthly_accounting' => '월간 거래',
        'monthly_site_flow' => '월간 트래픽 소비',
        'select_hourly_date' => '시간별 날짜 선택',
        'sum_traffic_30d' => '30일 총 트래픽 비율',
        'today' => '오늘',
    ],
    'require' => '필수 사항',
    'role' => [
        'counts' => '총 <code>:num</code> 권한 역할',
        'description_hint' => '패널에 표시할 이름, 예: 관리자',
        'modify_admin_error' => '슈퍼 관리자를 수정하지 마세요!',
        'name_hint' => '고유 식별 이름, 예: 관리자',
        'permissions_all' => '모든 권한',
        'title' => '권한 역할 목록',
    ],
    'rule' => [
        'counts' => '총 <code>:num</code> 감사 규칙',
        'group' => [
            'counts' => '총 <code>:num</code> 그룹',
            'title' => '규칙 그룹',
            'type' => [
                'off' => '차단',
                'on' => '허용',
            ],
        ],
        'title' => '규칙 목록',
        'type' => [
            'domain' => '도메인',
            'ip' => 'IP',
            'protocol' => '프로토콜',
            'reg' => '정규 표현식',
        ],
    ],
    'select_all' => '전체 선택',
    'selected_hint' => '할당된 규칙을 여기서 검색할 수 있습니다',
    'set_to' => ':attribute로 설정',
    'setting' => [
        'common' => [
            'connect_nodes' => '연결된 노드 수',
            'set_default' => '기본값으로 설정',
            'title' => '일반 설정',
        ],
        'email' => [
            'black' => '블랙리스트',
            'rule' => '제한 유형',
            'tail' => '이메일 접미사',
            'tail_placeholder' => '이메일 접미사를 입력하세요',
            'title' => '이메일 필터 목록 <small>(등록 시 사용)</small>',
            'white' => '화이트리스트',
        ],
        'no_permission' => '매개변수를 수정할 권한이 없습니다!',
        'system' => [
            'account' => '계정 설정',
            'auto_job' => '자동 작업',
            'check_in' => '체크인 시스템',
            'extend' => '확장 기능',
            'menu' => '메뉴',
            'node' => '노드 설정',
            'notify' => '알림 시스템',
            'other' => '로고|고객 서비스|통계',
            'payment' => '결제 시스템',
            'promotion' => '프로모션 시스템',
            'title' => '시스템 설정',
            'web' => '웹사이트 일반',
        ],
    ],
    'sort_asc' => '정렬 값이 클수록 우선순위가 높습니다',
    'start_time' => '시작 시간',
    'system' => [
        'AppStore_id' => '애플 계정',
        'AppStore_password' => '애플 비밀번호',
        'account_expire_notification' => '계정 만료 알림',
        'active_account' => [
            'after' => '등록 후 활성화',
            'before' => '등록 전 활성화',
        ],
        'active_times' => '계정 활성화 횟수',
        'admin_invite_days' => '관리자 초대 유효 기간',
        'aff_salt' => '초대 링크 사용자 정보 암호화',
        'alipay_qrcode' => '알리페이 QR 코드',
        'auto_release_port' => '포트 회수 메커니즘',
        'bark_key' => 'Bark 장치 키',
        'captcha' => [
            'geetest' => 'Geetest',
            'hcaptcha' => 'hCaptcha',
            'recaptcha' => 'Google ReCaptcha',
            'standard' => '일반 캡차',
            'turnstile' => 'Turnstile',
        ],
        'captcha_key' => '캡차 키',
        'captcha_secret' => '캡차 시크릿/ID',
        'codepay_id' => '코드페이 ID',
        'codepay_key' => '통신 키',
        'codepay_url' => '요청 URL',
        'data_anomaly_notification' => '데이터 이상 알림',
        'data_exhaust_notification' => '데이터 소진 알림',
        'ddns_key' => 'DNS 서비스 제공자 키',
        'ddns_mode' => 'DDNS 모드',
        'ddns_secret' => 'DNS 서비스 제공자 시크릿',
        'default_days' => '기본 유효 기간',
        'default_traffic' => '기본 초기 데이터',
        'demo_restriction' => '데모 환경에서는 이 구성 변경이 금지되어 있습니다!',
        'detection_check_times' => '차단 감지 알림',
        'dingTalk_access_token' => '딩톡 커스텀 봇 액세스 토큰',
        'dingTalk_secret' => '딩톡 커스텀 봇 시크릿',
        'epay_key' => '상점 시크릿 키',
        'epay_mch_id' => '상점 ID',
        'epay_url' => '인터페이스 접속 주소',
        'expire_days' => '만료 경고 임계값',
        'f2fpay_app_id' => '애플리케이션 ID',
        'f2fpay_private_key' => '애플리케이션 개인 키',
        'f2fpay_public_key' => '알리페이 공개 키',
        'forbid' => [
            'china' => '중국 차단',
            'mainland' => '중국 본토 차단',
            'oversea' => '해외 차단',
        ],
        'forbid_mode' => '접근 제한 모드',
        'hint' => [
            'AppStore_id' => 'iOS 소프트웨어 설정 가이드에 사용된 애플 계정',
            'AppStore_password' => 'iOS 소프트웨어 설정 가이드에 사용된 애플 비밀번호',
            'account_expire_notification' => '사용자에게 계정 만료 알림',
            'active_times' => '24시간 내 이메일을 통해 계정 활성화 횟수',
            'admin_invite_days' => '관리자가 생성한 초대 코드의 유효 기간',
            'aff_salt' => '비워두면 초대 링크에 사용자 ID가 표시됩니다; 임의의 영문/숫자를 입력하면 사용자 링크 ID가 암호화됩니다',
            'auto_release_port' => '차단/만료된 계정의 포트를 자동으로 회수합니다 <code>'.sysConfig('tasks.release_port').'</code> 일 후 자동으로 포트를 회수합니다',
            'bark_key' => 'iOS 장치로 푸시 메시지를 보내려면 Bark라는 앱을 설치하고, URL 뒤에 있는 긴 문자열을 입력해야 합니다. Bark를 활성화하려면 반드시 이 값을 입력해야 합니다.',
            'captcha_key' => '설정 가이드를 보려면 <a href="https://proxypanel.gitbook.io/wiki/captcha" target="_blank">여기</a>를 클릭하세요',
            'data_anomaly_notification' => '1시간 내 데이터 이상 임계값을 초과하면 관리자에게 알림',
            'data_exhaust_notification' => '데이터가 소진되기 전에 사용자에게 알림',
            'ddns_key' => '설정 가이드를 보려면 <a href="https://proxypanel.gitbook.io/wiki/ddns" target="_blank">여기</a>를 클릭하세요',
            'ddns_mode' => '노드의 도메인, IPv4, IPv6를 추가/편집/삭제할 때 자동으로 DNS 제공자에 업데이트',
            'default_days' => '사용자 등록 시 기본 계정 유효 기간, 0이면 당일 만료',
            'default_traffic' => '사용자 등록 시 기본 데이터 용량',
            'detection_check_times' => 'N회 알림 후 자동으로 노드를 오프라인 상태로 전환, 0 또는 비워두면 무제한, 최대 12회',
            'dingTalk_access_token' => '커스텀 봇 액세스 토큰을 보려면 <a href="https://open.dingtalk.com/document/group/custom-robot-access#title-jfe-yo9-jl2" target="_blank">딩톡 매뉴얼</a>을 참조하세요',
            'dingTalk_secret' => '선택 사항! 봇 서명 기능을 활성화하면 필수 항목!',
            'expire_days' => '계정 만료 알림 시작 임계값, 매일 사용자에게 알림',
            'f2fpay_app_id' => '애플리케이션 ID',
            'f2fpay_private_key' => '키 생성 소프트웨어로 생성된 애플리케이션 개인 키',
            'f2fpay_public_key' => '애플리케이션 공개 키가 아닙니다!',
            'forbid_mode' => '지정된 지역의 IP를 차단, 차단되지 않은 지역은 정상 접근 가능',
            'iYuu_token' => 'IYUU를 활성화하려면 반드시 이 값을 입력해야 합니다(<a href="https://iyuu.cn" target="_blank">IYUU 토큰 신청</a>)',
            'invite_num' => '사용자가 기본적으로 초대할 수 있는 인원 수',
            'is_activate_account' => '활성화 후 사용자가 이메일을 통해 계정을 활성화해야 합니다',
            'is_ban_status' => '(신중하게) 전체 계정을 차단하면 모든 데이터가 초기화되고 사용자가 로그인할 수 없게 됩니다. 활성화하지 않으면 사용자 에이전트만 차단됩니다.',
            'is_captcha' => '활성화 후 로그인/등록 시 캡차 인증 필요',
            'is_checkin' => '로그인 시 데이터 범위에 따라 무작위로 데이터를 받습니다',
            'is_clear_log' => '(추천) 활성화 후 불필요한 로그를 자동으로 삭제',
            'is_custom_subscribe' => '활성화 후 구독 정보 상단에 만료 시간 및 남은 데이터가 표시됩니다(일부 클라이언트만 지원)',
            'is_email_filtering' => '블랙리스트: 사용자가 블랙리스트에 없는 이메일로 등록 가능; 화이트리스트: 사용자가 화이트리스트에 있는 이메일 도메인으로만 등록 가능',
            'is_forbid_robot' => '로봇, 크롤러, 프록시가 웹사이트에 접근하면 404 오류 발생',
            'is_free_code' => '비활성화 시 무료 초대 코드가 보이지 않음',
            'is_rand_port' => '사용자 등록/추가 시 랜덤 포트 생성',
            'is_register' => '비활성화 시 등록 불가',
            'is_subscribe_ban' => '활성화 후 사용자 구독 링크 요청이 설정된 임계값을 초과하면 자동 차단',
            'is_traffic_ban' => '1시간 내 데이터 이상 임계값을 초과하면 자동으로 계정 차단(프록시만 차단)',
            'maintenance_content' => '맞춤 유지 보수 내용 정보',
            'maintenance_mode' => '활성화 후 사용자가 유지 보수 페이지로 이동 | 관리자는 <a href=\'javascript:(0)\'>:url</a>로 로그인',
            'maintenance_time' => '유지 보수 페이지 카운트다운에 사용',
            'min_port' => '포트 범위: 1000~65535',
            'node_blocked_notification' => '매 시간 노드 차단 여부를 감지하고 관리자에게 알림',
            'node_daily_notification' => '각 노드의 전날 데이터 사용량 보고',
            'node_offline_notification' => '10분마다 노드 오프라인 상태를 감지하고 관리자에게 알림',
            'node_renewal_notification' => '노드 만료 7일, 3일, 1일 전에 관리자가 갱신하도록 알립니다.',
            'oauth_path' => '.ENV에 설정을 추가한 후 이곳에서 플랫폼을 활성화하세요',
            'offline_check_times' => '24시간 내 n회 알림 후 더 이상 알림 없음',
            'password_reset_notification' => '활성화 후 사용자가 비밀번호를 재설정할 수 있습니다',
            'paybeaver_app_id' => '<a href="https://merchant.paybeaver.com/" target="_blank">상점 센터</a> -> 개발자 -> 앱 ID',
            'paybeaver_app_secret' => '<a href="https://merchant.paybeaver.com/" target="_blank">상점 센터</a> -> 개발자 -> 앱 시크릿',
            'payjs_mch_id' => '<a href="https://payjs.cn/dashboard/member" target="_blank">여기</a>에서 정보를 얻으세요',
            'payment_confirm_notification' => '사용자가 수동 결제를 사용한 후 관리자가 주문을 처리하도록 알림',
            'payment_received_notification' => '사용자가 결제 후 주문 상태를 알림',
            'pushDeer_key' => 'PushDeer를 활성화하려면 반드시 이 값을 입력하세요(<a href="https://www.pushdeer.com/official.html" target="_blank">PushDeer 키 신청</a>)',
            'pushplus_token' => 'PushPlus를 활성화하려면 반드시 이 값을 입력하세요(<a href="https://www.pushplus.plus/push1.html" target="_blank">PushPlus 토큰 신청</a>)',
            'rand_subscribe' => '활성화 후 구독 시 노드 정보를 무작위로 반환, 그렇지 않으면 노드 순서대로 반환',
            'redirect_url' => '감사 규칙이 트리거되면 접근 요청을 이 주소로 리디렉션',
            'referral_money' => '출금 신청 가능한 최소 금액',
            'referral_percent' => '초대 링크를 통해 등록된 계정의 각 주문 금액에서 초대자가 받을 수 있는 비율',
            'referral_status' => '비활성화 시 사용자에게 보이지 않지만 기존 초대 리베이트에는 영향을 미치지 않음',
            'referral_traffic' => '초대 링크 또는 초대 코드를 통해 등록 시 해당 데이터 제공',
            'referral_type' => '모드를 전환한 후 기존 데이터는 변경되지 않으며, 새로운 리베이트는 새로운 모드에 따라 계산됩니다',
            'register_ip_limit' => '24시간 내 동일 IP에서 허용되는 등록 수, 0 또는 비워두면 무제한',
            'reset_password_times' => '24시간 내 이메일을 통해 비밀번호 재설정 가능한 횟수',
            'reset_traffic' => '사용자가 구매한 패키지의 날짜에 따라 자동으로 데이터 재설정',
            'server_chan_key' => 'ServerChan을 활성화하려면 반드시 이 값을 입력하세요(<a href="https://sct.ftqq.com/r/2626" target="_blank">SCKEY 신청</a>)',
            'standard_currency' => '웹사이트에서 사용되는 기본 통화',
            'subject_name' => '결제 채널에서 표시되는 상품 제목',
            'subscribe_ban_times' => '24시간 내 구독 링크 요청 횟수 제한',
            'subscribe_domain' => 'DNS 독을 방지하기 위해 http:// 또는 https://로 시작해야 합니다',
            'subscribe_max' => '클라이언트 구독 시 반환되는 최대 노드 수, 0 또는 비워두면 모든 노드 반환',
            'telegram_token' => '로봇 <a href="https://t.me/BotFather" target="_blank">@BotFather</a>에서 TOKEN을 신청하세요',
            'tg_chat_token' => 'TG Chat을 활성화하려면 반드시 이 값을 입력하세요(<a href="https://t.me/realtgchat_bot" target="_blank">Token 신청</a>)',
            'ticket_closed_notification' => '티켓이 종료되면 사용자에게 알림',
            'ticket_created_notification' => '새 티켓이 생성되면 관리자/사용자에게 알림',
            'ticket_replied_notification' => '티켓에 응답하면 상대방에게 알림',
            'traffic_ban_time' => '이상으로 인해 계정/구독이 차단되는 기간, 만료 후 자동 해제',
            'traffic_ban_value' => '1시간 내 이 값을 초과하면 자동으로 계정 차단',
            'traffic_limit_time' => '체크인 간격 시간',
            'traffic_warning_percent' => '데이터 소진 알림 시작 임계값, 매일 사용자에게 알림',
            'user_invite_days' => '사용자가 생성한 초대 코드의 유효 기간',
            'username_type' => '사이트 사용자 계정 유형, 기본값은 이메일',
            'v2ray_tls_provider' => '백엔드에서 자동으로 TLS 인증서를 발급/로드할 때 사용(노드 설정 값이 이 설정보다 우선)',
            'web_api_url' => '예: '.config('app.url'),
            'webmaster_email' => '오류 메시지 표시 시 관리자 이메일을 연락처로 제공',
            'website_analytics' => '분석 JS 코드',
            'website_callback_url' => '웹사이트 도메인이 DNS 독에 의해 감염된 경우 결제 콜백 실패를 방지하기 위해 http:// 또는 https://로 시작해야 합니다',
            'website_customer_service' => '고객 서비스 JS 코드',
            'website_name' => '이메일 발송 시 표시되는 웹사이트 이름',
            'website_security_code' => '비워두지 않으면 <a href=":url" target="_blank">보안 입구</a>를 통해 보안 코드로 접근해야 합니다',
            'website_url' => '비밀번호 재설정, 온라인 결제 생성에 필수',
            'wechat_aid' => '응용 프로그램 관리에서 자가 생성 응용 프로그램 - AgentId',
            'wechat_cid' => '내 기업에서 기업 ID 얻기',
            'wechat_encodingAESKey' => '응용 프로그램 관리 -> 응용 프로그램 -> API 수신 설정 -> EncodingAESKey',
            'wechat_secret' => '응용 프로그램 시크릿(기업 위챗을 다운로드해야 볼 수 있음)',
            'wechat_token' => '응용 프로그램 관리 -> 응용 프로그램 -> API 수신 설정 -> TOKEN, URL 설정: :url',
        ],
        'iYuu_token' => 'IYUU 토큰',
        'invite_num' => '기본 초대 가능 인원',
        'is_AliPay' => '알리페이 결제',
        'is_QQPay' => 'QQ 지갑',
        'is_WeChatPay' => '위챗 결제',
        'is_activate_account' => '계정 활성화',
        'is_ban_status' => '만료 시 자동 차단',
        'is_captcha' => '캡차 모드',
        'is_checkin' => '체크인 데이터 추가',
        'is_clear_log' => '로그 자동 삭제',
        'is_custom_subscribe' => '고급 구독',
        'is_email_filtering' => '이메일 필터링 메커니즘',
        'is_forbid_robot' => '로봇 접근 차단',
        'is_free_code' => '무료 초대 코드',
        'is_invite_register' => '초대 등록',
        'is_otherPay' => '특수 결제',
        'is_rand_port' => '랜덤 포트',
        'is_register' => '사용자 등록',
        'is_subscribe_ban' => '이상 구독 요청 자동 차단',
        'is_traffic_ban' => '이상 데이터 사용 자동 차단',
        'maintenance_content' => '유지 보수 내용',
        'maintenance_mode' => '유지 보수 모드',
        'maintenance_time' => '유지 보수 종료 시간',
        'min_port' => '포트 범위',
        'min_rand_traffic' => '데이터 범위',
        'node_blocked_notification' => '노드 차단 알림',
        'node_daily_notification' => '노드 사용 보고서',
        'node_offline_notification' => '노드 오프라인 알림',
        'node_renewal_notification' => '노드 갱신 알림',
        'notification' => [
            'channel' => [
                'bark' => 'Bark',
                'dingtalk' => '딩톡',
                'email' => '이메일',
                'iyuu' => 'IYUU',
                'pushdeer' => 'PushDeer',
                'pushplus' => 'PushPlus',
                'serverchan' => 'ServerChan',
                'site' => '사이트 팝업',
                'telegram' => '텔레그램',
                'tg_chat' => 'TG Chat',
                'wechat' => '기업 위챗',
            ],
            'send_test' => '테스트 메시지 보내기',
            'test' => [
                'content' => '테스트 내용',
                'success' => '성공적으로 전송되었습니다. 휴대폰에서 푸시 알림을 확인하세요.',
                'title' => '이것은 테스트 제목입니다',
                'unknown_channel' => '알 수 없는 채널',
            ],
        ],
        'oauth_path' => '타사 로그인 플랫폼',
        'offline_check_times' => '오프라인 알림 횟수',
        'params_required' => '먼저 이 :attribute의 필수 매개변수를 완료해 주세요!',
        'password_reset_notification' => '비밀번호 재설정 알림',
        'paybeaver_app_id' => '앱 ID',
        'paybeaver_app_secret' => '앱 시크릿',
        'payjs_key' => '통신 키',
        'payjs_mch_id' => '상점 번호',
        'payment' => [
            'attribute' => '결제 설정',
            'channel' => [
                'alipay' => '알리페이 F2F',
                'codepay' => '코드페이',
                'epay' => 'ePay',
                'manual' => '수동 결제',
                'paybeaver' => 'PayBeaver',
                'payjs' => 'PayJs',
                'paypal' => 'PayPal',
                'stripe' => 'Stripe',
                'theadpay' => 'THeadPay',
            ],
            'hint' => [
                'alipay' => '이 기능은 <a href="https://open.alipay.com/platform/appManage.htm?#/create/" target="_blank">알리페이 오픈 플랫폼</a>에서 권한 및 애플리케이션을 신청해야 합니다',
                'codepay' => '<a href="https://codepay.fateqq.com/i/377289" target="_blank">코드페이</a>에서 계정을 신청한 후 소프트웨어를 다운로드하고 설정합니다',
                'manual' => '게이트웨이를 설정하고 선택하면 사용자 인터페이스에 표시됩니다',
                'paybeaver' => '<a href="https://merchant.paybeaver.com/?aff_code=iK4GNuX8" target="_blank">PayBeaver</a>에서 계정을 신청하세요',
                'payjs' => '<a href="https://payjs.cn/ref/zgxjnb" target="_blank">PayJs</a>에서 계정을 신청하세요',
                'paypal' => '<a href="https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty" target="_blank">API 자격 증명 신청 페이지</a>에 상점 계정으로 로그인하여 설정 정보를 얻으세요',
                'theadpay' => '<a href="https://theadpay.com/" target="_blank">THeadPay</a>에서 계정을 신청하세요',
            ],
        ],
        'payment_confirm_notification' => '수동 결제 확인 알림',
        'payment_received_notification' => '결제 성공 알림',
        'paypal_app_id' => '앱 ID',
        'paypal_client_id' => '클라이언트 ID',
        'paypal_client_secret' => '클라이언트 시크릿 키',
        'placeholder' => [
            'bark_key' => 'Bark 장치 키 입력 -> 업데이트 클릭',
            'codepay_url' => 'https://codepay.fateqq.com/creat_order/?',
            'default_url' => '기본값: :url',
            'dingTalk_access_token' => '커스텀 봇 WebHook의 access_token',
            'dingTalk_secret' => '커스텀 봇 서명 후 나타나는 시크릿',
            'iYuu_token' => 'IYUU 토큰 입력 -> 업데이트 클릭',
            'pushDeer_key' => 'PushDeer Push Key 입력 -> 업데이트 클릭',
            'pushplus_token' => 'ServerChan에서 신청',
            'server_chan_key' => 'ServerChan SCKEY 입력 -> 업데이트 클릭',
            'telegram_token' => 'Telegram 토큰 입력 -> 업데이트 클릭',
            'tg_chat_token' => 'Telegram에서 신청',
            'wechat_aid' => '응용 프로그램의 AgentId',
            'wechat_cid' => 'WeChat 기업 ID 입력 -> 업데이트 클릭',
            'wechat_secret' => '응용 프로그램의 시크릿',
        ],
        'pushDeer_key' => 'PushDeer 키',
        'pushplus_token' => 'PushPlus 토큰',
        'rand_subscribe' => '랜덤 구독',
        'redirect_url' => '리디렉션 URL',
        'referral' => [
            'loop' => '반복 리베이트',
            'once' => '첫 구매 리베이트',
        ],
        'referral_money' => '출금 제한',
        'referral_percent' => '리베이트 비율',
        'referral_status' => '프로모션 기능',
        'referral_traffic' => '가입 시 데이터 제공',
        'referral_type' => '리베이트 모드',
        'register_ip_limit' => '동일 IP 등록 제한',
        'reset_password_times' => '비밀번호 재설정 횟수',
        'reset_traffic' => '데이터 자동 재설정',
        'server_chan_key' => 'ServerChan SCKEY',
        'standard_currency' => '기본 통화',
        'stripe_public_key' => '공개 키',
        'stripe_secret_key' => '시크릿 키',
        'stripe_signing_secret' => '웹훅 서명 시크릿',
        'subject_name' => '맞춤 상품 이름',
        'subscribe_ban_times' => '구독 요청 임계값',
        'subscribe_domain' => '노드 구독 주소',
        'subscribe_max' => '구독 노드 수',
        'telegram_token' => '텔레그램 토큰',
        'tg_chat_token' => 'TG Chat 토큰',
        'theadpay_key' => '상점 시크릿 키',
        'theadpay_mchid' => '상점 ID',
        'theadpay_url' => '인터페이스 주소',
        'ticket_closed_notification' => '티켓 종료 알림',
        'ticket_created_notification' => '새 티켓 알림',
        'ticket_replied_notification' => '티켓 응답 알림',
        'traffic_ban_time' => '차단 기간',
        'traffic_ban_value' => '데이터 이상 임계값',
        'traffic_limit_time' => '시간 간격',
        'traffic_warning_percent' => '데이터 경고 임계값',
        'trojan_license' => 'Trojan 라이센스',
        'user_invite_days' => '사용자 초대 유효 기간',
        'username' => [
            'any' => '임의 사용자 이름',
            'email' => '이메일',
            'mobile' => '전화번호',
        ],
        'username_type' => '사용자 이름 유형',
        'v2ray_license' => 'V2Ray 라이센스',
        'v2ray_tls_provider' => 'V2Ray TLS 설정',
        'web_api_url' => 'API 접근 도메인',
        'webmaster_email' => '관리자 이메일',
        'website_analytics' => '웹사이트 분석 코드',
        'website_callback_url' => '일반 결제 콜백 주소',
        'website_customer_service' => '웹사이트 고객 서비스 코드',
        'website_home_logo' => '홈페이지 로고',
        'website_logo' => '웹사이트 로고',
        'website_name' => '웹사이트 이름',
        'website_security_code' => '웹사이트 보안 코드',
        'website_url' => '웹사이트 주소',
        'wechat_aid' => '위챗 앱 ID',
        'wechat_cid' => '위챗 기업 ID',
        'wechat_encodingAESKey' => '위챗 앱 EncodingAESKey',
        'wechat_qrcode' => '위챗 QR 코드',
        'wechat_secret' => '위챗 앱 시크릿',
        'wechat_token' => '위챗 앱 토큰',
    ],
    'system_generate' => '시스템 생성',
    'ticket' => [
        'close_confirm' => '이 티켓을 닫으시겠습니까?',
        'counts' => '총 <code>:num</code> 티켓',
        'error' => '알 수 없는 오류! 로그를 확인하십시오',
        'inviter_info' => '초대자 정보',
        'self_send' => '자신에게 티켓을 생성할 수 없습니다!',
        'send_to' => '대상 사용자 정보를 입력하십시오',
        'title' => '티켓 목록',
        'user_info' => '사용자 정보',
    ],
    'times' => '회',
    'tools' => [
        'analysis' => [
            'file_missing' => ':file_name이 존재하지 않습니다. 먼저 파일을 생성하세요.',
            'not_enough' => '15,000개 미만의 기록으로 인해 분석 불가',
            'req_url' => '최근 요청 URL 기록',
            'title' => 'SSR 로그 분석 <small>단일 노드에만 해당</small>',
        ],
        'convert' => [
            'content_placeholder' => '변환할 구성 정보를 입력하십시오.',
            'file_missing' => '파일을 찾을 수 없습니다. 디렉터리 권한을 확인하세요.',
            'missing_error' => '변환 실패: 구성 정보에 [port_password] 필드가 누락되었거나 이 필드가 비어 있습니다.',
            'params_unknown' => '매개변수 예외',
            'title' => '포맷 변환 <small>SS에서 SSR로</small>',
        ],
        'decompile' => [
            'attribute' => '역구성 링크',
            'content_placeholder' => '역구성할 ShadowsocksR 링크를 입력하십시오. 줄 바꿈으로 구분합니다.',
            'title' => '디컴파일 <small>구성 정보</small>',
        ],
        'import' => [
            'file_error' => '알 수 없는 오류가 발생했습니다. 다시 업로드해 주세요.',
            'file_required' => '업로드할 파일을 선택하세요',
            'file_type_error' => ':type 파일만 업로드가 허용됩니다.',
            'format_error' => '콘텐츠 형식 분석 오류가 발생했습니다. 지정된 형식에 맞는 :type 파일을 업로드해 주세요.',
        ],
    ],
    'unselected_hint' => '할당할 규칙을 여기서 검색할 수 있습니다',
    'user' => [
        'admin_deletion' => '시스템 관리자는 삭제할 수 없습니다',
        'bulk_account_quantity' => '일괄 생성된 계정 수',
        'connection_test' => '연결 테스트',
        'counts' => '총 <code>:num</code> 계정',
        'group' => [
            'counts' => '총 <code>:num</code> 그룹',
            'name' => '그룹 이름',
            'title' => '사용자 그룹 제어 <small>（동일 노드는 여러 그룹에 속할 수 있지만, 사용자는 하나의 그룹에만 속할 수 있습니다. 사용자에게 보이는/사용 가능한 노드의 경우, 그룹이 레벨보다 우선합니다）</small>',
        ],
        'info' => [
            'account' => '계정 정보',
            'expired_date_hint' => '비워두면 기본 유효 기간은 1년입니다',
            'proxy' => '프록시 정보',
            'recharge_placeholder' => '음수 입력 시 잔액 차감',
            'reset_date_hint' => '다음 데이터 초기화 날짜',
            'switch' => '역할 전환',
            'uuid_hint' => 'V2Ray의 UUID',
        ],
        'online_monitor' => '온라인 모니터링',
        'proxies_config' => '【:username】의 연결 설정 정보',
        'proxy_info' => '설정 정보',
        'reset_confirm' => [0 => '【', 1 => '】의 트래픽을 초기화하시겠습니까?'],
        'reset_traffic' => '트래픽 초기화',
        'traffic_monitor' => '트래픽 통계',
        'update_help' => '업데이트 성공, 돌아가시겠습니까?',
        'user_view' => '사용자 뷰로 전환',
    ],
    'user_dashboard' => '개인 센터',
    'yes' => '예',
    'zero_unlimited_hint' => '설정하지 않음/0, 무제한',
];
