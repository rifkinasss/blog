<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('content_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('api_tokens', function (Blueprint $table): void {
            $table->foreignId('service_account_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('token_prefix', 16)->nullable()->after('token_hash');
            $table->index('service_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table): void {
            $table->dropForeign(['service_account_id']);
            $table->dropIndex(['service_account_id']);
            $table->dropColumn(['service_account_id', 'token_prefix']);
        });

        Schema::dropIfExists('service_accounts');
    }
};
