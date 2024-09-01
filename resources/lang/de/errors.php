<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => 'Unbekannte IP oder Proxy-Zugriff erkannt, Zugriff verweigert',
        'bots' => 'Bot-Zugriff erkannt, Zugriff verweigert',
        'china' => 'China-IP oder Proxy-Zugriff erkannt, Zugriff verweigert',
        'oversea' => 'Übersee-IP oder Proxy-Zugriff erkannt, Zugriff verweigert',
        'redirect' => '(:ip :url) wurde beim Zugriff über einen Abonnement-Link erkannt, erzwungene Weiterleitung.',
        'unknown' => 'Unbekannter verbotener Zugriffsmodus! Bitte ändern Sie den [Zugriffsbeschränkung] in den Systemeinstellungen!',
    ],
    'get_ip' => 'Fehler beim Abrufen der IP-Informationen',
    'log' => 'Protokoll',
    'refresh' => 'Aktualisieren',
    'refresh_page' => 'Bitte aktualisieren Sie die Seite und versuchen Sie es erneut',
    'report' => 'Der Fehler trug einen Bericht: ',
    'safe_code' => 'Bitte geben Sie den Sicherheitscode ein',
    'safe_enter' => 'Sicherer Eingang',
    'subscribe' => [
        'banned_until' => 'Konto bis :time gesperrt, bitte warten Sie auf die Freischaltung!',
        'expired' => 'Konto abgelaufen! Bitte erneuern Sie Ihr Abonnement!',
        'none' => 'Keine verfügbaren Knoten',
        'out' => 'KEINE DATEN MEHR! Bitte kaufen Sie mehr oder setzen Sie die Daten zurück!',
        'question' => 'Konto-Probleme!? Besuchen Sie die Website für Details',
        'sub_banned' => 'Abonnement gesperrt! Besuchen Sie die Website für Details',
        'unknown' => 'Ungültiger Abonnementlink! Bitte holen Sie sich einen neuen!',
        'user' => 'Ungültige URL, Konto existiert nicht!',
        'user_disabled' => 'Konto deaktiviert! Kontaktieren Sie den Support!',
    ],
    'title' => '⚠️ Fehler ausgelöst',
    'unsafe_enter' => 'Unsicherer Eingang',
    'visit' => 'Bitte besuchen Sie',
    'whoops' => 'Hoppla!',
];
