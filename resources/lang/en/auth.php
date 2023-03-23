<?php

return [
    'accept_term'     => 'I have read and accepted',
    'active'          => [
        'attribute' => 'Active',
        'error'     => [
            'activated' => 'Your account has been activated, no need to re-activate',
            'disable'   => 'Account activation has been disabled, you can sign in directly!',
            'throttle'  => 'You have triggered the activation request restriction, please do not operate too frequent! If you have any questions, please contact: email',
        ],
        'promotion' => 'Account not yet activated, please [:action] first!',
        'sent'      => 'Activation Email has sent to your mailbox, please check your mailbox (Email may be in the Trash)',
    ],
    'aup'             => 'Acceptable Use Policy',
    'captcha'         => [
        'attribute' => 'Captcha',
        'error'     => [
            'failed'  => 'Captcha verification failed, please try again',
            'timeout' => 'Invalid verification code! It maybe expired, please refresh and try again.',
        ],
        'required'  => 'Please complete the Captcha operation!',
        'sent'      => 'Email has been sent! Please check your mailbox! (Email may be in the Trash)',
    ],
    'email'           => [
        'error' => [
            'banned'  => 'Your email service provider was banned by our platform. Please use another valid email',
            'invalid' => 'Your email service provider is not in our supported list. Please use another email
',
        ],
    ],
    'error'           => [
        'account_baned'  => 'Your account has been banned!',
        'login_error'    => 'Login error, please try again later!',
        'login_failed'   => 'Login failed, please check whether the email or password is entered correctly!',
        'not_found_user' => 'No associated account found, please use other ways to login',
        'repeat_request' => 'Please refresh the page and try again',
        'url_timeout'    => 'The link has expired, please try again',
    ],
    'failed'          => 'These credentials do not match our records.',
    'invite'          => [
        'attribute'    => 'Invitation code',
        'error'        => [
            'unavailable' => 'Invitation code is invalid!',
        ],
        'get'          => 'Click to get the invitation code',
        'not_required' => 'No invitation code is required, you can sign up directly!',
    ],
    'login'           => 'Sign in',
    'logout'          => 'Logout',
    'maintenance'     => 'Maintenance',
    'maintenance_tip' => 'Maintenance in progress',
    'oauth'           => [
        'bind_failed'     => 'Binding failed',
        'bind_success'    => 'Binding successfully',
        'login_failed'    => 'Third-party login failed!',
        'rebind_success'  => 'Re-binding successfully',
        'register'        => 'Quick Registration',
        'register_failed' => 'Registration failed',
        'registered'      => 'Already registered, please sign in directly',
        'unbind_failed'   => 'Unbinding failed',
        'unbind_success'  => 'Unbinding successfully',
    ],
    'optional'        => 'Optional',
    'password'        => [
        'forget'   => 'Forgot password?',
        'new'      => 'Enter a new password',
        'original' => 'Current Password',
        'reset'    => [
            'attribute' => 'Reset Password',
            'error'     => [
                'disabled' => 'We have disabled the password reset function, please contact :email for help',
                'failed'   => 'Password reset failed',
                'throttle' => 'You can only reset your password :time times within 24 hours, please do not operate frequently.',
                'same'     => 'New password cannot be the same as old password, please re-enter',
                'wrong'    => 'Password incorrect, please try again',
                'demo'     => 'You can not change administrator password under Demo environment',
            ],
            'sent'      => 'Reset link has sent to your mailbox, please check the email (Email may be in the Trash)',
            'success'   => 'New password set successfully, you can sign in now',
        ],
    ],
    'register'        => [
        'attribute' => 'Sign up',
        'code'      => 'Registration Code',
        'error'     => [
            'disable'  => 'Sorry, we have temporarily stopped accepting new users',
            'throttle' => 'Anti-bots is active! Please do not send register forms too frequently!',
        ],
        'failed'    => 'Registration failed, please try later',
        'promotion' => 'Still no account? Please go to ',
        'success'   => 'Registration successfully',
    ],
    'remember_me'     => 'Remember me',
    'request'         => 'Request',
    'throttle'        => 'Too many login attempts. Please try again in :seconds seconds.',
    'tos'             => 'Terms of Service',
];
