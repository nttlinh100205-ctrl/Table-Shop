<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bảng giải thưởng (Prizes)
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['points', 'voucher', 'ticket', 'empty'])->default('empty');
            $table->decimal('value', 12, 2)->default(0); // Số điểm, giá trị giảm voucher, hoặc số lượt quay
            $table->unsignedInteger('probability')->default(10); // Trọng số xác suất (Weighted random)
            $table->integer('quantity')->default(-1); // -1: không giới hạn số lượng, >0: số lượng còn lại
            $table->string('color', 20)->default('#C29D62'); // Màu múi bánh xe
            $table->string('text_color', 20)->default('#FFFFFF'); // Màu chữ trên múi
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Bảng lịch sử quay thưởng (Spin histories)
        Schema::create('spin_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prize_id')->nullable()->constrained('prizes')->nullOnDelete();
            $table->string('prize_name', 100);
            $table->string('reward_detail', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });

        // Seed 8 giải thưởng mẫu đa dạng, cân đối tỷ lệ
        DB::table('prizes')->insert([
            [
                'name'        => '10.000 Điểm',
                'type'        => 'points',
                'value'       => 10000,
                'probability' => 25,
                'quantity'    => -1,
                'color'       => '#C29D62',
                'text_color'  => '#3F2F24',
                'icon'        => 'bi-coin',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Voucher 50.000đ',
                'type'        => 'voucher',
                'value'       => 50000,
                'probability' => 15,
                'quantity'    => 200,
                'color'       => '#5A4536',
                'text_color'  => '#FAF6F0',
                'icon'        => 'bi-tag-fill',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => '+1 Lượt quay',
                'type'        => 'ticket',
                'value'       => 1,
                'probability' => 15,
                'quantity'    => -1,
                'color'       => '#FAF6F0',
                'text_color'  => '#3F2F24',
                'icon'        => 'bi-ticket-perforated-fill',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => '20.000 Điểm',
                'type'        => 'points',
                'value'       => 20000,
                'probability' => 15,
                'quantity'    => -1,
                'color'       => '#3F2F24',
                'text_color'  => '#FAF6F0',
                'icon'        => 'bi-coin',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Voucher 100.000đ',
                'type'        => 'voucher',
                'value'       => 100000,
                'probability' => 8,
                'quantity'    => 50,
                'color'       => '#D97706',
                'text_color'  => '#FFFFFF',
                'icon'        => 'bi-stars',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => '50.000 Điểm',
                'type'        => 'points',
                'value'       => 50000,
                'probability' => 5,
                'quantity'    => -1,
                'color'       => '#A87957',
                'text_color'  => '#FFFFFF',
                'icon'        => 'bi-gem',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => '+2 Lượt quay',
                'type'        => 'ticket',
                'value'       => 2,
                'probability' => 7,
                'quantity'    => -1,
                'color'       => '#7E7065',
                'text_color'  => '#FAF6F0',
                'icon'        => 'bi-ticket-detailed-fill',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'May mắn lần sau',
                'type'        => 'empty',
                'value'       => 0,
                'probability' => 10,
                'quantity'    => -1,
                'color'       => '#E6D8C8',
                'text_color'  => '#5A4536',
                'icon'        => 'bi-emoji-smile',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_histories');
        Schema::dropIfExists('prizes');
    }
};
