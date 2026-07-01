<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseActivate extends Command
{
    protected $signature = 'license:activate {--key= : License key to validate}';

    protected $description = 'Validate a license key against the current machine';

    public function handle(LicenseService $licenseService): int
    {
        $licenseKey = (string) ($this->option('key') ?: env('LICENSE_KEY'));

        if ($licenseKey === '') {
            $this->error('Provide --key= or set LICENSE_KEY in the environment.');

            return self::FAILURE;
        }

        $message = $licenseService->validationMessage($licenseKey);

        if (! $licenseService->isValid($licenseKey)) {
            $this->error($message);

            return self::FAILURE;
        }

        $this->info($message);

        return self::SUCCESS;
    }
}
