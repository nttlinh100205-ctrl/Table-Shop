<?php
namespace App\Exceptions;

class AiUnavailableException extends \RuntimeException
{
    public function __construct(public string $reason)
    {
        parent::__construct(match ($reason) {
            'AI_RATE_LIMIT' => 'AI đang hết hạn mức hoặc quá tải. Vui lòng thử lại sau hoặc chọn Nhân viên.',
            'AI_TIMEOUT' => 'AI phản hồi quá chậm. Vui lòng thử lại hoặc chọn Nhân viên.',
            'AI_KEY_MISSING', 'AI_ACCESS_DENIED', 'AI_INVALID_KEY', 'AI_MODEL_UNAVAILABLE' => 'AI đang gặp lỗi cấu hình kết nối. Bạn vui lòng chọn Nhân viên để được tư vấn.',
            default => 'AI tạm thời không khả dụng. Vui lòng thử lại hoặc chọn Nhân viên.',
        });
    }
}
