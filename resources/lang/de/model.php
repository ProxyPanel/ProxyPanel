<?php

declare(strict_types=1);

return [
    'user' => [
        'id' => 'Benutzer-ID',
        'attribute' => 'Benutzer',
        'nickname' => 'Spitzname',
        'username' => 'Benutzername',
        'password' => 'Passwort',
        'credit' => 'Guthaben',
        'invite_num' => 'Verfügbare Einladungen',
        'reset_date' => 'Datenrückstellungsdatum',
        'port' => 'Port',
        'traffic_used' => 'Verwendete Daten',
        'service' => 'Proxy-Dienst',
        'group' => 'Gruppe',
        'account_status' => 'Kontostatus',
        'proxy_status' => 'Proxy-Status',
        'expired_date' => 'Ablaufdatum',
        'role' => 'Rolle',
        'wechat' => 'WeChat',
        'qq' => 'QQ',
        'remark' => 'Bemerkung',
        'uuid' => 'VMess UUID',
        'proxy_passwd' => 'Proxy-Passwort',
        'proxy_method' => 'Verschlüsselung',
        'usable_traffic' => 'Verfügbare Daten',
        'proxy_protocol' => 'Protokoll',
        'proxy_obfs' => 'Verschleierung',
        'speed_limit' => 'Geschwindigkeitsbegrenzung',
        'inviter' => 'Einlader',
        'created_date' => 'Registrierungsdatum',
    ],
    'common' => [
        'extend' => 'Erweiterte Informationen',
        'sort' => 'Sortieren',
        'description' => 'Beschreibung',
        'type' => 'Typ',
        'level' => 'Stufe',
    ],
    'country' => [
        'code' => 'Ländercode',
        'icon' => 'Flagge',
        'name' => 'Ländername',
    ],
    'subscribe' => [
        'code' => 'Abonnementcode',
        'req_times' => 'Anfragenanzahl',
        'updated_at' => 'Letzte Anfrage',
        'ban_time' => 'Sperrzeit',
        'ban_desc' => 'Sperrgrund',
        'req_ip' => 'Anfrage-IP',
        'req_header' => 'Anfrage-Header',
    ],
    'oauth' => [
        'type' => 'Kanal',
        'identifier' => 'Kennung',
    ],
    'user_group' => [
        'attribute' => 'Benutzergruppe',
        'name' => 'Gruppenname',
        'nodes' => 'Knoten',
    ],
    'node' => [
        'attribute' => 'Knoten',
        'id' => 'Knoten-ID',
        'name' => 'Name',
        'domain' => 'Domain',
        'static' => 'Status',
        'online_user' => 'Online-Benutzer',
        'data_consume' => 'Datenverbrauch',
        'data_rate' => 'Datenrate',
        'ddns' => 'DDNS',
        'ipv4' => 'IPv4',
        'ipv6' => 'IPv6',
        'push_port' => 'Push-Port',
        'rule_group' => 'Regelgruppe',
        'traffic_limit' => 'Geschwindigkeitsbegrenzung',
        'client_limit' => 'Gerätebegrenzung',
        'label' => 'Label',
        'country' => 'Land',
        'udp' => 'UDP',
        'display' => 'Anzeige & Abonnement',
        'detection' => 'Blockierungserkennung',
        'method' => 'Verschlüsselung',
        'protocol' => 'Protokoll',
        'protocol_param' => 'Protokollparameter',
        'obfs' => 'Obfs',
        'obfs_param' => 'Obfs-Parameter',
        'single' => 'Einzelport',
        'transfer' => 'Relay',
        'service_port' => 'Service-Port',
        'single_passwd' => '[Einzel] Passwort',
        'v2_alter_id' => 'Alter ID',
        'v2_net' => 'Netzwerk',
        'v2_cover' => 'Cover',
        'v2_host' => 'Host',
        'v2_path' => 'Pfad | Schlüssel',
        'v2_sni' => 'SNI',
        'v2_tls' => 'TLS',
        'v2_tls_provider' => 'TLS-Konfiguration',
        'relay_port' => 'Relay-Port',
    ],
    'node_auth' => [
        'attribute' => 'Knotenauthentifizierung',
        'key' => 'Schlüssel <small>für Knoten</small>',
        'secret' => 'Rückwärtsschlüssel',
    ],
    'node_cert' => [
        'attribute' => 'Domain-Zertifikat',
        'domain' => 'Domain',
        'key' => 'Schlüssel',
        'pem' => 'PEM',
        'issuer' => 'Aussteller',
        'signed_date' => 'Ausstellungsdatum',
        'expired_date' => 'Ablaufdatum',
    ],
    'order' => [
        'attribute' => 'Bestellung',
        'id' => 'Bestellnummer',
        'original_price' => 'Originalpreis',
        'price' => 'Tatsächlicher Preis',
        'pay_way' => 'Zahlungsmethode',
        'status' => 'Status',
    ],
    'goods' => [
        'attribute' => 'Produkt',
        'name' => 'Name',
        'price' => 'Preis',
        'category' => 'Kategorie',
        'renew' => 'Datenverlängerungspreis',
        'user_limit' => 'Benutzergeschwindigkeitsbegrenzung',
        'period' => 'Reset-Zyklus',
        'traffic' => 'Datenvolumen',
        'invite_num' => 'Bonus-Einladungen',
        'limit_num' => 'Kaufbegrenzung',
        'available_date' => 'Gültigkeitszeitraum',
        'hot' => 'Bestseller',
        'color' => 'Farbe',
        'logo' => 'Logo',
        'info' => 'Benutzerdefinierte Informationen',
    ],
    'rule' => [
        'attribute' => 'Regel',
        'name' => 'Beschreibung',
        'pattern' => 'Wert',
    ],
    'rule_group' => [
        'attribute' => 'Regelgruppe',
        'name' => 'Name',
        'type' => 'Typ',
        'rules' => 'Regeln',
    ],
    'role' => [
        'attribute' => 'Rolle',
        'name' => 'Name',
        'permissions' => 'Berechtigungen',
    ],
    'permission' => [
        'attribute' => 'Berechtigung',
        'description' => 'Beschreibung',
        'name' => 'Routenname',
    ],
    'article' => [
        'attribute' => 'Artikel',
        'category' => 'Kategorie',
        'language' => 'Sprache',
        'logo' => 'Cover',
        'created_at' => 'Veröffentlicht am',
        'updated_at' => 'Aktualisiert am',
    ],
    'coupon' => [
        'attribute' => 'Gutschein',
        'name' => 'Name',
        'sn' => 'Code',
        'logo' => 'Logo',
        'value' => 'Wert',
        'priority' => 'Priorität',
        'usable_times' => 'Verwendungsbeschränkung',
        'minimum' => 'Mindestbestellwert',
        'used' => 'Persönliche Begrenzung',
        'levels' => 'Stufenbeschränkung',
        'groups' => 'Gruppenbeschränkung',
        'users_whitelist' => 'Whitelist-Benutzer',
        'users_blacklist' => 'Blacklist-Benutzer',
        'services_whitelist' => 'Whitelist-Produkte',
        'services_blacklist' => 'Blacklist-Produkte',
        'newbie' => 'Nur für neue Benutzer',
        'num' => 'Anzahl',
    ],
    'aff' => [
        'invitee' => 'Käufer',
        'amount' => 'Bestellbetrag',
        'commission' => 'Provision',
        'updated_at' => 'Bearbeitet am',
        'created_at' => 'Bestellt am',
    ],
    'referral' => [
        'created_at' => 'Beantragt am',
        'user' => 'Antragsteller',
        'amount' => 'Betrag',
        'id' => 'Antragsnummer',
    ],
    'notification' => [
        'address' => 'Empfänger',
        'created_at' => 'Gesendet am',
        'status' => 'Status',
    ],
    'ip' => [
        'network_type' => 'Netzwerktyp',
        'info' => 'Standort',
    ],
    'user_traffic' => [
        'upload' => 'Upload',
        'download' => 'Download',
        'total' => 'Gesamt',
        'log_time' => 'Protokolliert am',
    ],
    'user_data_modify' => [
        'before' => 'Vorher',
        'after' => 'Nachher',
        'created_at' => 'Geändert am',
    ],
    'user_credit' => [
        'before' => 'Vorher',
        'after' => 'Nachher',
        'amount' => 'Betrag',
        'created_at' => 'Geändert am',
    ],
];
