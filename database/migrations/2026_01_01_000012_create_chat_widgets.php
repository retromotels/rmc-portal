<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-property embeddable guest chat widget. One row per property, addressed
 * publicly by a random token. `config` holds the editable title, welcome,
 * colours and FAQ entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 32)->unique();
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_widgets');
    }
};
