<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('owner');          // owner | admin
            $table->string('motel')->nullable();
            $table->string('band')->default('small');           // small | mid | large
            $table->string('tier')->default('standard');        // standard | growth | full
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('loc')->nullable();
            $table->boolean('details_complete')->default(false);
            $table->boolean('founding')->default(false);
            $table->timestamp('cancel_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'motel', 'band', 'tier', 'phone', 'bio', 'photo_path', 'loc', 'details_complete', 'founding', 'cancel_requested_at']);
        });
    }
};
