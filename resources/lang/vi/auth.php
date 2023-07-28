<?php

declare(strict_types=1);

return [
    'accept_term' => 'Tôi đã đọc và chấp nhận',
    'active' => [
        'attribute' => 'Kích hoạt',
        'error' => [
            'activated' => 'Tài khoản đã kích hoạt, không cần kích hoạt lại',
            'disable' => 'Kích hoạt tài khoản bị tắt, bạn có thể đăng nhập trực tiếp!',
            'throttle' => 'Đã vượt quá giới hạn yêu cầu kích hoạt, vui lòng không thao tác quá thường xuyên! Liên hệ :email nếu có vấn đề.',
        ],
        'promotion' => 'Tài khoản chưa kích hoạt, vui lòng [:action] trước!',
        'sent' => 'Email kích hoạt đã được gửi đến hộp thư của bạn, vui lòng kiểm tra kể cả thư rác.',
    ],
    'aup' => 'Chính sách Sử dụng Chấp nhận được',
    'captcha' => [
        'attribute' => 'Mã xác nhận',
        'error' => [
            'failed' => 'Xác minh mã không thành công, vui lòng thử lại',
            'timeout' => 'Mã xác nhận đã hết hạn, vui lòng làm mới và thử lại.',
        ],
        'required' => 'Vui lòng hoàn thành mã xác nhận!',
        'sent' => 'Mã xác nhận đã được gửi đến email của bạn, vui lòng kiểm tra kể cả thư rác.',
    ],
    'email' => [
        'error' => [
            'banned' => 'Nhà cung cấp email của bạn bị chặn, vui lòng sử dụng email khác.',
            'invalid' => 'Email của bạn không được hỗ trợ.',
        ],
    ],
    'error' => [
        'account_baned' => 'Tài khoản của bạn bị cấm!',
        'login_error' => 'Lỗi đăng nhập, vui lòng thử lại sau!',
        'login_failed' => 'Đăng nhập không thành công, vui lòng kiểm tra email và mật khẩu!',
        'not_found_user' => 'Không tìm thấy tài khoản, vui lòng thử cách đăng nhập khác.',
        'repeat_request' => 'Vui lòng làm mới và thử lại.',
        'url_timeout' => 'Liên kết đã hết hạn, vui lòng yêu cầu lại.',
    ],
    'failed' => 'Thông tin tài khoản không tìm thấy trong hệ thống.',
    'invite' => [
        'attribute' => 'Mã lời mời',
        'error' => [
            'unavailable' => 'Mã lời mời không hợp lệ, vui lòng thử lại.',
        ],
        'get' => 'Nhận mã lời mời',
        'not_required' => 'Không cần mã lời mời, bạn có thể đăng ký trực tiếp!',
    ],
    'login' => 'Đăng nhập',
    'logout' => 'Đăng xuất',
    'maintenance' => 'Bảo trì',
    'maintenance_tip' => 'Đang bảo trì',
    'oauth' => [
        'bind_failed' => 'Gắn kết không thành công',
        'bind_success' => 'Gắn kết thành công',
        'login_failed' => 'Đăng nhập bên thứ ba không thành công!',
        'rebind_success' => 'Gắn kết lại thành công',
        'register' => 'Đăng ký nhanh',
        'register_failed' => 'Đăng ký không thành công',
        'registered' => 'Đã đăng ký, vui lòng đăng nhập trực tiếp.',
        'unbind_failed' => 'Hủy gắn kết không thành công',
        'unbind_success' => 'Hủy gắn kết thành công',
    ],
    'one-click_login' => 'Đăng nhập một chạm',
    'optional' => 'Tùy chọn',
    'password' => [
        'forget' => 'Quên mật khẩu?',
        'new' => 'Nhập mật khẩu mới',
        'original' => 'Mật khẩu hiện tại',
        'reset' => [
            'attribute' => 'Đặt lại mật khẩu',
            'error' => [
                'demo' => 'Không thể thay đổi mật khẩu admin trong demo.',
                'disabled' => 'Đặt lại mật khẩu bị tắt, vui lòng liên hệ :email để được hỗ trợ.',
                'failed' => 'Đặt lại mật khẩu không thành công.',
                'same' => 'Mật khẩu mới không được giống mật khẩu cũ, vui lòng nhập lại.',
                'throttle' => 'Bạn chỉ có thể đặt lại mật khẩu :time lần trong 24 giờ, không nên thao tác quá thường xuyên.',
                'wrong' => 'Mật khẩu sai, vui lòng thử lại.',
            ],
            'sent' => 'Liên kết đặt lại mật khẩu đã được gửi đến email của bạn, vui lòng kiểm tra kể cả thư rác.',
            'success' => 'Mật khẩu mới đã được đặt lại thành công, bạn có thể đăng nhập.',
        ],
    ],
    'register' => [
        'attribute' => 'Đăng ký',
        'code' => 'Mã đăng ký',
        'error' => [
            'disable' => 'Xin lỗi, chúng tôi tạm thời ngừng nhận người dùng mới.',
            'throttle' => 'Chống bot đã kích hoạt! Vui lòng không gửi quá thường xuyên mẫu đăng ký!',
        ],
        'failed' => 'Đăng ký không thành công, vui lòng thử lại sau.',
        'promotion' => 'Chưa có tài khoản? Vui lòng đi đến ',
        'success' => 'Đăng ký thành công',
    ],
    'remember_me' => 'Nhớ tôi',
    'request' => 'Yêu cầu',
    'throttle' => 'Vượt quá số lần đăng nhập cho phép. Vui lòng thử lại sau :seconds giây.',
    'tos' => 'Điều khoản Dịch vụ',
];
