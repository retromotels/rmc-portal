<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Public job-board applicants (jobs.retromotels.com)
        'seeker' => [
            'driver' => 'session',
            'provider' => 'seekers',
        ],

        // External (non-member) employers posting paid jobs
        'employer' => [
            'driver' => 'session',
            'provider' => 'employers',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        'seekers' => [
            'driver' => 'eloquent',
            'model' => App\Models\JobSeeker::class,
        ],

        'employers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employer::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'seekers' => [
            'provider' => 'seekers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
