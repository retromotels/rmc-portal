<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug', 120);              // unique per site
            $table->string('source_url')->nullable(); // the internal page it mirrors
            $table->unsignedInteger('nav_order')->default(0);
            $table->longText('body')->nullable();     // scraped text content
            $table->json('images')->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
    }
};
