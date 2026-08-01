<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type')->default('monthly_metrics');
            $table->date('reporting_month')->nullable();

            $table->string('recipient_email');
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();

            $table->string('status')->default('processing');
            $table->text('error_message')->nullable();

            $table->timestamp('attempted_at');
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'reporting_month']);
            $table->index(['status', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
