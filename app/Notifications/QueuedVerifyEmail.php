<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

/**
 * Email xác thực được gửi qua queue (không chặn HTTP request).
 * Thay thế VerifyEmail mặc định của Laravel để tránh 504 khi SMTP chậm.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Số lần thử lại nếu job thất bại.
     */
    public int $tries = 3;

    /**
     * Timeout (giây) cho mỗi lần thử.
     */
    public int $timeout = 30;
}
