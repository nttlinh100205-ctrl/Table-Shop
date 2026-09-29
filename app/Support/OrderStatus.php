<?php

namespace App\Support;

/**
 * Nhãn + quy tắc chuyển trạng thái đơn hàng / vận chuyển.
 */
class OrderStatus
{
    public const ORDER = [
        'pending'     => 'Chờ xử lý',
        'paid'        => 'Đã thanh toán',
        'paid_momo'   => 'Đã thanh toán MoMo',
        'cod_ordered' => 'COD — đã lên đơn',
        'cod_paid'    => 'COD — đã thu',
        'confirmed'   => 'Đã xác nhận',
        'completed'   => 'Hoàn thành',
        'cancel_requested' => 'Chờ duyệt hủy',
        'cancelled'   => 'Đã hủy',
        'failed'      => 'Thất bại',
    ];

    public const SHIPPING = [
        'pending'             => 'Chờ tạo vận đơn',
        'not_shipped'         => 'Chưa giao hàng',
        'processing'          => 'Đang xử lý',
        'ready_to_pick'       => 'Chờ lấy hàng',
        'picking'             => 'Đang lấy hàng',
        'picked'              => 'Đã lấy hàng',
        'storing'             => 'Đang lưu kho',
        'transporting'        => 'Đang trung chuyển',
        'sorting'             => 'Đang phân loại',
        'delivering'          => 'Đang giao hàng',
        'delivered'           => 'Giao thành công',
        'return'              => 'Yêu cầu trả hàng',
        'returning'           => 'Đang hoàn hàng',
        'returned'            => 'Đã hoàn hàng',
        'return_transporting' => 'Đang chuyển hoàn',
        'return_sorting'      => 'Phân loại hoàn',
        'cancelled'           => 'Đã hủy VC',
    ];

    public const RETURN = [
        'requested' => 'Chờ duyệt trả',
        'approved'  => 'Đã duyệt — đang hoàn',
        'rejected'  => 'Từ chối trả hàng',
        'completed' => 'Hoàn tất trả hàng',
    ];

    /** Badge Bootstrap class theo shipping_status */
    public static function shipBadge(string $status): string
    {
        return match ($status) {
            'delivered' => 'bg-success',
            'delivering', 'picked', 'storing', 'transporting', 'sorting' => 'bg-primary',
            'ready_to_pick', 'picking', 'processing' => 'bg-info text-dark',
            'return', 'returning', 'return_transporting', 'return_sorting' => 'bg-warning text-dark',
            'returned' => 'bg-secondary',
            'cancelled' => 'bg-danger',
            default => 'bg-light text-dark border',
        };
    }

    public static function orderBadge(string $status): string
    {
        return match ($status) {
            'paid', 'paid_momo', 'completed' => 'bg-success',
            'cod_ordered', 'cod_paid', 'confirmed' => 'bg-primary',
            'pending' => 'bg-warning text-dark',
            'cancelled', 'failed' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function orderLabel(?string $status): string
    {
        return self::ORDER[$status] ?? ($status ?: '—');
    }

    public static function shipLabel(?string $status): string
    {
        return self::SHIPPING[$status] ?? ($status ?: '—');
    }

    public static function returnLabel(?string $status): string
    {
        return self::RETURN[$status] ?? ($status ?: '—');
    }

    public static function bulkOrderOptions(): array
    {
        // Chỉ các trạng thái có nghĩa để đổi hàng loạt:
        // - Loại bỏ cancel_requested, cancelled (có flow riêng)
        // - Loại bỏ failed (không áp dụng thủ công)
        return array_diff_key(self::ORDER, array_flip(['cancel_requested', 'cancelled', 'failed']));
    }

    /**
     * Trạng thái vận chuyển admin được phép đổi hàng loạt.
     * Không gồm: return*, returned, cancelled (có flow riêng), pending (chưa xử lý).
     */
    public static function bulkShipOptions(): array
    {
        return array_intersect_key(self::SHIPPING, array_flip([
            'processing',
            'ready_to_pick',
            'picking',
            'delivering',
            'delivered',
        ]));
    }

    /**
     * Trạng thái VC admin được phép chọn tay (không gồm return*).
     */
    public static function manualShipOptions(): array
    {
        return [
            'pending',
            'processing',
            'ready_to_pick',
            'picking',
            'delivering',
            'delivered',
            'cancelled',
        ];
    }

    /**
     * Chuyển bước gợi ý tiếp theo (pipeline).
     */
    public static function nextShip(?string $current): ?string
    {
        $pipe = [
            'pending'       => 'ready_to_pick',
            'processing'    => 'ready_to_pick',
            'ready_to_pick' => 'picking',
            'picking'       => 'delivering',
            'delivering'    => 'delivered',
        ];

        return $pipe[$current] ?? null;
    }
}
