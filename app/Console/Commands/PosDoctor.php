<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PosDoctor extends Command
{
    protected $signature = 'pos:doctor';

    protected $description = 'Diagnose common production deployment issues';

    public function handle(LicenseService $licenseService): int
    {
        $ok = true;

        $this->info('POS deployment diagnostics');
        $this->newLine();

        $appKey = config('app.key');
        if (! is_string($appKey) || trim($appKey) === '') {
            $this->error('APP_KEY is missing');
            $ok = false;
        } else {
            $this->line('APP_KEY: ok');
        }

        try {
            DB::connection()->getPdo();
            $this->line('Database connection: ok');
        } catch (\Throwable $exception) {
            $this->error('Database connection failed: '.$exception->getMessage());
            $ok = false;
        }

        foreach (['users', 'personal_access_tokens', 'sessions', 'cache'] as $table) {
            if (Schema::hasTable($table)) {
                $this->line("Table {$table}: ok");
            } else {
                $this->error("Table {$table}: missing");
                $ok = false;
            }
        }

        $users = User::query()->count();
        $this->line("Admin users: {$users}");

        if ($users === 0) {
            $this->error('No users found. Run: php artisan db:seed --force');
            $ok = false;
        }

        $this->newLine();
        $this->line($licenseService->validationMessage());

        if (! $licenseService->isValid()) {
            $ok = false;
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
