<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every email the system WOULD send is recorded here. Until SendGrid is
        // connected they stay 'queued' so the admin can preview them.
        Schema::create('outbox_emails', function (Blueprint $table) {
            $table->id();
            $table->string('template', 40);          // welcome | admin_new_signup | pending_reminder | password_reset | health_request
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->longText('body');                // rendered HTML
            $table->json('meta')->nullable();
            $table->string('status', 12)->default('queued'); // queued | sent | failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('pending_reminded_at')->nullable()->after('cancel_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('pending_reminded_at'));
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('outbox_emails');
    }
};
