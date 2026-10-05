<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ReferralRewarded extends Notification
{
    public function __construct(public int $orderId, public int $points) {}
    public function via($notifiable): array { return ['database']; }
    public function toArray($notifiable): array
    {
        return ['order_id' => $this->orderId, 'points' => $this->points,
            'message' => "Bạn được cộng {$this->points} điểm giới thiệu từ đơn #{$this->orderId}."];
    }
}
