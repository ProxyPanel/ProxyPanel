<?php

declare(strict_types=1);

return [
    'accept_term' => 'Ich habe gelesen und akzeptiert',
    'active' => [
        'attribute' => 'Aktivieren',
        'error' => [
            'activated' => 'Konto bereits aktiviert, keine erneute Aktivierung erforderlich',
            'disable' => 'Kontoaktivierung deaktiviert, Sie können sich direkt anmelden!',
            'throttle' => 'Limit für Aktivierungsanfragen erreicht, bitte nicht zu häufig ausführen! Kontaktieren Sie bei Fragen: :email',
        ],
        'promotion' => 'Konto noch nicht aktiviert, bitte zuerst [:action]!',
        'sent' => 'Aktivierungslink wurde an Ihre E-Mail gesendet, bitte auch Spam-Ordner prüfen.',
    ],
    'aup' => 'Akzeptable Nutzungsbedingungen',
    'captcha' => [
        'attribute' => 'Captcha',
        'error' => [
            'failed' => 'Captcha-Verifizierung fehlgeschlagen, bitte erneut versuchen',
            'timeout' => 'Captcha abgelaufen, bitte aktualisieren und erneut versuchen.',
        ],
        'required' => 'Bitte Captcha ausfüllen!',
        'sent' => 'Captcha an Ihre E-Mail gesendet, bitte auch Spam-Ordner prüfen.',
    ],
    'email' => [
        'error' => [
            'banned' => 'Ihr E-Mail-Anbieter ist gesperrt, bitte eine andere E-Mail verwenden.',
            'invalid' => 'Ihre E-Mail wird nicht unterstützt.',
        ],
    ],
    'error' => [
        'account_baned' => 'Ihr Konto ist gesperrt!',
        'login_error' => 'Login-Fehler, bitte später erneut versuchen!',
        'login_failed' => 'Login fehlgeschlagen, bitte E-Mail und Passwort prüfen!',
        'not_found_user' => 'Kein Konto gefunden, bitte andere Login-Methode versuchen.',
        'repeat_request' => 'Bitte aktualisieren und erneut versuchen.',
        'url_timeout' => 'Link abgelaufen, bitte erneut anfordern.',
    ],
    'failed' => 'Diese Kombination aus Zugangsdaten wurde nicht in unserer Datenbank gefunden.',
    'invite' => [
        'attribute' => 'Einladungscode',
        'error' => [
            'unavailable' => 'Ungültiger Einladungscode, bitte erneut versuchen.',
        ],
        'get' => 'Einladungscode anfordern',
        'not_required' => 'Kein Einladungscode erforderlich, Sie können sich direkt registrieren!',
    ],
    'login' => 'Login',
    'logout' => 'Logout',
    'maintenance' => 'Wartung',
    'maintenance_tip' => 'In Wartung',
    'oauth' => [
        'bind_failed' => 'Bindung fehlgeschlagen',
        'bind_success' => 'Bindung erfolgreich',
        'login_failed' => 'Social-Login fehlgeschlagen!',
        'rebind_success' => 'Erneute Bindung erfolgreich',
        'register' => 'Schnellregistrierung',
        'register_failed' => 'Registrierung fehlgeschlagen',
        'registered' => 'Bereits registriert, bitte direkt anmelden.',
        'unbind_failed' => 'Aufhebung der Bindung fehlgeschlagen',
        'unbind_success' => 'Bindung aufgehoben',
    ],
    'one-click_login' => 'Ein-Klick-Login',
    'optional' => 'Optional',
    'password' => [
        'forget' => 'Passwort vergessen?',
        'new' => 'Neues Passwort eingeben',
        'original' => 'Aktuelles Passwort',
        'reset' => [
            'attribute' => 'Passwort zurücksetzen',
            'error' => [
                'demo' => 'Änderung des Admin-Passworts in Demo nicht möglich.',
                'disabled' => 'Passwortzurücksetzung deaktiviert, bitte kontaktieren Sie für Hilfe: :email',
                'failed' => 'Zurücksetzen des Passworts fehlgeschlagen.',
                'same' => 'Neues Passwort darf nicht gleich wie altes sein, bitte erneut eingeben.',
                'throttle' => 'Sie können Ihr Passwort nur :time Mal in 24 Stunden zurücksetzen, bitte nicht zu häufig ausführen.',
                'wrong' => 'Falsches Passwort, bitte erneut versuchen.',
            ],
            'sent' => 'Link zum Zurücksetzen an Ihre E-Mail gesendet, bitte auch Spam-Ordner prüfen.',
            'success' => 'Neues Passwort erfolgreich gesetzt, Sie können sich nun anmelden.',
        ],
    ],
    'register' => [
        'attribute' => 'Registrieren',
        'code' => 'Registrierungscode',
        'error' => [
            'disable' => 'Die Registrierung ist derzeit geschlossen.',
            'throttle' => 'Anti-Bot aktiv! Bitte senden Sie keine Registrierungsformulare zu häufig!',
        ],
        'failed' => 'Registrierung fehlgeschlagen, bitte später erneut versuchen.',
        'promotion' => 'Noch kein Konto? Bitte gehen Sie zu',
        'success' => 'Registrierung erfolgreich',
    ],
    'remember_me' => 'Eingeloggt bleiben',
    'request' => 'Anfordern',
    'throttle' => 'Zu viele Loginversuche. Versuchen Sie es bitte in :seconds Sekunden nochmal.',
    'tos' => 'Nutzungsbedingungen',
];
