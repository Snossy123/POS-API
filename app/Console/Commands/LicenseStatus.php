<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseStatus extends Command
{
    protected $signature = 'license:status';

    protected $description = 'Show license and machine binding status';

    public function handle(LicenseService $licenseService): int
    {
        $this->info('License enforcement: '.(config('license.enforce') ? 'enabled' : 'disabled'));
        $this->line('Machine ID: '.($licenseService->getMachineId() ?? 'missing'));
        $this->line('Fingerprint: '.($licenseService->fingerprint() ?? 'missing'));
        $this->line('Public key: '.($this->publicKeySource()));
        $this->newLine();
        $this->line($licenseService->validationMessage());

        return $licenseService->isValid() ? self::SUCCESS : self::FAILURE;
    }

    private function publicKeySource(): string
    {
        if (is_string(env('LICENSE_PUBLIC_KEY')) && trim(env('LICENSE_PUBLIC_KEY')) !== '') {
            return 'from LICENSE_PUBLIC_KEY env';
        }

        $path = config('license.public_key_path');

        if (is_string($path) && is_readable($path)) {
            return "from {$path}";
        }

        return 'missing';
    }
}
