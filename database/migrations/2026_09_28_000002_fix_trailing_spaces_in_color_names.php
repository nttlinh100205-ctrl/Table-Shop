<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CODES = ['VCT-01', 'VCT-03', 'VCT-04'];

    public function up(): void
    {
        DB::table('colors')
            ->whereIn('code', self::CODES)
            ->update(['name' => DB::raw('TRIM(name)')]);
    }

    public function down(): void
    {
        DB::table('colors')
            ->whereIn('code', self::CODES)
            ->update(['name' => DB::raw("CONCAT(TRIM(name), ' ')")]);
    }
};