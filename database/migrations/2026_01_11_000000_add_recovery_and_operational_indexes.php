<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->softDeletes();
            $table->index(['status', 'scheduled_at']);
            $table->index(['status', 'published_at']);
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['actor_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex(['actor_type', 'created_at']);
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropIndex(['status', 'scheduled_at']);
            $table->dropIndex(['status', 'published_at']);
            $table->dropSoftDeletes();
        });
    }
};
