<?php

declare(strict_types=1);

return [
    'accept_term' => 'Ich habe gelesen und akzeptiere',
    'active' => [
        'attribute' => 'Aktivieren',
        'error' => [
            'activated' => 'Konto bereits aktiviert, erneute Aktivierung nicht erforderlich',
            'disable' => 'Die Kontoaktivierung ist deaktiviert, Sie können sich direkt anmelden!',
            'throttle' => 'Sie haben das Aktivierungsanfragelimit erreicht, bitte versuchen Sie es später erneut! Bei Fragen kontaktieren Sie :email.',
        ],
        'promotion' => 'Konto noch nicht aktiviert, bitte zuerst [:action]!',
        'sent' => 'Aktivierungs-E-Mail wurde an Ihr Postfach gesendet, bitte überprüfen Sie es (einschließlich des Spam-Ordners).',
    ],
    'aup' => 'Akzeptable Nutzungsrichtlinien',
    'captcha' => [
        'attribute' => 'Captcha',
        'error' => [
            'failed' => 'Captcha-Überprüfung fehlgeschlagen, bitte erneut versuchen',
            'timeout' => 'Captcha ist abgelaufen, bitte aktualisieren und erneut versuchen.',
        ],
        'required' => 'Bitte vervollständigen Sie das Captcha!',
        'sent' => 'Captcha wurde an Ihre E-Mail gesendet, bitte überprüfen Sie es (einschließlich des Spam-Ordners).',
    ],
    'email' => [
        'error' => [
            'banned' => 'Ihr E-Mail-Anbieter ist blockiert, bitte verwenden Sie eine andere E-Mail.',
            'invalid' => 'Ihre E-Mail wird nicht unterstützt.',
        ],
    ],
    'error' => [
        'account_baned' => 'Ihr Konto wurde gesperrt!',
        'login_error' => 'Anmeldefehler, bitte versuchen Sie es später erneut!',
        'login_failed' => 'Anmeldung fehlgeschlagen, bitte überprüfen Sie Ihren Benutzernamen und Ihr Passwort!',
        'not_found_user' => 'Kein Konto gefunden, bitte versuchen Sie andere Anmeldemethoden.',
        'repeat_request' => 'Bitte keine wiederholten Anfragen, aktualisieren und erneut versuchen.',
        'url_timeout' => 'Der Link ist abgelaufen, bitte erneut anfordern.',
    ],
    'failed' => 'Diese Kombination aus Zugangsdaten wurde nicht in unserer Datenbank gefunden.',
    'invite' => [
        'attribute' => 'Einladungscode',
        'error' => [
            'unavailable' => 'Ungültiger Einladungscode, bitte erneut versuchen.',
        ],
        'get' => 'Einladungscode erhalten',
        'not_required' => 'Kein Einladungscode erforderlich, Sie können sich direkt registrieren!',
    ],
    'login' => 'Anmelden',
    'logout' => 'Abmelden',
    'maintenance' => 'Wartung',
    'maintenance_tip' => 'In Wartung',
    'oauth' => [
        'bind_failed' => 'Bindung fehlgeschlagen',
        'bind_success' => 'Bindung erfolgreich',
        'login_failed' => 'Drittanbieter-Anmeldung fehlgeschlagen!',
        'rebind_success' => 'Neubindung erfolgreich',
        'register' => 'Schnellregistrierung',
        'register_failed' => 'Registrierung fehlgeschlagen',
        'registered' => 'Bereits registriert, bitte direkt anmelden.',
        'unbind_failed' => 'Entbindung fehlgeschlagen',
        'unbind_success' => 'Entbindung erfolgreich',
    ],
    'one-click_login' => 'Ein-Klick-Anmeldung',
    'optional' => 'Optional',
    'password' => [
        'forget' => 'Passwort vergessen?',
        'new' => 'Neues Passwort eingeben',
        'original' => 'Aktuelles Passwort',
        'reset' => [
            'attribute' => 'Passwort zurücksetzen',
            'error' => [
                'demo' => 'Ändern des Administratorpassworts im Demomodus nicht möglich.',
                'disabled' => 'Passwortzurücksetzung deaktiviert, bitte kontaktieren Sie :email für Unterstützung.',
                'failed' => 'Passwortzurücksetzung fehlgeschlagen.',
                'same' => 'Das neue Passwort darf nicht mit dem alten übereinstimmen, bitte erneut eingeben.',
                'throttle' => 'Sie können das Passwort nur :time Mal in 24 Stunden zurücksetzen, bitte nicht zu häufig operieren.',
                'wrong' => 'Falsches Passwort, bitte erneut versuchen.',
            ],
            'sent' => 'Zurücksetzungslink wurde an Ihr Postfach gesendet, bitte überprüfen Sie es (einschließlich des Spam-Ordners).',
            'success' => 'Neues Passwort erfolgreich zurückgesetzt, Sie können sich jetzt anmelden.',
        ],
    ],
    'register' => [
        'attribute' => 'Registrieren',
        'code' => 'Registrierungscode',
        'error' => [
            'disable' => 'Entschuldigung, wir nehmen derzeit keine neuen Benutzer auf.',
            'throttle' => 'Anti-Bot-System aktiviert! Bitte vermeiden Sie häufige Einreichungen!',
        ],
        'failed' => 'Registrierung fehlgeschlagen, bitte später erneut versuchen.',
        'promotion' => 'Noch kein Konto? Bitte gehen Sie zu ',
        'success' => 'Erfolgreich registriert',
    ],
    'remember_me' => 'Angemeldet bleiben',
    'request' => 'Anfordern',
    'throttle' => 'Zu viele Loginversuche. Versuchen Sie es bitte in :seconds Sekunden nochmal.',
    'tos' => 'Nutzungsbedingungen',
];
