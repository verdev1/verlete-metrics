<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('website', 100)->nullable();
            $table->string('analytics_property', 100)->nullable();
            $table->string('store', 20)->default('none');
            $table->string('application_username', 100)->nullable();
            $table->text('application_password')->nullable();
            $table->text('surecart_api_key')->nullable();
            $table->string('emails', 255)->nullable();
            $table->string('recipient_names', 255)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};