<?php

declare(strict_types=1);

return [
    'accept_term' => 'Tôi đã đọc và đồng ý với những điều sau',
    'active' => [
        'attribute' => 'Kích hoạt tài khoản',
        'error' => [
            'activated' => 'Tài khoản đã được kích hoạt, vui lòng đăng nhập trực tiếp!',
            'disable' => 'Trang web này đã tắt chức năng kích hoạt tài khoản, bạn có thể đăng nhập trực tiếp!',
            'throttle' => 'Đã đạt giới hạn yêu cầu kích hoạt, vui lòng thử lại sau!',
        ],
        'promotion' => 'Tài khoản chưa được kích hoạt, vui lòng「:action」trước!',
        'sent' => 'Đã gửi link kích hoạt đến địa chỉ email của bạn, vui lòng chờ một chút hoặc kiểm tra thư mục spam.',
    ],
    'aup' => 'Điều khoản sử dụng',
    'captcha' => [
        'attribute' => 'Mã xác thực',
        'error' => [
            'failed' => 'Mã xác thực không đúng, vui lòng nhập lại!',
            'timeout' => 'Mã xác thực đã hết hạn, vui lòng làm mới trang và thử lại!',
        ],
        'required' => 'Vui lòng hoàn thành mã xác thực chính xác',
        'sent' => 'Đã gửi mã xác thực đến địa chỉ email của bạn, vui lòng chờ một chút hoặc kiểm tra thư mục spam.',
    ],
    'email' => [
        'error' => [
            'banned' => 'Trang web này không hỗ trợ nhà cung cấp dịch vụ email của bạn, vui lòng sử dụng địa chỉ email khác!',
            'invalid' => 'Địa chỉ email bạn nhập không được hỗ trợ bởi trang web này!',
        ],
    ],
    'error' => [
        'account_baned' => 'Tài khoản của bạn đã bị cấm!',
        'login_error' => 'Đã xảy ra lỗi trong quá trình đăng nhập, vui lòng thử lại sau!',
        'login_failed' => 'Đăng nhập thất bại, vui lòng kiểm tra tên người dùng hoặc mật khẩu có đúng không!',
        'not_found_user' => 'Không tìm thấy tài khoản liên quan, vui lòng thử phương thức đăng nhập khác!',
        'repeat_request' => 'Vui lòng tránh yêu cầu trùng lặp, làm mới trang và thử lại!',
        'url_timeout' => 'Link đã hết hạn, vui lòng thao tác lại!',
    ],
    'failed' => 'Tên người dùng hoặc mật khẩu không đúng.',
    'invite' => [
        'get' => 'Lấy mã mời',
        'not_required' => 'Không cần mã mời, bạn có thể đăng ký trực tiếp!',
        'unavailable' => 'Mã mời không hợp lệ, vui lòng thử lại!',
    ],
    'login' => 'Đăng nhập',
    'logout' => 'Đăng xuất',
    'maintenance' => 'Bảo trì hệ thống',
    'maintenance_tip' => 'Hệ thống đang bảo trì, vui lòng truy cập lại sau!',
    'oauth' => [
        'login_failed' => 'Đăng nhập bên thứ ba thất bại!',
        'register' => 'Đăng ký nhanh',
        'registered' => 'Đã đăng ký, vui lòng đăng nhập trực tiếp.',
    ],
    'one-click_login' => 'Đăng nhập một cú nhấp',
    'optional' => 'Tùy chọn',
    'password' => [
        'forget' => 'Quên mật khẩu?',
        'new' => 'Nhập mật khẩu mới',
        'original' => 'Mật khẩu hiện tại',
        'reset' => [
            'attribute' => 'Đặt lại mật khẩu',
            'error' => [
                'demo' => 'Môi trường demo cấm thay đổi mật khẩu quản trị viên!',
                'disabled' => 'Trang web này đã tắt chức năng đặt lại mật khẩu!',
                'same' => 'Mật khẩu mới không thể giống mật khẩu hiện tại, vui lòng đặt mật khẩu khác!',
                'throttle' => 'Trong 24 giờ chỉ có thể đặt lại mật khẩu :time lần, vui lòng thử lại sau!',
                'wrong' => 'Mật khẩu hiện tại không đúng, vui lòng nhập lại!',
            ],
            'sent' => 'Đã gửi link đặt lại đến địa chỉ email của bạn, vui lòng kiểm tra email (bao gồm thư mục spam).',
            'success' => 'Mật khẩu mới đã được đặt thành công, vui lòng đăng nhập tại trang đăng nhập.',
        ],
    ],
    'register' => [
        'attribute' => 'Đăng ký mới',
        'code' => 'Mã xác thực đăng ký',
        'error' => [
            'disable' => 'Xin lỗi, trang web này hiện đang tạm dừng đăng ký mới.',
            'throttle' => 'Chức năng chống spam đã kích hoạt, vui lòng tránh đăng ký thường xuyên!',
        ],
        'failed' => 'Đăng ký thất bại, vui lòng thử lại sau.',
        'promotion' => 'Chưa có tài khoản? Trước tiên',
    ],
    'remember_me' => 'Duy trì trạng thái đăng nhập',
    'request' => 'Lấy',
    'throttle' => 'Quá nhiều lần thử đăng nhập. Vui lòng thử lại sau :seconds giây.',
    'tos' => 'Điều khoản dịch vụ',
];
