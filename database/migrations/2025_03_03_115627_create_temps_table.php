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
        Schema::create('temps', function (Blueprint $table) {
            $table->uuid('uuid')->unique();
            $table->string('password');
            $table->string('email');
            $table->string('referrer')->nullable();
            $table->string('referral_token')->unique()->nullable();
            $table->string('email_otp');
            $table->timestamp('email_otp_expires_at');
            $table->string('mobile_phone')->nullable(); 
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('phone_otp')->nullable();
            $table->timestamp('phone_otp_expires_at')->nullable();
            $table->boolean('policy_agreement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temps');
    }
};
