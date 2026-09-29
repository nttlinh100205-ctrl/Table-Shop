@php
    $shippingStages = [
        'Tiếp nhận',
        'Chờ lấy hàng',
        'Đang giao',
        'Đã giao',
    ];
    $shippingStage = match ($shippingStatus) {
        'ready_to_pick', 'picking', 'picked' => 2,
        'storing', 'transporting', 'sorting', 'delivering' => 3,
        'delivered' => 4,
        'pending', 'not_shipped', 'processing' => 1,
        default => null,
    };
@endphp

@once
<style>
    .shipping-progress {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin: 0.25rem 0 0;
        padding: 0;
        list-style: none;
    }
    .shipping-progress-step {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        color: #94a3b8;
        text-align: center;
        font-size: 0.68rem;
        font-weight: 600;
    }
    .shipping-progress-step:not(:last-child)::after {
        position: absolute;
        top: 0.45rem;
        left: calc(50% + 0.65rem);
        width: calc(100% - 1.3rem);
        height: 2px;
        background: #e2e8f0;
        content: '';
    }
    .shipping-progress-step.is-complete:not(:last-child)::after { background: #22c55e; }
    .shipping-progress-dot {
        z-index: 1;
        display: grid;
        width: 1rem;
        height: 1rem;
        place-items: center;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        background: #fff;
        color: #fff;
        font-size: 0.55rem;
    }
    .shipping-progress-step.is-complete,
    .shipping-progress-step.is-current { color: #166534; }
    .shipping-progress-step.is-complete .shipping-progress-dot { border-color: #22c55e; background: #22c55e; }
    .shipping-progress-step.is-current .shipping-progress-dot { border-color: #16a34a; box-shadow: 0 0 0 3px #dcfce7; }
    .shipping-progress-exception {
        margin-top: 0.75rem;
        padding: 0.5rem 0.65rem;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
    }
    @media (max-width: 575.98px) {
        .shipping-progress-step { font-size: 0.62rem; }
        .shipping-progress { margin-top: 0.5rem; }
    }
</style>
@endonce

@if ($shippingStage)
    <ol class="shipping-progress" aria-label="Tiến trình vận chuyển">
        @foreach ($shippingStages as $index => $label)
            @php $step = $index + 1; @endphp
            <li class="shipping-progress-step {{ $step < $shippingStage ? 'is-complete' : '' }} {{ $step === $shippingStage ? 'is-current' : '' }}"
                @if ($step === $shippingStage) aria-current="step" @endif>
                <span class="shipping-progress-dot">
                    @if ($step < $shippingStage)<i class="bi bi-check"></i>@endif
                </span>
                <span>{{ $label }}</span>
            </li>
        @endforeach
    </ol>
@else
    <div class="shipping-progress-exception">
        <i class="bi bi-info-circle me-1"></i>
        {{ \App\Support\OrderStatus::shipLabel($shippingStatus) }}
    </div>
@endif