<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => 'Der Zugriff wurde aus Sicherheitsgründen verweigert (unbekannte IP oder Proxy erkannt)',
        'bots' => 'Automatisierter Zugriff erkannt. Der Zugriff wurde aus Sicherheitsgründen verweigert',
        'china' => 'Chinesische IP oder Proxy erkannt, Zugriff verweigert!',
        'oversea' => 'Ausländische IP oder Proxy erkannt, Zugriff verweigert!',
        'redirect' => 'Erkannt (:ip :url) verwendet Abonnement-Link für Zugriff, zwangsweise weitergeleitet',
        'unknown' => 'Unbekannter Sperrmodus, bitte prüfen Sie die Konfiguration in den Systemeinstellungen!',
    ],
    'get_ip' => 'Abrufen der IP-Informationen fehlgeschlagen',
    'log' => 'Protokoll',
    'refresh' => 'Aktualisieren',
    'refresh_page' => 'Bitte aktualisieren Sie die Seite und versuchen Sie es erneut',
    'report' => 'Fehlerbericht-Inhalt:',
    'safe_code' => 'Bitte geben Sie den Sicherheitscode ein',
    'safe_enter' => 'Sicherer Eingangszugriff',
    'subscribe' => [
        'banned_until' => 'Konto gesperrt bis :time, bitte versuchen Sie es nach der Entsperrung erneut!',
        'expired' => 'Konto abgelaufen, bitte verlängern Sie vor der Nutzung!',
        'none' => 'Derzeit keine verfügbaren Knoten',
        'out' => 'Traffic aufgebraucht, bitte kaufen oder setzen Sie Traffic zurück!',
        'question' => 'Konto weist Anomalien auf, bitte besuchen Sie die offizielle Website für Details!',
        'sub_banned' => 'Abonnement-Link wurde gesperrt, bitte besuchen Sie die offizielle Website für Informationen!',
        'unknown' => 'Abonnement-Link ungültig, bitte neu abrufen!',
        'user' => 'Link ungültig, Konto existiert nicht, bitte neu abrufen!',
        'user_disabled' => 'Konto wurde deaktiviert!',
    ],
    'title' => '⚠️ Fehler aufgetreten',
    'unsafe_enter' => 'Unsicherer Eingangszugriff',
    'visit' => 'Bitte besuchen Sie',
    'whoops' => 'Hoppla!',
];
