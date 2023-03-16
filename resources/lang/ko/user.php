<?php

return [
    'account'             => [
        'credit'           => '계정 잔고',
        'status'           => '계정 상태',
        'level'            => '계정 등급',
        'group'            => '소속 그룹',
        'speed_limit'      => '속도 제한',
        'remain'           => '잔여 데이터',
        'time'             => '요금제 기간',
        'last_login'       => '최근 접속',
        'reset'            => '{0} 还有 <code id="restTime">:days</code> 重置流量|[1,*] 还有 :days 天重置流量',
        'connect_password' => '비밀번호 연결',
        'reason'           => [
            'normal'            => '계정이 정상입니다.',
            'expired'           => '고객님 요금제 상품이 만료되었습니다.',
            'overused'          => '本时段使用流量超过 <code>:data</code> GB触发系统限制<br/> <code id="banedTime">:min</code> 后解除限制',
            'traffic_exhausted' => '고객님의 [데이터] 가 소진되었습니다.',
            'unknown'           => '未知原因，请尝试[刷新]你的浏览器！多次无果后再请开工单联系管理',
        ],
    ],
    'home'                => [
        'attendance'         => [
            'attribute' => '출첵',
            'disable'   => '출첵 기능이 활성화되지 않았습니다.',
            'done'      => '출첵완료, 내일도 방문해주세요!',
            'success'   => '고객님은 :data 용량을 받았습니다.',
            'failed'    => '시스템 이상',
        ],
        'traffic_logs'       => '용량 기록',
        'announcement'       => '공지',
        'wechat_push'        => '위챗 공지 발송',
        'chat_group'         => '단톡방',
        'empty_announcement' => '공지 없음',
    ],
    'purchase_to_unlock'  => '서비스 구매 후 잠금 해제',
    'purchase_required'   => '해당 기능은 유료 구매자에게만 제공됩니다!',
    'more'                => '더 보기',
    'attribute'           => [
        'node'    => '회선',
        'data'    => '용량',
        'ip'      => 'IP 주소',
        'isp'     => '사업자',
        'address' => '지역',
    ],
    'purchase_promotion'  => '서둘러 서비스를 구매하세요!',
    'menu'                => [
        'helps'           => '도움',
        'home'            => '메인페이지',
        'invites'         => '초대',
        'invoices'        => '주문',
        'nodes'           => '회선',
        'referrals'       => '홍보',
        'shop'            => '서비스',
        'profile'         => '설정',
        'tickets'         => '문의',
        'admin_dashboard' => '관리',
    ],
    'contact'             => '연락처 정보',
    'oauth'               => [
        'bind_title' => '소셜 계정 연동',
        'not_bind'   => '연동되지않음',
        'bind'       => '연동',
        'rebind'     => '재연동',
        'unbind'     => '연동 해제',
    ],
    'coupon'              => [
        'attribute' => '할인쿠폰',
        'voucher'   => '상품권',
        'recharge'  => '충전권',
        'discount'  => '할인',
        'error'     => [
            'unknown'  => '유효하지 않은 쿠폰',
            'used'     => '이미 사용된 쿠폰',
            'expired'  => '쿠폰이 만료됨',
            'run_out'  => '쿠폰 소진',
            'inactive' => '쿠폰이 적용되지 않음',
            'wait'     => ':time  에 활성화 됩니다. 기다려주세요!',
            'unmet'    => '사용 조건 불충족',
            'minimum'  => '本券最低使用金额为 :amount',
            'overused' => '本券只能使用 :times 次',
            'users'    => '账户不符合促销条件',
            'services' => '商品不符合折扣条件，请查看促销条款',
        ],
    ],
    'error_response'      => '오류가 발생했습니다. 나중에 시도하십시오.',
    'invite'              => [
        'attribute'       => '초대 코드',
        'counts'          => '共 <code>:num</code> 个邀请码',
        'tips'            => '<strong>: num </strong > 초대장 코드를 생성할 수 있습니다.: days일 동안 유효합니다.',
        'logs'            => '초대 기록',
        'promotion'       => '당신의 초대 코드를 통해 등록하고 활성화하면, 양쪽 모두 <mark>:traffic</mark> 데이터 사용 인센티브를 받게 됩니다. 그들이 소비할 때, 당신은 그들의 소비 금액 <mark>:referral_percent%</mark>을 보상으로 받습니다.',
        'generate_failed' => '생성 실패: 초대코드 생성 불가',
    ],
    'reset_data'          => [
        ''          => '데이터 재설정',
        'required'  => '필요',
        'cost_tips' => '本次重置流量将扣除余额 :amount！',
        'lack'      => '잔액 부족, 잔액을 충전해주세요',
        'logs'      => '사용자 직접 용량을 초기화',
        'success'   => '초기화 성공',
    ],
    'referral'            => [
        'link'    => '홍보 링크',
        'total'   => '合计返利 :amount（ :total 次），满 :money 可以申请提现。',
        'logs'    => '수수료 기록',
        'failed'  => '신청 실패',
        'success' => '신청 성공',
        'msg'     => [
            'account'     => '계정이 만료됨, 다시 서비스를 구매해주세요.',
            'applied'     => '이미 신청이 존재하므로  이전 신청이 완료될 때까지 기다려주세요.',
            'unfulfilled' => '满 :amount 才可以提现，继续努力吧',
            'wait'        => '관리자의 승인을 기다려주세요',
            'error'       => '반환 실패, 나중에 다시 시도하거나 관리자에게 문의해주세요',
        ],
    ],
    'inviter'             => '초대자',
    'invitee'             => '초대 받은 사람',
    'registered_at'       => '회원가입 시간',
    'bought_at'           => '구매 날짜',
    'payment_method'      => '결제 방식',
    'pay'                 => '결제',
    'input_coupon'        => '쿠폰 번호를 입력해주세요',
    'recharge'            => '충전',
    'recharge_credit'     => '잔액 충전',
    'recharging'          => '충전중...',
    'withdraw_commission' => '결산 수수료',
    'withdraw_at'         => '결산 날짜',
    'withdraw_logs'       => '출금 기록',
    'withdraw'            => '출금',
    'scan_qrcode'         => '프로그램으로 QR코드를 스캔하세요',
    'shop'                => [
        'hot'                => '인기',
        'limited'            => '한정판',
        'change_amount'      => '금액 충전',
        'change_amount_help' => '请输入充值金额',
        'buy'                => '구매',
        'description'        => '표시',
        'service'            => '서비스',
        'pay_credit'         => '잔액 결제',
        'pay_online'         => '온라인 결제',
        'price'              => '가격',
        'quantity'           => '수량',
        'subtotal'           => '소계',
        'total'              => '합계',
        'conflict'           => '패키지 중복 존재',
        'conflict_tips'      => '< p> 현재 구매 패키지는 < code> 예약 패키지로 자동 설정됩니다. </ code> < p> <ol class="text- left" > < li> 예약 패키지는 발효 중인 패키지가 종료되면 자동으로 실행됩니다! </ li> < li> < li> > 결제 후 패키지를 수동으로 활성화할 수 있습니다! </ li> </ li>',
        'call4help'          => '관리자에게 문의하세요',
    ],
    'service'             => [
        'node_count'    => '<code>:num</code> 条优质线路',
        'country_count' => '覆盖 <code>:num</code> 个国家或地区',
        'unlimited'     => '속도 제한 없음',
    ],
    'payment'             => [
        'error'           => '충전 잔액이 올바르지 않습니다',
        'creating'        => '주문서 생성 중...',
        'redirect_stripe' => 'Stripe 결제 페이지로 이동',
        'qrcode_tips'     => '< strong class="red-600" >: software< > 을 사용하여 QR코드를 스캔하십시오.',
        'close_tips'      => '< code>: minutes분 </ code> 안에 결제를 완료해 주십시오, 그렇지 않으면 주문이 자동으로 취소됩니다.',
        'mobile_tips'     => '<strong > 모바일 사용자 </strong >:  QR코드를 길게 누름 - > 이미지 저장 - > 결제 프로그램 열기 - > 청소 - > 앨범 선택 그리고 결제하기',
    ],
    'invoice'             => [
        'attribute'               => '주문서',
        'detail'                  => '소비 기록',
        'amount'                  => '금액',
        'active_prepaid_question' => '미리 예비 결제를 신청하시겠습니까?',
        'active_prepaid_tips'     => '상품이 활성화되면: <br> 이전 상품은 바로 효력을 잃습니다! <br> 만료일은 오늘 다시 시작됩니다!',
    ],
    'node'                => [
        'info'     => '설정 정보',
        'setting'  => '프록시 설정',
        'unstable' => '회선 점검중',
        'rate'     => ':ratio배 데이터 소비',
    ],
    'subscribe'           => [
        'baned'            => '구독 기능이 비활성화되었습니다. 관리자에게 연락하세요.',
        'link'             => '구독 주소',
        'tips'             => '경고: 이 구독 링크는 개인에 한해서만 사용할 수 있습니다. 이 링크를 공유하지 마십시오. 그렇지 않으면 계정에 문제가 생길 수 있습니다.',
        'exchange_warning' => '구독 주소를 변경 시 다음과 같습니다. \n1. 이전 주소는 즉시 비활성화됩니다 \n2. 연결 비밀번호가 변경됩니다.',
        'custom'           => '自定义订阅',
        'ss_only'          => '只订阅SS',
        'ssr_only'         => '只订阅SSR (包含SS)',
        'v2ray_only'       => 'V2Ray 만 구독',
        'trojan_only'      => 'Trojan 만 구독',
        'error'            => '구독 주소 변경 오류',
    ],
    'ticket'              => [
        'attribute'           => '문의',
        'submit_tips'         => '문의를 하시겠습니까?',
        'reply_confirm'       => '문의 답변을 하시겠습니까?',
        'close_tips'          => '문의를 종료하시겠습니까?',
        'close'               => '문의 닫기',
        'failed_closed'       => '오류: 문의가 이미 종료되었습니다.',
        'reply_placeholder'   => '어떤 도움이 필요한가요?',
        'reply'               => '답장',
        'close_msg'           => '문의: 사용자 ID:id 가 수동으로 닫음',
        'title_placeholder'   => '请简单表示你的问题类型，或者涉及的内容',
        'content_placeholder' => '请详细的描述您遇到的问题，或者需要我们帮助的地方，以便我们快速帮助到您',
        'new'                 => '새로 문의하기',
        'working_hour'        => '영업시간',
        'online_hour'         => '접속 시간',
        'service_tips'        => '다양한 연락처가 있습니다. 이 중 < code > 의 </ code > 로 연락하십시오! 잦은 요청 시, 처리 시간이 자동으로 지연됩니다.',
        'error'               => '알 수 없는 오류! 관리자에게 문의하세요',
    ],
    'traffic_logs'        => [
        '24hours' => '오늘 데이터 사용 상황',
        '30days'  => '이달 데이터 사용 상황',
        'tips'    => '알림: 데이터 통계 업데이트가 지연될 수 있습니다. 일별 통계는 다음날 갱신, 시간별 갱신은 몇 시간 뒤 적용됩니다.',
    ],
    'client'              => '프로그램',
    'tutorials'           => '教 程',
    'current_role'        => '현재  정보',
    'knowledge'           => [
        'title' => '知 识 库',
        'basic' => '基 础',
    ],
];
