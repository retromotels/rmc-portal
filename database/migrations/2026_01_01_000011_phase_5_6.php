<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);                 // health_request | website_check | new_signup | property_claimed
            $table->string('title');
            $table->text('body')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // subject property
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('health_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // property
            $table->string('type', 30);                 // ota | seo | gmb | reviews | social
            $table->string('status', 20)->default('requested'); // requested | done
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->string('path', 500);
            $table->string('label')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('claim_token', 64)->nullable()->unique()->after('current_property_id');
            $table->timestamp('claimed_at')->nullable()->after('claim_token');
            $table->boolean('created_by_admin')->default(false)->after('claimed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['claim_token', 'claimed_at', 'created_by_admin']));
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('health_requests');
        Schema::dropIfExists('admin_notifications');
    }
};
