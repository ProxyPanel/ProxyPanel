<?php

return [
    'user'             => [
        'id'             => 'ユーザ ID',
        'attribute'      => 'ユーザー',
        'nickname'       => 'ニックネーム',
        'username'       => 'アカウント',
        'password'       => 'パスワード',
        'credit'         => '残高',
        'invite_num'     => '利用可能な招待コード',
        'reset_date'     => 'リセット日',
        'port'           => 'ポート',
        'traffic_used'   => 'データ使用量',
        'service'        => 'プロキシ',
        'group'          => 'グループ',
        'account_status' => 'アカウント状態',
        'proxy_status'   => 'プロキシ状態',
        'expired_date'   => '終了日',
        'role'           => 'ロール権限',
        'wechat'         => 'マイクロレター',
        'qq'             => 'QQ',
        'remark'         => 'メモ',
        'uuid'           => 'VMess UUUID',
        'proxy_passwd'   => 'パスワード',
        'proxy_method'   => '暗号化方式',
        'usable_traffic' => '利用可能なデータ',
        'proxy_protocol' => 'プロトコル',
        'proxy_obfs'     => '難読化',
        'speed_limit'    => 'ユーザー制限速度',
        'inviter'        => '招待人：',
        'created_date'   => '登録日',
    ],
    'common'           => [
        'extend'      => '拡張',
        'sort'        => '並べ替え',
        'description' => '説明',
        'type'        => 'タイプ',
        'level'       => 'レベル',
    ],
    'country'          => [
        'code' => 'ISO国コード',
        'icon' => 'ビーコン',
        'name' => '国名',
    ],
    'subscribe'        => [
        'code'       => 'サブスクリプションコード',
        'req_times'  => '要求回数',
        'updated_at' => '前回の要求時間',
        'ban_time'   => 'アクセス禁止時間',
        'ban_desc'   => 'BANされた理由',
        'req_ip'     => 'リクエスト IP',
        'req_header' => '訪問',
    ],
    'oauth'            => [
        'type'       => 'チャネル',
        'identifier' => ' 識別のみ',
    ],
    'user_group'       => [
        'attribute' => 'ユーザーグループ',
        'name'      => 'グループ名',
        'nodes'     => 'ノードの選択',
    ],
    'node'             => [
        'attribute'       => 'ノード',
        'id'              => 'ノードID',
        'name'            => '注文番号による検索',
        'domain'          => 'ドメイン',
        'static'          => '生存',
        'online_user'     => '開くべきクリック ギルドVault 4',
        'data_consume'    => 'トラフィックの生成',
        'data_rate'       => 'トラフィックの割合',
        'ddns'            => 'DDNS',
        'ipv4'            => 'ＩＰｖ４アドレス',
        'ipv6'            => 'IPv6アドレス',
        'push_port'       => 'メッセージポート:',
        'rule_group'      => '監査グループ',
        'traffic_limit'   => 'ノード制限速度',
        'client_limit'    => 'デバイスの制限',
        'label'           => 'タグ',
        'country'         => '都道府県',
        'udp'             => 'UDP',
        'display'         => '購読と表示',
        'detection'       => 'ノードのブロック認識',
        'method'          => '暗号化方式',
        'protocol'        => 'プロトコル',
        'protocol_param'  => 'プロトコルのパラメータ',
        'obfs'            => '難読化',
        'obfs_param'      => '混乱した引数',
        'single'          => '単一ポート',
        'transfer'        => '転送',
        'service_port'    => 'サービスポート',
        'single_passwd'   => '[单] パスワード',
        'v2_alter_id'     => '追加ID',
        'v2_net'          => '転送方法',
        'v2_cover'        => '偽装タイプ',
        'v2_host'         => '偽装ドメイン',
        'v2_path'         => 'パス | キー',
        'v2_sni'          => 'SNI',
        'v2_tls'          => '接続TLS',
        'v2_tls_provider' => 'TLS の構成設定',
        'relay_port'      => '出力ポート',
    ],
    'node_auth'        => [
        'attribute' => 'ノードライセンス',
        'key'       => '通信キー<small>ノード</small>',
        'secret'    => 'リバース通信キー',
    ],
    'node_cert'        => [
        'attribute'    => 'ドメイン証明書',
        'domain'       => 'ドメイン',
        'key'          => 'KEY',
        'pem'          => 'PEM',
        'issuer'       => '発行者',
        'signed_date'  => '発行日',
        'expired_date' => '有効期限',
    ],
    'order'            => [
        'attribute'      => '注文',
        'id'             => '名前で検索します。',
        'original_price' => '旧価格',
        'price'          => '価格',
        'pay_way'        => '支払い方法',
        'status'         => 'ご注文状況:',
    ],
    'goods'            => [
        'attribute'      => '配送番号の設定',
        'name'           => '注文番号による検索',
        'price'          => '販売価格',
        'category'       => 'カテゴリ',
        'renew'          => '使用量のリセット',
        'user_limit'     => 'ユーザー制限速度',
        'period'         => '期間をリセット',
        'traffic'        => 'トラフィックの経度',
        'invite_num'     => '招待コードを贈る',
        'limit_num'      => '最大購入数',
        'available_date' => '有効期限',
        'hot'            => '人気',
        'color'          => 'カラー',
        'logo'           => 'ベンダサムネイル画像サイズ',
        'info'           => 'カスタムリスト',
    ],
    'rule'             => [
        'attribute' => 'ルール',
        'name'      => '説明',
        'pattern'   => '値',
    ],
    'rule_group'       => [
        'attribute' => 'ルールグループ',
        'name'      => 'グループ名',
        'type'      => '監査モード',
        'rules'     => '規則選択',
    ],
    'role'             => [
        'attribute'   => 'ロール',
        'name'        => '注文番号による検索',
        'permissions' => '権限',
    ],
    'permission'       => [
        'attribute'   => 'アクセス許可の動作',
        'description' => 'ビヘイビアの説明',
        'name'        => 'ルート名',
    ],
    'article'          => [
        'attribute'  => '記事',
        'category'   => 'クラス',
        'language'   => '話す',
        'logo'       => 'ヘッダ',
        'created_at' => '公開日',
        'updated_at' => '最終更新日',
    ],
    'coupon'           => [
        'attribute'          => 'カード券',
        'name'               => 'カード券名',
        'sn'                 => 'クーポンコードを使用',
        'logo'               => 'カード券画像',
        'value'              => '割引率',
        'priority'           => '重さ',
        'usable_times'       => '使用数',
        'minimum'            => 'MAX減条件',
        'used'               => '個人制限',
        'levels'             => 'ランク制限',
        'groups'             => 'グループの制限',
        'users_whitelist'    => '限定ユーザー',
        'users_blacklist'    => 'ユーザーを無効にする',
        'services_whitelist' => 'Emailアドレスを入力してください',
        'services_blacklist' => '製品を無効にする。',
        'newbie'             => '新人専属',
        'num'                => '個数',
    ],
    'aff'              => [
        'invitee'    => '消費者',
        'amount'     => '金銭的額',
        'commission' => '払戻額',
        'updated_at' => '処理時間',
        'created_at' => '注文なし',
    ],
    'referral'         => [
        'created_at' => '要求時間',
        'user'       => 'アカウントのリクエスト',
        'amount'     => '引き出し額申請',
        'id'         => '単品ID',
    ],
    'notification'     => [
        'address'    => '送信先アドレス',
        'created_at' => '配達日数',
        'status'     => '納品検収状態',
    ],
    'ip'               => [
        'network_type' => '通信タイプ',
        'info'         => '関する地域',
    ],
    'user_traffic'     => [
        'upload'   => 'トラフィックのアップロード',
        'download' => '通信量のダウンロード',
        'total'    => '合計トラフィック',
        'log_time' => '記録時間',
    ],
    'user_data_modify' => [
        'before'     => '変更前のトラフィック',
        'after'      => '運用の後退率',
        'created_at' => 'タイムスタンプ',
    ],
    'user_credit'      => [
        'before'     => '操作前残高',
        'after'      => '操作後の金額',
        'amount'     => '発生金額',
        'created_at' => 'タイムスタンプ',
    ],
];
