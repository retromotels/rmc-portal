<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The member Community: a directory + forum. A property must join (create a
 * community profile) before it can see or take part — non-members see only the
 * join screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();  // the property (User) row
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('display_name');
            $table->string('town')->nullable();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });

        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_member_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40)->default('general')->index();
            $table->string('title');
            $table->text('body');
            $table->boolean('pinned')->default(false);
            $table->boolean('locked')->default(false);
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
            $table->index(['pinned', 'last_reply_at']);
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_member_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('community_members');
    }
};
