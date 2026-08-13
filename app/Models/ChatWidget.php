<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatWidget extends Model
{
    protected $fillable = ['user_id', 'token', 'enabled', 'config'];

    protected function casts(): array
    {
        return ['config' => 'array', 'enabled' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Get (or create with sensible defaults) the widget for a property. */
    public static function forProperty(User $u): self
    {
        return static::firstOrCreate(
            ['user_id' => $u->id],
            ['token' => static::freshToken(), 'enabled' => true, 'config' => static::defaultConfig($u)]
        );
    }

    public static function freshToken(): string
    {
        do {
            $t = Str::lower(Str::random(16));
        } while (static::where('token', $t)->exists());

        return $t;
    }

    /** Starter config with placeholder answers the property fills in. */
    public static function defaultConfig(User $u): array
    {
        $motel = $u->motel ?: 'our motel';

        return [
            'title'    => $u->motel ?: 'Guest concierge',
            'subtitle' => 'Guest concierge · ask me anything',
            'welcome'  => "Hi, welcome to {$motel}! Tap a button below or ask me about the wifi, breakfast, the pool or things to do nearby.",
            'primary'  => '#1E7F86',
            'accent'   => '#E8553D',
            'entries'  => [
                ['label' => '📶 WiFi',        'keys' => 'wifi, wi-fi, internet, password, network',                       'answer' => "📶 **WiFi**\nNetwork: [your network name]\nPassword: [your password]\n\nFree for all guests."],
                ['label' => '🛎️ Reception',   'keys' => 'reception, front desk, office, help, contact, phone',             'answer' => "🛎️ **Reception**\nOpen [hours].\nCall us on [phone number]."],
                ['label' => '🕑 Check-in',    'keys' => 'check in, check-in, checkin, arrive, arrival, early',             'answer' => "🕑 **Check-in**\nRooms are ready from [time]. Arriving early? Leave your bags at reception."],
                ['label' => '🕙 Check-out',   'keys' => 'check out, check-out, checkout, leave, leaving, late',            'answer' => "🕙 **Check-out**\nCheck-out is by [time]. Ask reception about a late check-out."],
                ['label' => '🏊 Pool',        'keys' => 'pool, swim, swimming, spa',                                      'answer' => "🏊 **Pool**\nOpen [hours]. Towels are at reception."],
                ['label' => '☕ Breakfast',   'keys' => 'breakfast, cafe, coffee, food, eat, dining',                     'answer' => "☕ **Breakfast**\nServed at [where] from [times]."],
                ['label' => '🧺 Laundry',     'keys' => 'laundry, washing, washer, dryer',                                'answer' => "🧺 **Laundry**\nGuest laundry is [location]."],
                ['label' => '🚗 Parking',     'keys' => 'parking, park, car',                                             'answer' => "🚗 **Parking**\n[Free on-site parking / details]."],
                ['label' => '🗺️ Things to do', 'keys' => 'things to do, to do, around, attractions, nearby, local, tips',   'answer' => "🗺️ **Out & about**\n[Add a few local tips — beach, cafés, walks, markets]."],
                ['label' => '📋 House rules', 'keys' => 'smoking, smoke, vape, pet, pets, dog, noise, quiet, rules',       'answer' => "📋 **House rules**\n[Smoking, pets, quiet hours…]"],
            ],
        ];
    }
}
