<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->string('actor_type')->nullable()->index()->after('user_id');
            $table->string('actor_name')->nullable()->after('actor_type');
            $table->text('description')->nullable()->after('subject_id');
        });

        DB::table('audit_logs')->whereNotNull('user_id')->update(['actor_type' => 'human']);
        DB::table('audit_logs')->whereNull('user_id')->update(['actor_type' => 'system', 'actor_name' => 'System']);
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex(['actor_type']);
            $table->dropColumn(['actor_type', 'actor_name', 'description']);
        });
    }
};
