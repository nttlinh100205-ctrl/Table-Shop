<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'user_id'             => 'integer',
        'discount_value'      => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount'    => 'decimal:2',
        'usage_limit'         => 'integer',
        'used_count'          => 'integer',
        'start_date'          => 'datetime',
        'end_date'            => 'datetime',
        'is_active'           => 'boolean',
    ];

    /**
     * User sở hữu riêng mã này (null nếu là voucher chung toàn shop)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship với đơn hàng áp dụng mã này
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope lọc khuyến mãi theo user (gồm mã chung toàn hệ thống + mã riêng của user)
     */
    public function scopeForUser($query, ?int $userId = null)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    /**
     * Scope lấy các khuyến mãi đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope lấy các khuyến mãi còn hạn sử dụng
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Kiểm tra chương trình đã bắt đầu chưa
     */
    public function hasStarted(): bool
    {
        return empty($this->start_date) || now()->gte($this->start_date);
    }

    /**
     * Kiểm tra chương trình đã hết hạn chưa (HSD)
     */
    public function hasExpired(): bool
    {
        return !empty($this->end_date) && now()->gt($this->end_date);
    }

    /**
     * Kiểm tra chương trình đã hết lượt dùng chưa
     */
    public function isExhausted(): bool
    {
        return !is_null($this->usage_limit) && $this->used_count >= $this->usage_limit;
    }

    /**
     * Số lượt dùng còn lại (null nếu không giới hạn)
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if (is_null($this->usage_limit)) {
            return null;
        }

        return max(0, $this->usage_limit - $this->used_count);
    }

    /**
     * Kiểm tra mã khuyến mãi có hợp lệ hay không (có giải thích lý do cụ thể)
     */
    public function isValid(?float $orderTotal = null, ?string &$errorMsg = null, ?int $checkUserId = null): bool
    {
        if (!$this->is_active) {
            $errorMsg = 'Mã khuyến mãi hiện đang bị tạm khóa hoặc ngừng hoạt động.';
            return false;
        }

        // Nếu mã khuyến mãi được gắn riêng cho 1 user thì chỉ user đó mới được dùng
        if (!empty($this->user_id)) {
            $effectiveUserId = $checkUserId ?? auth()->id();
            if (!$effectiveUserId || (int) $this->user_id !== (int) $effectiveUserId) {
                $errorMsg = 'Mã voucher này là phần quà ưu đãi cá nhân, chỉ chủ tài khoản sở hữu mới có quyền áp dụng.';
                return false;
            }
        }

        if (!$this->hasStarted()) {
            $errorMsg = 'Chương trình khuyến mãi chưa bắt đầu (bắt đầu lúc ' . $this->start_date->format('d/m/Y H:i') . ').';
            return false;
        }

        if ($this->hasExpired()) {
            $errorMsg = 'Mã khuyến mãi đã hết hạn sử dụng (hạn chót ' . $this->end_date->format('d/m/Y H:i') . ').';
            return false;
        }

        if ($this->isExhausted()) {
            $errorMsg = 'Mã khuyến mãi đã hết số lượt sử dụng (' . $this->usage_limit . ' lượt).';
            return false;
        }

        if (!is_null($orderTotal) && $this->min_order_amount > 0 && $orderTotal < (float) $this->min_order_amount) {
            $errorMsg = 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($this->min_order_amount, 0, ',', '.') . 'đ để áp dụng mã này.';
            return false;
        }

        return true;
    }

    /**
     * Kiểm tra điều kiện áp dụng cho đơn hàng và trả về thông tin chi tiết
     */
    public function getEligibilityInfo(?float $orderTotal = null): array
    {
        $errorMsg = null;
        $isValid = $this->isValid($orderTotal, $errorMsg);
        $needMore = 0;

        if (!$isValid && !is_null($orderTotal) && (float) $this->min_order_amount > $orderTotal) {
            $needMore = (float) $this->min_order_amount - $orderTotal;
        }

        return [
            'is_eligible'     => $isValid,
            'reason'          => $errorMsg,
            'need_more'       => $needMore,
            'discount_amount' => ($isValid && !is_null($orderTotal)) ? $this->calculateDiscount($orderTotal) : 0,
        ];
    }

    /**
     * Tính toán số tiền được giảm dựa trên tổng tiền hàng
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            $discount = ($orderTotal * (float) $this->discount_value) / 100;
            if (!is_null($this->max_discount_amount) && $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            // Giảm theo số tiền cố định
            $discount = (float) $this->discount_value;
        }

        // Không bao giờ giảm vượt quá tổng tiền hàng
        return (float) min($orderTotal, round($discount));
    }

    /**
     * Tăng số lượt dùng
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Chuỗi tóm tắt mức giảm giá
     */
    public function getDiscountDisplayAttribute(): string
    {
        if ($this->discount_type === 'percent') {
            $text = '-' . (int) $this->discount_value . '%';
            if ($this->max_discount_amount > 0) {
                $text .= ' (Tối đa ' . number_format($this->max_discount_amount, 0, ',', '.') . 'đ)';
            }
            return $text;
        }

        return '-' . number_format($this->discount_value, 0, ',', '.') . 'đ';
    }

    /**
     * Lấy thông tin trạng thái để hiển thị badge
     */
    public function getStatusInfoAttribute(): array
    {
        if (!$this->is_active) {
            return ['key' => 'inactive', 'label' => 'Đã tắt', 'badge' => 'bg-secondary'];
        }

        if ($this->hasExpired()) {
            return ['key' => 'expired', 'label' => 'Hết hạn', 'badge' => 'bg-danger'];
        }

        if ($this->isExhausted()) {
            return ['key' => 'exhausted', 'label' => 'Hết lượt', 'badge' => 'bg-warning text-dark'];
        }

        if (!$this->hasStarted()) {
            return ['key' => 'upcoming', 'label' => 'Sắp diễn ra', 'badge' => 'bg-info text-dark'];
        }

        return ['key' => 'active', 'label' => 'Đang diễn ra', 'badge' => 'bg-success'];
    }
}
