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
            $table->timestamp('scheduled_at')->nullable()->index()->after('status');
        });

        DB::table('articles')
            ->where('status', 'published')
            ->where('published_at', '>', now())
            ->update([
                'status' => 'scheduled',
                'scheduled_at' => DB::raw('published_at'),
                'published_at' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('articles')
            ->where('status', 'scheduled')
            ->update([
                'status' => 'published',
                'published_at' => DB::raw('scheduled_at'),
            ]);

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn('scheduled_at');
        });
    }
};
