<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseGenerate extends Command
{
    protected $signature = 'license:generate
                            {--client= : Licensed client name}
                            {--fingerprint= : Machine fingerprint from license:fingerprint}';

    protected $description = 'Generate a signed license key for a client machine';

    public function handle(LicenseService $licenseService): int
    {
        $client = (string) $this->option('client');
        $fingerprint = (string) $this->option('fingerprint');

        if ($client === '' || $fingerprint === '') {
            $this->error('Both --client and --fingerprint are required.');

            return self::FAILURE;
        }

        if (! preg_match('/^[a-f0-9]{64}$/', $fingerprint)) {
            $this->error('Fingerprint must be a 64-character SHA-256 hash.');

            return self::FAILURE;
        }

        try {
            $licenseKey = $licenseService->signPayload([
                'client' => $client,
                'fingerprint' => $fingerprint,
                'issued_at' => now()->toIso8601String(),
            ]);
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('License generated successfully.');
        $this->newLine();
        $this->line('Client: '.$client);
        $this->line('Fingerprint: '.$fingerprint);
        $this->newLine();
        $this->comment('Add this to the client .env file as LICENSE_KEY=');
        $this->line($licenseKey);

        return self::SUCCESS;
    }
}
