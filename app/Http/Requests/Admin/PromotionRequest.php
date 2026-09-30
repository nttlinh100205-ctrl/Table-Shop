<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }

        // Chuyển đổi checkbox is_active
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $promotionId = $this->route('promotion')?->id ?? null;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_\-]+$/',
                Rule::unique('promotions', 'code')->ignore($promotionId),
            ],
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string|max:1000',
            'discount_type'       => 'required|in:percent,fixed',
            'discount_value'      => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    if ($this->discount_type === 'percent' && $value > 100) {
                        $fail('Giảm theo phần trăm không được vượt quá 100%.');
                    }
                },
            ],
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'usage_limit'         => 'nullable|integer|min:1',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'is_active'           => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required'               => 'Vui lòng nhập mã khuyến mãi.',
            'code.unique'                 => 'Mã khuyến mãi này đã tồn tại trong hệ thống.',
            'code.regex'                  => 'Mã khuyến mãi chỉ được chứa chữ in hoa, số, dấu gạch nối (-) hoặc gạch dưới (_).',
            'code.max'                    => 'Mã khuyến mãi không được quá 50 ký tự.',
            'name.required'               => 'Vui lòng nhập tên chương trình khuyến mãi.',
            'discount_type.required'      => 'Vui lòng chọn hình thức giảm giá.',
            'discount_type.in'            => 'Hình thức giảm giá không hợp lệ.',
            'discount_value.required'     => 'Vui lòng nhập giá trị giảm.',
            'discount_value.numeric'      => 'Giá trị giảm phải là chữ số.',
            'discount_value.min'          => 'Giá trị giảm phải lớn hơn 0.',
            'max_discount_amount.numeric' => 'Số tiền giảm tối đa phải là số.',
            'max_discount_amount.min'     => 'Số tiền giảm tối đa không được nhỏ hơn 0.',
            'min_order_amount.numeric'    => 'Giá trị đơn hàng tối thiểu phải là số.',
            'min_order_amount.min'        => 'Giá trị đơn hàng tối thiểu không được nhỏ hơn 0.',
            'usage_limit.integer'         => 'Số lượt dùng phải là số nguyên.',
            'usage_limit.min'             => 'Số lượt dùng tối thiểu là 1.',
            'start_date.date'             => 'Ngày bắt đầu không đúng định dạng ngày giờ.',
            'end_date.date'               => 'Hạn sử dụng không đúng định dạng ngày giờ.',
            'end_date.after_or_equal'     => 'Hạn sử dụng (ngày kết thúc) phải sau hoặc bằng ngày bắt đầu.',
        ];
    }
}
