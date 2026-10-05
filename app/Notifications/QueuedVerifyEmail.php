<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email xác thực tài khoản qua API hoặc Queue.
 * Tùy biến nội dung tiếng Việt chuẩn thương hiệu Table Shop.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;

    /**
     * Tùy biến thông điệp email xác thực tiếng Việt.
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('[Table Shop] Xác thực địa chỉ email tài khoản của bạn')
            ->greeting('Xin chào quý khách!')
            ->line('Cảm ơn bạn đã đăng ký tài khoản tại Table Shop.')
            ->line('Vui lòng bấm vào nút bên dưới để xác thực địa chỉ email và hoàn tất kích hoạt tài khoản của bạn:')
            ->action('Xác thực tài khoản ngay', $url)
            ->line('Liên kết xác thực này sẽ hết hạn sau 60 phút.')
            ->line('Nếu bạn không thực hiện đăng ký tài khoản này, vui lòng bỏ qua email này.')
            ->salutation('Trân trọng, Đội ngũ Table Shop');
    }
}
