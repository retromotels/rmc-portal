<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend the activity log (now "User Log") to also record triggered emails:
 * a `kind` to distinguish page views from emails, a `detail` line (recipient /
 * subject), and a nullable user_id (system emails have no acting user).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('kind', 20)->default('page')->after('id');
            $table->string('detail')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['kind', 'detail']);
        });
    }
};
