<?php

declare(strict_types=1);

return [
    'accept_term' => 'I have read and agree to',
    'active' => [
        'attribute' => 'Activation',
        'error' => [
            'activated' => 'Account activated, please sign in!',
            'disable' => 'Account activation is disabled, you can sign in directly!',
            'throttle' => 'You have triggered an activation request limit, please try again later!',
        ],
        'promotion' => 'Account not activated yet, please [:action] first!',
        'sent' => 'Activation email has been sent to your mailbox, please check it (including the spam folder).',
    ],
    'aup' => 'Acceptable Use Policy',
    'captcha' => [
        'attribute' => 'Captcha',
        'error' => [
            'failed' => 'Incorrect captcha. Please try again!',
            'timeout' => 'The captcha has expired. Please refresh and try again!',
        ],
        'required' => 'Please complete the captcha!',
        'sent' => 'Captcha sent to your email, please check it (including the spam folder).',
    ],
    'email' => [
        'error' => [
            'banned' => 'Your email provider is blocked, please use another email!',
            'invalid' => 'The email you entered is not supported!',
        ],
    ],
    'error' => [
        'account_baned' => 'Your account has been suspended!',
        'login_error' => 'Login error, please try again later!',
        'login_failed' => 'Login failed, please check your username and password!',
        'not_found_user' => 'No account found, please try other login methods!',
        'repeat_request' => 'Please do not repeat requests, refresh and try again!',
        'url_timeout' => 'The link has expired, please request again!',
    ],
    'failed' => 'Invalid credentials.',
    'invite' => [
        'get' => 'Get invitation code',
        'not_required' => 'No invitation code required, you can register directly!',
        'unavailable' => 'This invitation code is invalid. Please try again!',
    ],
    'login' => 'Login',
    'logout' => 'Logout',
    'maintenance' => 'System maintenance',
    'maintenance_tip' => 'The system is under maintenance. Please check back later!',
    'oauth' => [
        'login_failed' => 'Third-party login failed!',
        'register' => 'Quick Register',
        'registered' => 'Already registered, please login directly.',
    ],
    'one-click_login' => 'One-Click Login',
    'optional' => 'Optional',
    'password' => [
        'forget' => 'Forgot password?',
        'new' => 'Enter a new password',
        'original' => 'Current Password',
        'reset' => [
            'attribute' => 'Reset Password',
            'error' => [
                'demo' => 'Demo environment prohibits changing admin password!',
                'disabled' => 'Password reset disabled on this site!',
                'same' => 'New password cannot be the same as the current one. Please choose a different password!',
                'throttle' => 'You can reset your password up to :time times within 24 hours. Please try again later!',
                'wrong' => 'Old password incorrect, please try again!',
            ],
            'sent' => 'Reset link sent to your mailbox, please check it (including the spam folder).',
            'success' => 'New password reset successfully, you can now login.',
        ],
    ],
    'register' => [
        'attribute' => 'Sign Up',
        'code' => 'Registration Code',
        'error' => [
            'disable' => 'Sorry, we have temporarily stopped accepting new users.',
            'throttle' => 'Anti-bot system activated! Please avoid frequent submissions!',
        ],
        'failed' => 'Registration failed, please try again later.',
        'promotion' => 'Don’t have an account yet? Please',
    ],
    'remember_me' => 'Remember Me',
    'request' => 'Request',
    'throttle' => 'Too many attempts, please try again in :seconds seconds.',
    'tos' => 'Terms of Service',
];
