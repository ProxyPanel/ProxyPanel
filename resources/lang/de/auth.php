<?php

declare(strict_types=1);

return [
    'accept_term' => 'Ich habe gelesen und stimme zu',
    'active' => [
        'attribute' => 'Aktivierung',
        'error' => [
            'activated' => 'Konto bereits aktiviert, bitte melden Sie sich direkt an!',
            'disable' => 'Diese Website hat die Kontoaktivierungsfunktion deaktiviert, Sie können sich direkt anmelden!',
            'throttle' => 'Sie haben das Aktivierungsanfrage-Limit erreicht, bitte versuchen Sie es später erneut!',
        ],
        'promotion' => 'Konto noch nicht aktiviert, bitte zuerst „:action"!',
        'sent' => 'Aktivierungslink wurde an Ihre E-Mail-Adresse gesendet, bitte warten oder prüfen Sie den Spam-Ordner',
    ],
    'aup' => 'Nutzungsbedingungen',
    'captcha' => [
        'attribute' => 'Captcha',
        'error' => [
            'failed' => 'Captcha falsch eingegeben, bitte erneut eingeben!',
            'timeout' => 'Captcha ist abgelaufen, bitte aktualisieren und erneut versuchen!',
        ],
        'required' => 'Bitte vervollständigen Sie die Captcha-Verifikation korrekt',
        'sent' => 'Captcha wurde an Ihre E-Mail-Adresse gesendet, bitte warten oder prüfen Sie den Spam-Ordner',
    ],
    'email' => [
        'error' => [
            'banned' => 'Diese Website unterstützt Ihren E-Mail-Anbieter nicht, bitte wechseln Sie die E-Mail-Adresse!',
            'invalid' => 'Die von Ihnen eingegebene E-Mail-Adresse steht nicht auf der Liste der unterstützten E-Mail-Adressen!',
        ],
    ],
    'error' => [
        'account_baned' => 'Ihr Konto wurde gesperrt!',
        'login_error' => 'Fehler beim Anmeldeprozess, bitte versuchen Sie es später erneut!',
        'login_failed' => 'Anmeldung fehlgeschlagen, bitte prüfen Sie, ob Konto oder Passwort korrekt sind!',
        'not_found_user' => 'Kein verknüpftes Konto gefunden, bitte verwenden Sie andere Anmeldemethoden!',
        'repeat_request' => 'Bitte keine wiederholten Anfragen, aktualisieren Sie und versuchen Sie es erneut!',
        'url_timeout' => 'Link ist ungültig geworden, bitte führen Sie den Vorgang erneut aus!',
    ],
    'failed' => 'Benutzername oder Passwort falsch.',
    'invite' => [
        'get' => 'Einladungscode abrufen',
        'not_required' => 'Kein Einladungscode erforderlich, direkte Registrierung möglich!',
        'unavailable' => 'Einladungscode ungültig, bitte erneut versuchen!',
    ],
    'login' => 'Anmelden',
    'logout' => 'Abmelden',
    'maintenance' => 'Systemwartung',
    'maintenance_tip' => 'System wird gewartet, bitte besuchen Sie uns später!',
    'oauth' => [
        'login_failed' => 'Drittanbieter-Anmeldung fehlgeschlagen!',
        'register' => 'Schnellregistrierung',
        'registered' => 'Bereits registriert, bitte melden Sie sich direkt an',
    ],
    'one-click_login' => 'Ein-Klick-Anmeldung',
    'optional' => 'Optional',
    'password' => [
        'forget' => 'Passwort vergessen?',
        'new' => 'Neues Passwort eingeben',
        'original' => 'Altes Passwort',
        'reset' => [
            'attribute' => 'Passwort zurücksetzen',
            'error' => [
                'demo' => 'Demo-Umgebung verbietet Änderung des Administrator-Passworts!',
                'disabled' => 'Diese Website hat die Passwort-Reset-Funktion deaktiviert!',
                'same' => 'Neues Passwort kann nicht mit dem alten Passwort identisch sein, bitte neu festlegen!',
                'throttle' => 'Passwort kann nur :time Mal alle 24 Stunden zurückgesetzt werden, bitte nicht häufig verwenden!',
                'wrong' => 'Altes Passwort falsch, bitte erneut eingeben!',
            ],
            'sent' => 'Der Reset-Link wurde erfolgreich versendet. Bitte überprüfen Sie Ihre E-Mails (auch den Spam-Ordner)',
            'success' => 'Neues Passwort erfolgreich festgelegt, bitte gehen Sie zur Anmeldeseite.',
        ],
    ],
    'register' => [
        'attribute' => 'Registrieren',
        'code' => 'Registrierungsverifikationscode',
        'error' => [
            'disable' => 'Entschuldigung, diese Website hat den Registrierungskanal vorübergehend geschlossen',
            'throttle' => 'Anti-Spam-Mechanismus aktiviert, bitte nicht häufig registrieren',
        ],
        'failed' => 'Registrierung fehlgeschlagen, bitte versuchen Sie es später erneut',
        'promotion' => 'Sie haben noch kein Konto? Bitte',
    ],
    'remember_me' => 'Angemeldet bleiben',
    'request' => 'Abrufen',
    'throttle' => 'Zu viele Anmeldeversuche, bitte versuchen Sie es in :seconds Sekunden erneut.',
    'tos' => 'Nutzungsbedingungen',
];
