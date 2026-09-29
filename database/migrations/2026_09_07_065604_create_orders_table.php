<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Thông tin người nhận
            $table->string('name');
            $table->string('phone', 20);
            $table->string('address');

            // Tiền
            $table->decimal('total_price', 15, 2); // tổng tiền hàng + ship

            // Trạng thái đơn
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed...

            // Trạng thái vận chuyển GHN
            $table->string('shipping_status')->default('pending');
            // pending, ready_to_pick, picking, delivering, delivered, cancelled...

            // GHN
            $table->string('ghn_order_code')->nullable()->comment('Mã vận đơn GHN');
            $table->integer('ghn_total_fee')->default(0)->comment('Phí ship GHN (VNĐ)');
            $table->integer('to_district_id')->nullable()->comment('ID quận/huyện nhận (GHN)');
            $table->string('to_ward_code')->nullable()->comment('Mã phường/xã nhận (GHN)');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
