<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-tenancy: an account (an owner user row) can hold several properties.
 * Each property is itself an owner user row linked to the account via
 * account_id (child property rows have no login of their own). The existing
 * single-property owners work unchanged — their own row is their first
 * property (account_id null, current_property_id null → resolves to self).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->index()->after('role');
            $table->unsignedBigInteger('current_property_id')->nullable()->after('account_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_id', 'current_property_id']);
        });
    }
};
