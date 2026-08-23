<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Job board: property-submitted listings (approved by admin, shown publicly on
 * jobs.retromotels.com), public job-seeker accounts, and applications.
 * Named job_listings to avoid the framework queue "jobs" table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the property
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->string('employment_type', 30)->default('full-time');    // full-time | part-time | casual | contract
            $table->string('department', 40)->nullable();                   // housekeeping | front-office | food-beverage | management | maintenance | other
            $table->string('location')->nullable();                         // snapshot of the property's location
            $table->string('pay')->nullable();
            $table->text('description');
            $table->string('status', 20)->default('pending');               // draft | pending | approved | rejected | closed
            $table->string('reject_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_seeker_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('status', 20)->default('new');                   // new | reviewed | shortlisted | archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_seekers');
        Schema::dropIfExists('job_listings');
    }
};
