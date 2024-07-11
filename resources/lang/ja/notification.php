<?php

declare(strict_types=1);

return [
    'attribute' => '通知',
    'new' => '新しいメッセージが:num件あります',
    'empty' => '現在、新しいメッセージはありません',
    'payment_received' => '支払いを受け取りました。金額: :amount。注文の詳細を見る',
    'account_expired' => 'アカウントの有効期限通知',
    'account_expired_content' => 'あなたのアカウントは:days日後に有効期限が切れます。サービスの継続利用のため、早めに更新してください。',
    'account_expired_blade' => 'アカウントは:days日後に有効期限が切れます。早めに更新してください',
    'active_email' => '30分以内に認証を完了してください',
    'close_ticket' => 'チケット番号:id、タイトル:titleがクローズされました',
    'view_web' => '公式サイトを見る',
    'view_ticket' => 'このチケットの進捗を見る',
    'new_ticket' => '新しい返信があります。チケット:titleを確認してください',
    'reply_ticket' => 'チケットの返信：:title',
    'ticket_content' => 'チケット内容：',
    'node_block' => 'ノードブロック警告通知',
    'node_offline' => 'ノードオフライン警告',
    'node_offline_content' => '以下のノードが異常です。オフラインの可能性があります：',
    'block_report' => '詳細なブロックログ：',
    'traffic_warning' => 'データ使用警告',
    'traffic_remain' => 'データ使用率が:percent%に達しました。ご注意ください',
    'traffic_tips' => 'データリセット日を確認し、合理的にデータを使用するか、使用後にリチャージしてください',
    'verification_account' => 'アカウント認証通知',
    'verification' => 'あなたの認証コードは：',
    'verification_limit' => ':minutes分以内に認証を完了してください',
    'data_anomaly' => 'データ異常ユーザー通知',
    'data_anomaly_content' => 'ユーザー:id、最近1時間のデータ（アップロード:upload、ダウンロード:download、合計:total）',
    'node' => [
        'upload' => 'アップロードデータ',
        'download' => 'ダウンロードデータ',
        'total' => '総データ',
    ],
];
