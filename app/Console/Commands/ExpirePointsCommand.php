<?php

namespace App\Console\Commands;

use App\Services\MembershipService;
use Illuminate\Console\Command;

class ExpirePointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quét và xử lý hết hạn điểm thưởng của thành viên sau 1 năm theo từng lô FIFO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Bắt đầu kiểm tra điểm thưởng hết hạn...');

        $expiredPoints = MembershipService::expirePoints();

        if ($expiredPoints > 0) {
            $this->info("Đã xử lý hết hạn tổng cộng " . number_format($expiredPoints, 0, ',', '.') . " điểm thưởng.");
        } else {
            $this->info('Không có lô điểm nào hết hạn hôm nay.');
        }

        return Command::SUCCESS;
    }
}
