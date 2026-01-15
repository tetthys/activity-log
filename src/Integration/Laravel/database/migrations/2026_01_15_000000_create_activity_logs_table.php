<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('activity-log.table', 'activity_logs'), function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->dateTime('occurred_at')->index();

            $table->string('action', 191)->index();

            // Definition metadata (useful for policy/retention queries)
            $table->boolean('auditable')->default(true)->index();
            $table->string('sensitivity', 32)->default('normal')->index();
            $table->string('category', 64)->nullable()->index();
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('retention_days')->nullable();

            // Actor/Subject
            $table->string('actor_type', 64)->nullable();
            $table->string('actor_id', 64)->nullable();

            $table->string('subject_type', 64)->nullable();
            $table->string('subject_id', 64)->nullable();

            // Request context
            $table->string('correlation_id', 128)->nullable()->index();
            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('channel', 32)->default('unknown')->index();

            // Arbitrary payload
            $table->json('metadata')->nullable();

            // Convenience timestamps (insert time), distinct from occurred_at
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['actor_type', 'actor_id', 'occurred_at'], 'actlog_actor_time');
            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'actlog_subject_time');
            $table->index(['action', 'occurred_at'], 'actlog_action_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('activity-log.table', 'activity_logs'));
    }
};
