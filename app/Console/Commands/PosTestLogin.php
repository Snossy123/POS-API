<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PosTestLogin extends Command
{
    protected $signature = 'pos:test-login {email=admin@pos.local} {password=password}';

    protected $description = 'Test admin login flow without HTTP (password check + token creation)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) $this->argument('password');

        $this->line('APP_KEY: '.(config('app.key') ? 'set' : 'missing'));
        $this->line('personal_access_tokens table: '.(Schema::hasTable('personal_access_tokens') ? 'yes' : 'no'));

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error("User not found: {$email}");

            return self::FAILURE;
        }

        $this->line("User found: {$user->email}");

        if (! Hash::check($password, $user->password)) {
            $this->error('Password check failed');

            return self::FAILURE;
        }

        $this->info('Password check: ok');

        try {
            $token = $user->createToken('doctor-test')->plainTextToken;
            $user->tokens()->where('name', 'doctor-test')->delete();
            $this->info('Token creation: ok');
            $this->line('Token preview: '.substr($token, 0, 20).'...');
        } catch (\Throwable $exception) {
            $this->error('Token creation failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
