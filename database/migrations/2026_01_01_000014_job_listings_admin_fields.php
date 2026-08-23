<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-posted / aggregated job listings: an employer name (for jobs not tied
 * to a member property) plus a source + external reference (e.g. SEEK id).
 * Admin jobs are attached to the admin user row and carry the employer name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('employer')->nullable()->after('user_id');
            $table->string('source', 20)->nullable()->after('employer');       // e.g. 'seek'
            $table->string('source_ref')->nullable()->after('source')->index(); // external job id
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn(['employer', 'source', 'source_ref']);
        });
    }
};
