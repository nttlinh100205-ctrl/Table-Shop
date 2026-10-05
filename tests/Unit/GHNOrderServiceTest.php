<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use Mockery;
use Tests\TestCase;

class GHNOrderServiceTest extends TestCase
{
    public function test_cod_above_limit_is_rejected_without_calling_ghn(): void
    {
        config(['services.ghn.max_cod_amount' => 50000000]);
        $ghn = Mockery::mock(GHNService::class);
        $ghn->shouldNotReceive('createOrder');
        $result = (new GHNOrderService($ghn))->create(new Order(['total_price' => 81333000]));
        $this->assertSame(422, $result['code']);
        $this->assertStringContainsString('50.000.000', $result['message']);
    }

    public function test_paid_high_value_order_sends_zero_cod_and_boundary_cod_is_not_reduced(): void
    {
        config(['services.ghn.max_cod_amount' => 50000000]);
        $ghn = Mockery::mock(GHNService::class);
        $ghn->shouldReceive('packageParameters')->twice()->andReturn([
            'weight' => 1000, 'length' => 10, 'width' => 10, 'height' => 10, 'service_type_id' => 2,
        ]);
        $ghn->shouldReceive('createOrder')->once()->with(Mockery::on(fn ($p) => $p['cod_amount'] === 0))
            ->andReturn(['code' => 200, 'data' => ['order_code' => 'PAID-TEST']]);
        $ghn->shouldReceive('createOrder')->once()->with(Mockery::on(fn ($p) => $p['cod_amount'] === 50000000))
            ->andReturn(['code' => 200, 'data' => ['order_code' => 'COD-TEST']]);
        $order = new Order(['total_price' => 81333000]);
        $order->setRelation('items', collect());
        $service = new GHNOrderService($ghn);
        $this->assertSame('PAID-TEST', $service->create($order, true)['data']['order_code']);
        $order->total_price = 50000000;
        $this->assertSame('COD-TEST', $service->create($order, false)['data']['order_code']);
    }

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
