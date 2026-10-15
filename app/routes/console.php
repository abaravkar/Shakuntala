<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shakuntala:admin {email}', function () {
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Enter a valid email address.');

        return 1;
    }
    if (User::where('email', $email)->exists()) {
        $this->error('This email already exists.');

        return 1;
    }
    $password = $this->secret('Admin password (at least 12 characters)');
    if (strlen($password ?? '') < 12) {
        $this->error('Use at least 12 characters.');

        return 1;
    }
    $user = User::create(['name' => 'Shakuntala Admin', 'email' => $email, 'password' => $password]);
    $user->forceFill(['is_admin' => true])->save();
    $this->info('Admin account created. Sign in at /admin/login.');
})->purpose('Create a protected administrator without a default password');

Artisan::command('instagram:sync', function (\App\Services\InstagramFeed $feed) {
    if (!$feed->sync()) { $this->error('Instagram sync failed. Check the protected admin connection page.'); return 1; }
    $this->info('Instagram feed synced.');
})->purpose('Sync the official brand Instagram feed');
\Illuminate\Support\Facades\Schedule::command('instagram:sync')->hourly()->withoutOverlapping();
