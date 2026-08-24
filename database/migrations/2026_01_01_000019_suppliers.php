<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supplier directory: head office curates supplier offers/deals; members browse,
 * filter, save favourites and either grab a discount code, follow an offer link,
 * or send a request that emails head office to action on their behalf.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('summary')->nullable();          // one-liner
            $table->text('description')->nullable();
            $table->string('offer_type', 12)->default('link'); // code | link | request
            $table->string('offer_headline')->nullable();    // e.g. "15% off first year"
            $table->string('discount_code')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_label')->nullable();
            $table->text('terms')->nullable();
            $table->string('contact_email')->nullable();     // where requests route (else admin_emails)
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('supplier_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->unique(['supplier_id', 'user_id']);
        });

        Schema::create('supplier_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->string('property_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 12)->default('new');
            $table->timestamps();
        });

        DB::table('suppliers')->insert([
            'name'          => 'BrightSpark Energy',
            'slug'          => 'brightspark-energy',
            'category'      => 'energy',
            'summary'       => 'Group electricity rates negotiated for collective members.',
            'description'   => 'BrightSpark offers Retro Motel Collective members preferential electricity rates through our group buying agreement. Request a comparison and our team will handle the switch on your behalf.',
            'offer_type'    => 'request',
            'offer_headline' => 'Members-only group rate',
            'contact_email' => null,
            'website'       => 'https://example.com',
            'is_active'     => true,
            'sort'          => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_requests');
        Schema::dropIfExists('supplier_saves');
        Schema::dropIfExists('suppliers');
    }
};
