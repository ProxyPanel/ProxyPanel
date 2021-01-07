<?php

return [
    'accept_term'     => 'I have read and agree to obey',
    'active'          => [
        'attribute' => 'Activation',
        'error'     => [
            'activated' => 'The account has been activated, no need to activate again',
            'disable'   => 'The account activation subsystem has been disabled, you can login directly!',
            'throttle'  => 'Anti-bots Shield Active! Please do not send multiple activate from at short amount of times! If you have any questions, please contact :email',
        ],
        'promotion' => ['0' => 'Account has not been activated，Please', '1' => 'before login！'],
        'sent'      => 'Email has been sent! Please check your mailbox! (Email may be in the Trash)',
    ],
    'aup'             => 'Acceptable Use Policy',
    'captcha'         => [
        'attribute' => 'Captcha',
        'error'     => ['failed' => 'Verification failed, please try again', 'timeout' => 'The code is invalid! Please refresh and try again'],
        'required'  => 'Please complete the Captcha operation!',
        'sent'      => 'Email has been sent! Please check your mailbox! (Email may be in the Trash)',
    ],
    'email'           => [
        'error' => [
            'banned'  => 'Your email service provider was banned by our platform. Please use another valid email',
            'invalid' => 'Your email service provider is not in our supporting list. Please use another email',
        ],
    ],
    'error'           => [
        'account_baned'  => 'Your account has been banned!',
        'login_error'    => 'Login error, please try again later!',
        'login_failed'   => 'Login failed, please check whether the email or password is entered correctly!',
        'repeat_request' => 'Please refresh the page and try again',
        'url_timeout'    => 'The link has expired, please try again',
    ],
    'invite'          => [
        'attribute'    => 'Invitation code',
        'error'        => ['unavailable' => 'Invitation code is invalid!'],
        'get'          => 'Click to get the invitation code',
        'not_required' => 'No invitation code is required, you can register directly!',
    ],
    'login'           => 'Login',
    'logout'          => 'Logout',
    'maintenance'     => 'Maintain',
    'maintenance_tip' => 'Website under maintenance',
    'optional'        => 'Optional',
    'password'        => [
        'forget'   => 'forget password?',
        'new'      => 'Enter a new password',
        'original' => 'Original Password',
        'reset'    => [
            'attribute' => 'Reset Password',
            'error'     => [
                'disabled' => 'The password reset subsystem has been disabled, you can contact :email for help',
                'failed'   => 'Password reset failed',
                'throttle' => 'Password can only be reset :time times within 24 hours, Please Do not operate too frequently',
                'same'     => 'The new password cannot be the same as the old password, please re-enter',
                'wrong'    => 'The password is incorrect, please re-enter',
                'demo'     => 'You can not change administrator password under Demo environment',
            ],
            'sent'      => 'Reset link has been sent to your email, please check the mailbox (the email may be in the Trash)',
            'success'   => 'The password has been reset successfully, please go to login',
        ],
    ],
    'register'        => [
        'attribute' => 'Register',
        'code'      => 'Registration Code',
        'error'     => [
            'disable'  => 'Sorry, The registration function has been disabled',
            'throttle' => 'Anti-bots shield is active! Please do not send multiple register from at short amount of times!',
        ],
        'promotion' => 'No account yet? Please go to',
        'failed'    => 'Registration failed, please try later',
        'success'   => 'Registration successfully',
    ],
    'remember_me'     => 'Remember me',
    'request'         => 'Request',
    'tos'             => 'Terms of Service',
];
