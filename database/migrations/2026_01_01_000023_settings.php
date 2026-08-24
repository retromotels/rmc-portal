<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Simple key/value settings store powering admin-toggleable modules
 * (AI Assist, Monthly Roundtable, Community) and their editable content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('settings')->insert([
            ['key' => 'module_ai_assist',  'value' => '1', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'module_roundtable', 'value' => '0', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'module_community',  'value' => '0', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'roundtable_title',  'value' => 'The Monthly Roundtable', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'roundtable_body',   'value' => "Once a month we get the collective together on a call — a chance to swap what's working, hear from a guest speaker, and put your questions to head office.\n\nDetails for the next session appear here. Add the link below and members can join in a click.", 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'roundtable_link',   'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'community_title',   'value' => 'The Community', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'community_body',    'value' => "You're not running your motel alone. The collective community is where members share advice, wins, suppliers and the odd war story.\n\nJoin the group below to get involved.", 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'community_link',     'value' => '', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
