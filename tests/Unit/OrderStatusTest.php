<?php

namespace Tests\Unit;

use App\Support\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_bulk_order_options_use_canonical_order_labels(): void
    {
        foreach (OrderStatus::bulkOrderOptions() as $status => $label) {
            $this->assertSame(OrderStatus::ORDER[$status], $label);
        }
    }

    public function test_bulk_order_options_exclude_cancellation_workflows(): void
    {
        $options = OrderStatus::bulkOrderOptions();

        $this->assertArrayNotHasKey('cancelled', $options);
        $this->assertArrayNotHasKey('cancel_requested', $options);
    }
}