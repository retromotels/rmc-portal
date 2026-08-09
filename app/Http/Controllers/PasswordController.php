<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Outbox;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $r)
    {
        $data = $r->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('password_resets')->where('email', $user->email)->delete();
            DB::table('password_resets')->insert([
                'email'      => $user->email,
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]);
            $url = route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email);
            Outbox::passwordReset($user, $url);
        }

        // Always the same message — don't reveal whether the email exists.
        return back()->with('status', 'If that email is registered, a reset link is on its way.');
    }

    public function showReset(Request $r, string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => (string) $r->query('email', '')]);
    }

    public function reset(Request $r)
    {
        $data = $r->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $row = DB::table('password_resets')->where('email', $data['email'])->first();
        if (!$row || !Hash::check($data['token'], $row->token)) {
            return back()->withErrors(['email' => 'This reset link is invalid. Please request a new one.'])->onlyInput('email');
        }
        if (Carbon::parse($row->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['email' => 'This reset link has expired. Please request a new one.'])->onlyInput('email');
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->password = $data['password'];   // hashed by the model cast
        $user->save();

        DB::table('password_resets')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset — you can now log in.');
    }
}
