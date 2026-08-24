<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * External (non-member) employers: companies outside the collective who buy
 * job credits and post to the board (admin-approved). Purchases track paid
 * packs and Top Shelf enquiries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('name')->nullable();       // contact name
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->unsignedInteger('job_credits')->default(0);
            $table->string('stripe_customer_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('job_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->string('tier', 30);
            $table->unsignedInteger('credits')->default(0);
            $table->unsignedInteger('amount')->default(0);     // in dollars
            $table->string('currency', 8)->default('aud');
            $table->string('status', 20)->default('pending');  // pending|paid|requested|enquiry
            $table->string('stripe_session_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->unsignedBigInteger('employer_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn('employer_id');
        });
        Schema::dropIfExists('job_purchases');
        Schema::dropIfExists('employers');
    }
};
