<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('reviews', function (Blueprint $t) {
            $t->text('admin_reply')->nullable();
            $t->timestamp('replied_at')->nullable();
            $t->string('resolution_status', 20)->default('pending')->index();
        });
        Schema::table('users', fn(Blueprint $t) => $t->boolean('email_verification_exempt')->default(false));
        // One-time exemption for existing accounts; newly registered accounts default to false.
        \Illuminate\Support\Facades\DB::table('users')->update(['email_verification_exempt'=>true]);
    }
    public function down(): void {
        Schema::table('reviews', function (Blueprint $t) {
            $t->dropIndex(['resolution_status']);
            $t->dropColumn(['admin_reply','replied_at','resolution_status']);
        });
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn('email_verification_exempt'));
    }
};
