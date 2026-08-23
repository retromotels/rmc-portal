<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a normalised state code (NSW, VIC, QLD…) and a normalised annual-equivalent
 * salary so the public board can offer state and pay filters. salary_annual is the
 * job's upper pay figure converted to a yearly figure (hourly ×38×52, weekly ×52,
 * monthly ×12) so hourly and salaried roles can be compared on one scale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('state', 8)->nullable()->after('location')->index();
            $table->unsignedInteger('salary_annual')->nullable()->after('pay')->index();
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn(['state', 'salary_annual']);
        });
    }
};
