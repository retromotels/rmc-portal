<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Vetting Desk: a property checks an Instagram creator's fit against its own
 * drive market and guest type. Properties store their vetting profile (own IG
 * handle, drive market, guest type) once; each check is saved for history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ig_handle')->nullable()->after('bio');
            $table->text('drive_market')->nullable()->after('ig_handle');
            $table->text('guest_type')->nullable()->after('drive_market');
        });

        Schema::create('vet_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');           // acting user
            $table->unsignedBigInteger('property_id')->nullable();
            $table->string('property_name')->nullable();
            $table->string('handle');
            $table->unsignedBigInteger('followers')->nullable();
            $table->unsignedBigInteger('following')->nullable();
            $table->unsignedBigInteger('posts')->nullable();
            $table->unsignedInteger('avg_likes')->nullable();
            $table->unsignedInteger('avg_comments')->nullable();
            $table->decimal('posts_per_week', 5, 1)->nullable();
            $table->string('based_location')->nullable();
            $table->string('account_type')->nullable();
            $table->decimal('engagement_rate', 5, 2)->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('verdict_tag')->nullable();
            $table->string('verdict_heading')->nullable();
            $table->text('verdict_body')->nullable();
            $table->json('dimensions')->nullable();
            $table->text('suggested_reply')->nullable();
            $table->json('raw_input')->nullable();
            $table->string('provider', 20)->default('assisted');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_checks');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ig_handle', 'drive_market', 'guest_type']);
        });
    }
};
