<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SOP / template documents members can open, personalise (placeholders prefill
 * with their property details), edit and download. Every open and download is
 * logged so head office can see which documents are used most.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('description')->nullable();
            $table->longText('body');            // HTML with {{placeholders}}
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('document_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->string('property_name')->nullable();
            $table->string('action', 12);        // view | download
            $table->timestamp('created_at')->nullable();
            $table->index(['document_id', 'action']);
        });

        DB::table('documents')->insert([
            'title'       => 'Guest Check-In Procedure',
            'slug'        => 'guest-check-in-procedure',
            'category'    => 'Front Office',
            'description' => 'A ready-to-use standard operating procedure for checking guests in. Personalised with your property details — edit and make it yours.',
            'is_published' => true,
            'sort'        => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
            'body'        => '<h1>Guest Check-In Procedure</h1>'
                . '<p><strong>Property:</strong> {{property_name}}<br><strong>Location:</strong> {{location}}<br><strong>Prepared:</strong> {{today}}</p>'
                . '<h2>Purpose</h2><p>This procedure sets out how the team at {{property_name}} welcomes and checks in every guest, so arrivals are consistent, warm and efficient.</p>'
                . '<h2>Before arrival</h2><ol><li>Review the day\'s arrivals and note any special requests.</li><li>Confirm rooms are cleaned, inspected and ready.</li><li>Prepare keys and registration cards.</li></ol>'
                . '<h2>On arrival</h2><ol><li>Greet the guest by name and welcome them to {{property_name}}.</li><li>Confirm the booking details and photo ID.</li><li>Explain check-out time, breakfast, parking and Wi-Fi.</li><li>Take payment or pre-authorisation as per policy.</li><li>Hand over keys and offer directions to the room.</li></ol>'
                . '<h2>After check-in</h2><ol><li>Update the PMS to reflect the guest in-house.</li><li>Log any outstanding requests for the relevant team.</li></ol>'
                . '<h2>Contact</h2><p>Questions about this procedure: {{manager_name}} — {{email}}{{phone_line}}.</p>'
                . '<p style="color:#888;font-size:12px">Retro Motel Collective · Template SOP · Personalise before use.</p>',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_events');
        Schema::dropIfExists('documents');
    }
};
