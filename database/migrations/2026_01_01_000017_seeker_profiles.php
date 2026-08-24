<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Richer job-seeker profiles: an avatar, a short bio / headline, home town, and
 * a library of resumes (so a seeker can keep old ones and pick which to send).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('state');
            $table->string('headline')->nullable()->after('avatar_path');
            $table->text('bio')->nullable()->after('headline');
            $table->string('town')->nullable()->after('bio');
        });

        Schema::create('job_seeker_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->unsignedInteger('size')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_resumes');
        Schema::table('job_seekers', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'headline', 'bio', 'town']);
        });
    }
};
