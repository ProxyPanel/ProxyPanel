<?php

declare(strict_types=1);

return [
    'account' => [
        'credit' => 'アカウント残高',
        'status' => 'アカウント状態',
        'level' => 'アカウントレベル',
        'group' => 'グループ',
        'speed_limit' => '速度制限',
        'remain' => '残りデータ',
        'time' => 'プラン期間',
        'last_login' => '最終ログイン',
        'reset' => '{0} データは <code id="restTime">:days</code> 後にリセットされます |{1} データリセットまで :days 日 |[2,*] データリセットまで :days 日',
        'connect_password' => 'プロキシ接続パスワード',
        'reason' => [
            'normal' => 'アカウントは正常です',
            'expired' => 'プランの有効期限が切れています',
            'overused' => 'この期間の <code>:data</code> GB 制限を超えました。<br/> 制限は <code id="banedTime">:min</code> 分後に解除されます',
            'traffic_exhausted' => 'データが使い果たされました',
            'unknown' => '不明な理由です。ブラウザをリフレッシュしてみてください。問題が続く場合はサポートに連絡してください。',
        ],
    ],
    'home' => [
        'attendance' => [
            'attribute' => 'チェックイン',
            'disable' => 'チェックイン機能が無効です',
            'done' => '既にチェックイン済みです。明日また来てください！',
            'success' => ':data データを受け取りました',
            'failed' => 'システムエラー',
        ],
        'traffic_logs' => 'データ記録',
        'announcement' => 'お知らせ',
        'wechat_push' => 'WeChat通知',
        'chat_group' => 'チャットグループ',
        'empty_announcement' => 'お知らせはありません',
    ],
    'purchase_to_unlock' => '購入してアンロック',
    'purchase_required' => 'この機能は無料ユーザーには利用できません。まずは',
    'attribute' => [
        'node' => 'ノード',
        'data' => 'データ',
        'ip' => 'IPアドレス',
        'isp' => 'ISP',
        'address' => '所在地',
    ],
    'purchase_promotion' => '今すぐサービスを購入！',
    'menu' => [
        'help' => 'ヘルプ',
        'home' => 'ホーム',
        'invites' => '招待',
        'invoices' => '請求書',
        'nodes' => 'ノード',
        'promotion' => 'プロモーション',
        'shop' => 'ショップ',
        'profile' => 'プロフィール',
        'tickets' => 'チケット',
        'admin_dashboard' => 'ダッシュボード',
    ],
    'contact' => 'お問い合わせ',
    'oauth' => [
        'bind_title' => 'ソーシャルアカウントを連携',
        'not_bind' => '未連携',
        'bind' => '連携する',
        'rebind' => '再連携する',
        'unbind' => '連携解除',
    ],
    'coupon' => [
        'discount' => '割引',
        'error' => [
            'unknown' => '無効なクーポン',
            'used' => 'クーポンは使用済みです',
            'expired' => 'クーポンの有効期限が切れています',
            'run_out' => 'クーポンの利用回数が制限に達しました',
            'inactive' => 'クーポンはまだ有効ではありません',
            'wait' => ':timeに有効になりますので、お待ちください。',
            'unmet' => '利用条件を満たしていません',
            'minimum' => '最低利用金額は :amount です',
            'overused' => 'クーポンの利用可能回数は :times 回です',
            'users' => 'アカウントはキャンペーン対象外です',
            'services' => '商品は割引対象外です。キャンペーン規約をご確認ください。',
        ],
    ],
    'error_response' => 'エラーが発生しました。時間をおいて再度お試しください。',
    'invite' => [
        'attribute' => '招待コード',
        'counts' => '招待コード総数 <code>:num</code>',
        'tips' => '残招待コード <strong>:num</strong> 個。招待コードは発行後:days日で失効します。',
        'logs' => '招待履歴',
        'promotion' => '招待コードで登録すると、招待元と被招待者の両方に<mark>:traffic</mark>データが付与されます。被招待者が購入した場合、購入金額の<mark>:referral_percent%</mark>が報酬として付与されます。',
        'generate_failed' => '生成失敗: 招待コードの上限に達しました',
    ],
    'reset_data' => [
        'action' => 'データリセット',
        'cost' => 'コスト <code>:amount</code>',
        'cost_tips' => 'データリセットには :amount が差し引かれます!',
        'insufficient' => '残高が足りません。チャージしてください。',
        'logs' => 'ユーザーデータリセット記録',
        'success' => 'リセット成功',
    ],
    'referral' => [
        'link' => '紹介リンク',
        'total' => '総報酬 :amount(:total 回)。:money 以上になると出金できます。',
        'logs' => '報酬履歴',
        'failed' => '申請失敗',
        'success' => '申請成功',
        'msg' => [
            'account' => 'アカウントの有効期限が切れています。まずはサービスを購入してください。',
            'applied' => '申請はすでに存在しています。処理完了をお待ちください。',
            'unfulfilled' => '出金には :amount が必要です。頑張ってください!',
            'wait' => '管理者による承認をお待ちください。',
            'error' => '申請の作成に失敗しました。時間をおいて再度試すか、管理者にお問い合わせください。',
        ],
    ],
    'inviter' => '招待者',
    'invitee' => '被招待者',
    'registered_at' => '登録日',
    'bought_at' => '購入日',
    'payment_method' => '決済方法',
    'pay' => '支払う',
    'input_coupon' => 'クーポンコードを入力',
    'recharge' => 'チャージ',
    'recharge_credit' => '残高チャージ',
    'recharging' => 'チャージ中...',
    'withdraw_commission' => '報酬出金',
    'withdraw_at' => '出金日',
    'withdraw_logs' => '出金記録',
    'withdraw' => '出金',
    'scan_qrcode' => 'クライアントでQRコードをスキャン',
    'shop' => [
        'hot' => '人気',
        'limited' => '限定',
        'change_amount' => 'チャージ金額',
        'change_amount_help' => 'チャージ金額を入力',
        'buy' => '購入',
        'description' => '説明',
        'service' => 'サービス',
        'pay_credit' => '残高支払い',
        'pay_online' => 'オンライン支払い',
        'price' => '価格',
        'quantity' => '数量',
        'subtotal' => '小計',
        'total' => '合計',
        'conflict' => 'プラン競合',
        'conflict_tips' => '<p>現在の購入は<code>前払いプラン</code>として設定されます</p><p><ol class="text-left"><li>前払いプランは、現在のプラン終了後に自動で開始されます!</li><li>支払い後に手動でプランを開始することができます!</li></ol>',
        'call4help' => 'お問い合わせはチケットにてお願いします',
    ],
    'service' => [
        'node_count' => '<code>:num</code> 本の高品質ノード',
        'country_count' => '<code>:num</code> カ国をカバー',
        'unlimited' => '無制限',
    ],
    'payment' => [
        'error' => 'チャージ金額が正しくありません',
        'creating' => '支払いを作成中...',
        'redirect_stripe' => 'Stripeの支払ページに移動中',
        'qrcode_tips' => '<strong class="red-600">:software</strong>でQRコードをスキャンしてください',
        'close_tips' => '<code>:minutes分</code>以内に支払いを完了してください。そうしないと注文は自動的にキャンセルされます。',
        'mobile_tips' => '<strong>モバイルユーザー</strong>: QRコードを長押し -> 画像を保存 -> 支払アプリを開く -> アルバムから読み取る',
    ],
    'invoice' => [
        'attribute' => '注文',
        'detail' => '購入履歴',
        'amount' => '金額',
        'active_prepaid_question' => '前払いパッケージをすぐにアクティブにしますか?',
        'active_prepaid_tips' => 'アクティブ後:<br>- 現在のパッケージはすぐに失効!<br>- 有効期限は本日から再開!',
    ],
    'node' => [
        'info' => '構成情報',
        'setting' => 'プロキシ設定',
        'unstable' => 'ノードが不安定/メンテナンス中',
        'rate' => ':ratio 倍のデータ使用',
    ],
    'subscribe' => [
        'link' => 'サブスクリプションリンク',
        'tips' => '警告: このサブスクリプションリンクは個人利用目的でのみ共有可。アカウントの異常なデータ使用を検知し、自動的にBANする可能性があるため、リンクは共有しないでください。',
        'exchange_warning' => 'サブスクリプション変更により:\n1. 現在のサブはすぐに無効になる\n2. 接続パスワードが変更される',
        'custom' => 'カスタムサブスクリプション',
        'ss_only' => 'SSのみ',
        'ssr_only' => 'SSRのみ(SS含む)',
        'v2ray_only' => 'V2Rayのみ',
        'trojan_only' => 'Trojanのみ',
        'error' => 'サブスクリプション変更エラー',
        'info' => [
            'title' => 'アカウント概要 [リアルタイムではない]',
            'upload' => 'アップロードデータ',
            'download' => 'ダウンロードデータ',
            'total' => 'プランデータ',
        ],
    ],
    'ticket' => [
        'attribute' => 'チケット',
        'submit_tips' => 'チケットを提出しますか?',
        'reply_confirm' => 'チケットへの返信を確定しますか?',
        'close_tips' => 'このチケットを閉じますか?',
        'close' => 'チケットを閉じる',
        'failed_closed' => 'エラー: チケットはすでに閉じられています',
        'reply_placeholder' => 'ここにコメントを入力...',
        'reply' => '返信',
        'close_msg' => 'チケット: ID :id がユーザーによって閉じられました',
        'title_placeholder' => '簡単に問題を説明してください',
        'content_placeholder' => '問題を詳細に説明して助けを得ることができるようにしてください',
        'new' => '新規チケット',
        'service_hours' => '営業時間',
        'online_hour' => 'オンライン時間',
        'service_tips' => 'ご連絡は<code>1つの</code>方法のみでお願いします。<br>重複したリクエストは対応を遅らせます。',
        'error' => '不明なエラーです。スタッフに通知してください。',
    ],
    'traffic_logs' => [
        'hourly' => '今日のデータ使用',
        'daily' => '今月のデータ使用',
        'tips' => '：トラフィックのデータは遅延を提供します。',
    ],
    'clients' => 'クライアント',
    'tutorials' => 'チュートリアル',
    'current_role' => '現在のロール',
    'knowledge' => [
        'title' => 'ナレッジベース',
        'basic' => '基本',
    ],
    'manual' => [
        'red_packet' => 'Alipayレッドパケット',
        'hint' => 'QRコードをスキャン後、【次へ】をクリックし、【送信】まで完了してください!',
        'step_1' => '注意',
        'step_1_title' => '手動支払いの方法',
        'step_2' => '支払い',
        'step_2_title' => '支払いQRコードを取得',
        'step_3' => '完了',
        'step_3_title' => '手動確認待ち',
        'remark' => 'アカウント備考',
        'remark_content' => 'ログインアカウントを入力して確認',
        'payment_hint' => '正確な金額を支払ってください。オーバーペイの返金はなく、アンダーペイの場合は差額をチャージ',
        'pre' => '前のステップ',
        'next' => '次のステップ',
    ],
];
