<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use Mockery;
use Tests\TestCase;

class GHNOrderServiceTest extends TestCase
{
    public function test_it_does_not_retry_a_disabled_recipient_ward(): void
    {
        $ghn = Mockery::mock(GHNService::class);
        $ghn->shouldReceive('packageParameters')->once()->andReturn([
            'weight' => 1000,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'service_type_id' => 2,
        ]);
        $ghn->shouldReceive('createOrder')->once()->andReturn([
            'code' => 400,
            'data' => ['code_message' => 'RECEIVE_WARD_IS_DISABLED'],
        ]);

        $order = new Order([
            'id' => 1,
            'name' => 'Test customer',
            'phone' => '0912345678',
            'address' => '1 Test Street',
            'total_price' => 100000,
            'to_district_id' => 1,
            'to_ward_code' => 'W001',
        ]);
        $order->setRelation('items', collect());

        $result = (new GHNOrderService($ghn))->create($order);

        $this->assertSame(400, $result['code']);
    }

    public function test_it_retries_a_temporary_warehouse_failure_once(): void
    {
        $ghn = Mockery::mock(GHNService::class);
        $ghn->shouldReceive('packageParameters')->once()->andReturn([
            'weight' => 1000,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'service_type_id' => 2,
        ]);
        $ghn->shouldReceive('createOrder')->twice()->andReturn(
            ['code' => 400, 'data' => ['code_message' => 'WAREHOUSE_NOT_FOUND']],
            ['code' => 200, 'data' => ['order_code' => 'GHN-TEST']]
        );

        $order = new Order([
            'id' => 1,
            'name' => 'Test customer',
            'phone' => '0912345678',
            'address' => '1 Test Street',
            'total_price' => 100000,
            'to_district_id' => 1,
            'to_ward_code' => 'W001',
        ]);
        $order->setRelation('items', collect());

        $result = (new GHNOrderService($ghn))->create($order);

        $this->assertSame('GHN-TEST', $result['data']['order_code']);
    }
}