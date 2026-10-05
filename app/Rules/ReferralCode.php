<?php
namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ReferralCode implements Rule
{
    public function __construct(private int $userId) {}
    public function passes($attribute, $value): bool
    {
        return User::where('referral_code', $value)->where('id', '!=', $this->userId)->exists();
    }
    public function message(): string { return 'Mã giới thiệu không tồn tại hoặc là mã của chính bạn.'; }
}
