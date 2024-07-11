<?php

declare(strict_types=1);

return [
    'accept_term' => 'Tôi đã đọc và đồng ý tuân thủ',
    'active' => [
        'attribute' => 'Kích hoạt',
        'error' => [
            'activated' => 'Tài khoản đã được kích hoạt, không cần kích hoạt lại',
            'disable' => 'Chức năng kích hoạt tài khoản đã bị tắt, bạn có thể đăng nhập trực tiếp!',
            'throttle' => 'Bạn đã đạt đến giới hạn yêu cầu kích hoạt, vui lòng thử lại sau. Nếu có bất kỳ câu hỏi nào, vui lòng liên hệ :email.',
        ],
        'promotion' => 'Tài khoản chưa được kích hoạt, vui lòng [:action] trước!',
        'sent' => 'Email kích hoạt đã được gửi đến hộp thư của bạn, vui lòng kiểm tra (bao gồm cả thư mục spam).',
    ],
    'aup' => 'Chính sách Sử dụng Chấp nhận được',
    'captcha' => [
        'attribute' => 'Mã xác nhận',
        'error' => [
            'failed' => 'Xác minh mã xác nhận thất bại, vui lòng thử lại',
            'timeout' => 'Mã xác nhận đã hết hạn, vui lòng làm mới và thử lại.',
        ],
        'required' => 'Vui lòng hoàn thành mã xác nhận!',
        'sent' => 'Mã xác nhận đã được gửi đến email của bạn, vui lòng kiểm tra (bao gồm cả thư mục spam).',
    ],
    'email' => [
        'error' => [
            'banned' => 'Nhà cung cấp email của bạn đã bị chặn, vui lòng sử dụng email khác.',
            'invalid' => 'Email của bạn không được hỗ trợ.',
        ],
    ],
    'error' => [
        'account_baned' => 'Tài khoản của bạn đã bị cấm!',
        'login_error' => 'Lỗi đăng nhập, vui lòng thử lại sau!',
        'login_failed' => 'Đăng nhập thất bại, vui lòng kiểm tra tên người dùng và mật khẩu!',
        'not_found_user' => 'Không tìm thấy tài khoản, vui lòng thử các phương thức đăng nhập khác.',
        'repeat_request' => 'Vui lòng không lặp lại yêu cầu, làm mới và thử lại.',
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
        'bind_failed' => 'Liên kết thất bại',
        'bind_success' => 'Liên kết thành công',
        'login_failed' => 'Đăng nhập bên thứ ba thất bại!',
        'rebind_success' => 'Liên kết lại thành công',
        'register' => 'Đăng ký nhanh',
        'register_failed' => 'Đăng ký thất bại',
        'registered' => 'Đã đăng ký, vui lòng đăng nhập trực tiếp.',
        'unbind_failed' => 'Hủy liên kết thất bại',
        'unbind_success' => 'Hủy liên kết thành công',
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
                'demo' => 'Không thể thay đổi mật khẩu quản trị viên trong chế độ demo.',
                'disabled' => 'Chức năng đặt lại mật khẩu đã bị tắt, vui lòng liên hệ :email để được hỗ trợ.',
                'failed' => 'Đặt lại mật khẩu thất bại.',
                'same' => 'Mật khẩu mới không thể giống mật khẩu cũ, vui lòng nhập lại.',
                'throttle' => 'Bạn chỉ có thể đặt lại mật khẩu :time lần trong 24 giờ, vui lòng không thực hiện quá thường xuyên.',
                'wrong' => 'Sai mật khẩu, vui lòng thử lại.',
            ],
            'sent' => 'Liên kết đặt lại đã được gửi đến hộp thư của bạn, vui lòng kiểm tra (bao gồm cả thư mục spam).',
            'success' => 'Mật khẩu mới đã được đặt lại thành công, bạn có thể đăng nhập ngay bây giờ.',
        ],
    ],
    'register' => [
        'attribute' => 'Đăng ký',
        'code' => 'Mã đăng ký',
        'error' => [
            'disable' => 'Xin lỗi, chúng tôi tạm thời ngừng nhận người dùng mới.',
            'throttle' => 'Hệ thống chống bot đã được kích hoạt! Vui lòng tránh gửi quá nhiều lần!',
        ],
        'failed' => 'Đăng ký thất bại, vui lòng thử lại sau.',
        'promotion' => 'Chưa có tài khoản? Vui lòng đến ',
        'success' => 'Đăng ký thành công',
    ],
    'remember_me' => 'Ghi nhớ tôi',
    'request' => 'Yêu cầu',
    'throttle' => 'Vượt quá số lần đăng nhập cho phép. Vui lòng thử lại sau :seconds giây.',
    'tos' => 'Điều khoản Dịch vụ',
];
