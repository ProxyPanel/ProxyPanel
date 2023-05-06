<?php

return [
    'account'             => [
        'credit'           => 'アカウント残高',
        'status'           => 'アカウント状態',
        'level'            => 'アカウントレベル',
        'group'            => '下限額',
        'speed_limit'      => 'スピード',
        'remain'           => '残りのデータ',
        'time'             => 'サブスクリプション期間',
        'last_login'       => '最近のサインイン順',
        'reset'            => '{0} が残っています <code id="restTime">:days</code> のトラフィックのリセット|[1,*] と :days 日のデータをリセットしました',
        'connect_password' => '接続パスワード',
        'reason'           => [
            'normal'            => 'いつも大丈夫よ',
            'expired'           => 'サブスクリプションの有効期限が切れました',
            'overused'          => 'この期間でのデータ通信量が <code>:data</code> GB<br/> を超えてシステムの制限をトリガー <code id="banedTime">%</code> から切断される',
            'traffic_exhausted' => 'アカウント[流量]は壊れているため',
            'unknown'           => '原因は不明です。[刷新]あなたのブラウザをお試しください。何度も問題が続くようであれば，管理者に連絡を取ってください．',
        ],
    ],
    'home'                => [
        'attendance'         => [
            'attribute' => 'セプト',
            'disable'   => 'システムにはログイン機能が開放されていません',
            'done'      => '簽到已經簽到了，明天再來！',
            'success'   => 'データ取得した :data',
            'failed'    => 'システム❌例外',
        ],
        'traffic_logs'       => 'トラフィックのログ',
        'announcement'       => 'お知らせ',
        'wechat_push'        => 'WeChat 通知',
        'chat_group'         => 'チャットグループ',
        'empty_announcement' => 'お知らせはありません',
    ],
    'purchase_to_unlock'  => '購入後にアンロックする',
    'purchase_required'   => 'この機能は非課金ユーザーに代わって有効です！',
    'attribute'           => [
        'node'    => 'ネットワーク',
        'data'    => 'トラフィック',
        'ip'      => 'IPアドレス',
        'isp'     => 'キャリア',
        'address' => '地域',
    ],
    'purchase_promotion'  => 'さあ買って買ってわかったかい？',
    'menu'                => [
        'helps'           => '組織援助',
        'home'            => 'メインページ',
        'invites'         => '招待者',
        'invoices'        => '元帳',
        'nodes'           => 'ルーティング',
        'referrals'       => 'あなたの宣伝フィールド',
        'shop'            => 'ビジネス',
        'profile'         => '設置',
        'tickets'         => '仕事オーダー',
        'admin_dashboard' => '関係ある？',
    ],
    'contact'             => '連絡先情報',
    'oauth'               => [
        'bind_title' => 'ソーシャル連携アカウント',
        'not_bind'   => '未リンク',
        'bind'       => 'ほどく',
        'rebind'     => '再リンクする',
        'unbind'     => '別荘を分解',
    ],
    'coupon'              => [
        'discount' => 'オフ',
        'error'    => [
            'unknown'  => '無効なクーポン',
            'used'     => 'このクーポンは既に使用されています',
            'expired'  => 'クーポンの有効期限が切れています',
            'run_out'  => 'クーポン切れ',
            'inactive' => 'このクーポンはまだ有効ではありません',
            'wait'     => 'イベントまで :timeは実行されますが、少々お待ちください。',
            'unmet'    => '使用条件が足りません',
            'minimum'  => 'このクーポンを利用できるのは :amount です',
            'overused' => 'この券は:times 回のみ使用できます',
            'users'    => 'アカウントはセール条件を満たしていません。',
            'services' => '品が割引基準を満たしていません。プロモーション条件をチェックしてください',
        ],
    ],
    'error_response'      => 'エラーが発生しました。後でもう一度お試し下さい。',
    'invite'              => [
        'attribute'       => '招待コード',
        'counts'          => '合計 <code>:num</code> 招待コード',
        'tips'            => 'の生成<strong> :num </strong>の招待コード、:days の',
        'logs'            => '招待履歴',
        'promotion'       => '招待コードに登録し、有効にすると購読者間の<mark>クフーン</mark>トラフィックが贈られます。消費時、消費される消費額の獲得金額が<mark>:referral_percent</mark>となります。',
        'generate_failed' => '生成に失敗しました：出席者が指定した回数だけ増やせません',
    ],
    'reset_data'          => [
        'action'    => 'トラフィックのリセット',
        'cost'      => '<code>:amount</code> が必要です',
        'cost_tips' => '今回のリセットトラフィックは残高:amountが差し引かれます！',
        'lack'      => '残高不足です。請求してください',
        'logs'      => 'ユーザー操作に必要な量をリセット',
        'success'   => 'リセットしました',
    ],
    'referral'            => [
        'link'    => '住所１',
        'total'   => '戻額合計額:amount(:total 回)、amoneyが追加された場合、出金を申請してください。',
        'logs'    => 'コミッション履歴',
        'failed'  => '申請できませんでした',
        'success' => '申請に成功しました。',
        'msg'     => [
            'account'     => 'アカウントが期限切れです。購入してください。',
            'applied'     => '申請中です。前回の応募が終わるまでお待ちください。',
            'unfulfilled' => '出金することができ、:amountのご利用をお待ちください',
            'wait'        => '販売中',
            'error'       => '還元フォームの作成に失敗しました。後ほどお試しください',
        ],
    ],
    'inviter'             => '招待者',
    'invitee'             => '招待者',
    'registered_at'       => '登録日',
    'bought_at'           => '購入日',
    'payment_method'      => '支払い方法',
    'pay'                 => 'お支払い',
    'input_coupon'        => 'チャージ券コードを入力してください',
    'recharge'            => 'クレジットの追加',
    'recharge_credit'     => '資金を追加',
    'recharging'          => 'クレジットを追加中...',
    'withdraw_commission' => 'チェックアウト手数料',
    'withdraw_at'         => '決済日',
    'withdraw_logs'       => 'キャッシュアウトの記録',
    'withdraw'            => '条件',
    'scan_qrcode'         => '代わりにクライアントを使用してQRコードをスキャン',
    'shop'                => [
        'hot'                => 'ヒート',
        'limited'            => '公式',
        'change_amount'      => '追加入金',
        'change_amount_help' => '追加入荷額を入力してください',
        'buy'                => '購入',
        'description'        => '説明',
        'service'            => 'サービス',
        'pay_credit'         => '残高の支払い',
        'pay_online'         => 'オンライン決済',
        'price'              => '価格',
        'quantity'           => '個数',
        'subtotal'           => '小計',
        'total'              => '合計',
        'conflict'           => 'パッケージの不一致',
        'conflict_tips'      => '<p>当前购买套餐将自动设置为 <code>预支付套餐</code><p><ol class="text-left"><li> 预支付套餐会在生效中的套餐失效后自动开通！</li><li> 您可以在支付后手动激活套餐！</li></ol>',
        'call4help'          => 'チケット・サポートチケットを開いてください。',
    ],
    'service'             => [
        'node_count'    => '<code>:num</code> 高品質なワイヤー',
        'country_count' => '<code>:num</code> か国を上書きします。',
        'unlimited'     => 'スピード制限',
    ],
    'payment'             => [
        'error'           => 'チャージ残高が正しくありません',
        'creating'        => '支払いフォームを作成しています...',
        'redirect_stripe' => 'Stripeの支払い画面に移動',
        'qrcode_tips'     => 'QRコードをスキャンするには<strong class="red-600">:software</strong>を使用してください',
        'close_tips'      => '<code>:minutes</code>内の支払い手続きを完了してください。さもなければ注文は自動的にオフになります。',
        'mobile_tips'     => '<strong>モバイルユーザー</strong>：長押しの QR コード → 支払いを保存します -> スイート -> スイート -> スイートの選択で 支払いの選択',
    ],
    'invoice'             => [
        'attribute'               => '注文',
        'detail'                  => '支払い履歴',
        'amount'                  => '金額',
        'active_prepaid_question' => '事前決済サービスを有効化しますか？',
        'active_prepaid_tips'     => '現在のパッケージはパック内から有効になりました：<br>以前のパッケージは直すことができます！<br>この日最初からやり直しです！',
    ],
    'node'                => [
        'info'     => '設定情報',
        'setting'  => 'プロキシ設定',
        'unstable' => '車両の変動/メンテナンスで',
        'rate'     => ':ratio倍トラフィック消費',
    ],
    'subscribe'           => [
        'link'             => 'サブスクリプションリンク',
        'tips'             => 'アラート：このサブスクリプションリンクは個人にのみ利用され、このリンクを伝染しない限り、そのリンクはアカウントの利用量の異常なパケットを発信します。',
        'exchange_warning' => 'サブスクリプションアドレスを変更すると、\n1.古いアドレスがすぐ無効\n2.接続パスワードの変更が発生します。',
        'custom'           => 'カスタムフィード',
        'ss_only'          => 'SSのみ購読する',
        'ssr_only'         => 'SSR（SSを含む）',
        'v2ray_only'       => 'V2Ray のみ購読する',
        'trojan_only'      => 'Trojan のみ登録',
        'error'            => '例外',
        'info'             => [
            'title'    => 'アカウント概要 [非实时]',
            'upload'   => 'アップロードデータ',
            'download' => '通信量のダウンロード',
            'total'    => 'パックトラフィック',
        ],
    ],
    'ticket'              => [
        'attribute'           => 'チケット',
        'submit_tips'         => '課題を確認しますか?',
        'reply_confirm'       => '課題に返信してよろしいですか。',
        'close_tips'          => 'この課題をクローズしますか？',
        'close'               => 'チケットを閉じる',
        'failed_closed'       => 'エラー：このチケットを閉じました',
        'reply_placeholder'   => '何か言えますか？',
        'reply'               => '返信',
        'close_msg'           => 'チケット：ID :id人が手動でクローズしました',
        'title_placeholder'   => 'イシューや問題の内容は簡単に表現できます。',
        'content_placeholder' => 'どのような状況で問題が起こるか、あなたが助けを必要とする場所かあなたの手助けをしてください',
        'new'                 => '新しいチケットを作成する',
        'working_hour'        => '営業時間',
        'online_hour'         => '現在時刻',
        'service_tips'        => '本站有多种联系方式，请使用其中<code>一种</code>联系客服！ <br>重复请求，将会自动延迟处理时间',
        'error'               => '不明なエラーです！サポートに連絡してください',
    ],
    'traffic_logs'        => [
        '24hours' => '本日のデータ使用',
        '30days'  => '今月のデータ使用',
        'tips'    => 'ヒント：トラフィックの統計更新は遅延があります。統計は、翌日更新され、更新、1時間ごとに更新されます。',
    ],
    'client'              => 'クライアント',
    'tutorials'           => '程度',
    'current_role'        => '現在のロール:',
    'knowledge'           => [
        'title' => 'コンセンサス',
        'basic' => '親しくさ',
    ],
    'manual'              => [
        'red_packet'     => 'PayPalギフト',
        'hint'           => 'コードを評価した後、👇(次へ)をクリックして「:backhand_index_hinting_down:」までお支払いを完了してください。',
        'step_1'         => '判断',
        'step_1_title'   => '適切に人工的な決済方法',
        'step_2'         => 'お支払い',
        'step_2_title'   => 'お支払いに QR コードを取得してください。',
        'step_3'         => '完了',
        'step_3_title'   => '保留中の支払いが承認されるまでお待ちください',
        'remark'         => '口座番号',
        'remark_content' => 'ログインフォームに残っているアカウント。これは手動で確認することができます。',
        'payment_hint'   => 'お支払いで、金額に対応します（ほとんど払えない場合）',
        'pre'            => '戻る',
        'next'           => '次へ',
    ],
];
