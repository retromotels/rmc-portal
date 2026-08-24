<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the module-toggle + content keys into the existing key/value `settings`
 * table (created earlier). Idempotent: only inserts keys that aren't present, so
 * it's safe to re-run and never touches other settings.
 */
return new class extends Migration
{
    private array $defaults = [
        'module_ai_assist'  => '1',
        'module_roundtable' => '0',
        'module_community'  => '0',
        'roundtable_title'  => 'The Monthly Roundtable',
        'roundtable_body'   => "Once a month we get the collective together on a call — a chance to swap what's working, hear from a guest speaker, and put your questions to head office.\n\nDetails for the next session appear here. Add the link below and members can join in a click.",
        'roundtable_link'   => '',
        'community_title'   => 'The Community',
        'community_body'    => "You're not running your motel alone. The collective community is where members share advice, wins, suppliers and the odd war story.\n\nJoin the group below to get involved.",
        'community_link'    => '',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        foreach ($this->defaults as $key => $value) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Only remove the keys we added — never drop the shared table.
        DB::table('settings')->whereIn('key', array_keys($this->defaults))->delete();
    }
};
