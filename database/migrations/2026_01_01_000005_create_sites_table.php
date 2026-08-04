<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('theme', 20)->default('seasea');   // seasea|surf|roy|capon
            $table->string('source_url');                     // motel's existing site (scraped)

            // Curated content (auto-pulled, admin-editable)
            $table->string('name')->nullable();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('booking_url')->nullable();        // where the booking button redirects
            $table->string('price_from')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('images')->nullable();               // gallery image URLs
            $table->json('amenities')->nullable();

            // Public page
            $table->string('slug')->nullable()->unique();
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();

            // Private preview
            $table->string('preview_token', 32)->unique();
            $table->string('preview_password', 40);

            $table->timestamps();
        });

        Schema::create('site_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 12)->default('preview');   // preview|public
            $table->boolean('unlocked')->default(false);      // preview: entered correct password
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_views');
        Schema::dropIfExists('sites');
    }
};
