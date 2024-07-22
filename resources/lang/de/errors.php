<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => 'Unbekannte IP oder Proxy-Zugriff erkannt, Zugriff verweigert',
        'bots' => 'Bot-Zugriff erkannt, Zugriff verweigert',
        'china' => 'China-IP oder Proxy-Zugriff erkannt, Zugriff verweigert',
        'oversea' => 'Übersee-IP oder Proxy-Zugriff erkannt, Zugriff verweigert',
        'unknown' => 'Unbekannter verbotener Zugriffsmodus! Bitte ändern Sie den [Zugriffsbeschränkung] in den Systemeinstellungen!',
        'redirect' => '(:ip :url) wurde beim Zugriff über einen Abonnement-Link erkannt, erzwungene Weiterleitung.',
    ],
    'log' => 'Protokoll',
    'refresh' => 'Aktualisieren',
    'refresh_page' => 'Bitte aktualisieren Sie die Seite und versuchen Sie es erneut',
    'report' => 'Der Fehler trug einen Bericht: ',
    'safe_enter' => 'Sicherer Eingang',
    'safe_code' => 'Bitte geben Sie den Sicherheitscode ein',
    'title' => '⚠️ Fehler ausgelöst',
    'unsafe_enter' => 'Unsicherer Eingang',
    'visit' => 'Bitte besuchen Sie',
    'whoops' => 'Hoppla!',
    'get_ip' => 'Fehler beim Abrufen der IP-Informationen',
    'subscribe' => [
        'unknown' => 'Ungültiger Abonnementlink! Bitte holen Sie sich einen neuen!',
        'sub_banned' => 'Abonnement gesperrt! Besuchen Sie die Website für Details',
        'user' => 'Ungültige URL, Konto existiert nicht!',
        'user_disabled' => 'Konto deaktiviert! Kontaktieren Sie den Support!',
        'banned_until' => 'Konto bis :time gesperrt, bitte warten Sie auf die Freischaltung!',
        'out' => 'KEINE DATEN MEHR! Bitte kaufen Sie mehr oder setzen Sie die Daten zurück!',
        'expired' => 'Konto abgelaufen! Bitte erneuern Sie Ihr Abonnement!',
        'question' => 'Konto-Probleme!? Besuchen Sie die Website für Details',
        'none' => 'Keine verfügbaren Knoten',
    ],
];
