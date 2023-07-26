<?php

declare(strict_types=1);

return [
    'accept_term' => 'I have read and accepted',
    'active' => [
        'attribute' => 'Activate',
        'error' => [
            'activated' => 'Account already activated, no need to reactivate',
            'disable' => 'Account activation disabled, you can sign in directly!',
            'throttle' => 'Activation request limit reached, please do not operate too frequently! Contact :email if you have any questions.',
        ],
        'promotion' => 'Account not yet activated, please [:action] first!',
        'sent' => 'Activation email has been sent to your mailbox, please check including spam folder.',
    ],
    'aup' => 'Acceptable Use Policy',
    'captcha' => [
        'attribute' => 'Captcha',
        'error' => [
            'failed' => 'Captcha verification failed, please try again',
            'timeout' => 'Captcha expired, please refresh and retry.',
        ],
        'required' => 'Please complete the captcha!',
        'sent' => 'Captcha sent to your email, please check including spam folder.',
    ],
    'email' => [
        'error' => [
            'banned' => 'Your email provider is blocked, please use another email.',
            'invalid' => 'Your email is not supported.',
        ],
    ],
    'error' => [
        'account_baned' => 'Your account is banned!',
        'login_error' => 'Login error, please retry later!',
        'login_failed' => 'Login failed, please check email and password!',
        'not_found_user' => 'No account found, please try other login methods.',
        'repeat_request' => 'Please refresh and try again.',
        'url_timeout' => 'Link expired, please request again.',
    ],
    'failed' => 'Invalid credentials.',
    'invite' => [
        'attribute' => 'Invitation Code',
        'error' => [
            'unavailable' => 'Invalid invitation code, please retry.',
        ],
        'get' => 'Get invitation code',
        'not_required' => 'No invitation code required, you can register directly!',
    ],
    'login' => 'Login',
    'logout' => 'Logout',
    'maintenance' => 'Maintenance',
    'maintenance_tip' => 'Under maintenance',
    'oauth' => [
        'bind_failed' => 'Binding failed',
        'bind_success' => 'Binding successful',
        'login_failed' => 'Third-party login failed!',
        'rebind_success' => 'Rebinding successful',
        'register' => 'Quick Register',
        'register_failed' => 'Registration failed',
        'registered' => 'Already registered, please login directly.',
        'unbind_failed' => 'Unbinding failed',
        'unbind_success' => 'Unbinding successful',
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
                'demo' => 'Cannot change admin password in demo.',
                'disabled' => 'Password reset disabled, please contact :email for assistance.',
                'failed' => 'Password reset failed.',
                'same' => 'New password cannot be the same as old, please re-enter.',
                'throttle' => 'You can only reset password :time times in 24 hours, do not operate too frequently.',
                'wrong' => 'Incorrect password, please try again.',
            ],
            'sent' => 'Reset link sent to your mailbox, please check including spam folder.',
            'success' => 'New password reset successfully, you can now login.',
        ],
    ],
    'register' => [
        'attribute' => 'Sign Up',
        'code' => 'Registration Code',
        'error' => [
            'disable' => 'Sorry, we have temporarily stopped accepting new users.',
            'throttle' => 'Anti-bot active! Please do not send registration forms too frequently!',
        ],
        'failed' => 'Registration failed, please try later.',
        'promotion' => 'No account yet? Please go to ',
        'success' => 'Registration successful',
    ],
    'remember_me' => 'Remember Me',
    'request' => 'Request',
    'throttle' => 'Too many attempts, please try again in :seconds seconds.',
    'tos' => 'Terms of Service',
];
