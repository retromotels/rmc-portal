<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 20)->default('booking');
            $table->string('url', 1000);
            $table->string('property_name')->nullable();
            $table->json('pulled')->nullable();   // best-effort scraped data
            $table->json('checks')->nullable();   // { item_key: {status, note} }
            $table->unsignedTinyInteger('score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_audits');
    }
};
