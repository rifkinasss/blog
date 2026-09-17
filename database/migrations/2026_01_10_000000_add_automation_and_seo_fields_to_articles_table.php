<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->foreignId('origin_service_account_id')->nullable()->after('user_id')->constrained('service_accounts')->nullOnDelete();
            $table->string('review_status')->default('none')->index()->after('review_requested_at');
            $table->foreignId('reviewed_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('canonical_url', 255)->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('canonical_url');
            $table->boolean('robots_index')->default(true)->after('og_image');
        });

        DB::table('articles')->whereNotNull('review_requested_at')->update(['review_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropForeign(['origin_service_account_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['review_status']);
            $table->dropColumn(['origin_service_account_id', 'review_status', 'reviewed_by', 'reviewed_at', 'canonical_url', 'og_image', 'robots_index']);
        });
    }
};
