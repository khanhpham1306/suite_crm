<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$mod_strings = array(
    'LBL_NAME' => 'Tên công việc',
    'LBL_EXECUTE_TIME' => 'Thời gian thực hiện',
    'LBL_SCHEDULER_ID' => 'Trình lập lịch',
    'LBL_STATUS' => 'Tình trạng việc làm',
    'LBL_RESOLUTION' => 'Kết quả',
    'LBL_MESSAGE' => 'Tin nhắn',
    'LBL_DATA' => 'Dữ liệu công việc',
    'LBL_REQUEUE' => 'Thử lại khi thất bại',
    'LBL_RETRY_COUNT' => 'Số lần thử lại tối đa',
    'LBL_FAIL_COUNT' => 'Thất bại',
    'LBL_INTERVAL' => 'Khoảng thời gian tối thiểu giữa các lần thử',
    'LBL_CLIENT' => 'Sở hữu khách hàng',
    'LBL_PERCENT' => 'Phần trăm hoàn thành',
    'ERR_CALL' => 'Không thể gọi hàm: %s',
    'ERR_CURL' => 'Không có CURL - không thể chạy các công việc URL',
    'ERR_FAILED' => 'Lỗi không mong muốn, vui lòng kiểm tra nhật ký PHP và suitecrm.log',
    'ERR_PHP' => '%s [%d]: %s trong %s trên dòng %d',
    'ERR_NOUSER' => 'Không có ID người dùng nào được chỉ định cho công việc',
    'ERR_NOSUCHUSER' => 'Không tìm thấy ID người dùng %s',
    'ERR_JOBTYPE' => 'Loại công việc không xác định: %s',
    'ERR_TIMEOUT' => 'Buộc phải thất bại khi hết thời gian chờ',
    'ERR_JOB_FAILED_VERBOSE' => 'Công việc %1$s (%2$s) không thành công khi chạy CRON',
);
