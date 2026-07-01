<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseFingerprint extends Command
{
    protected $signature = 'license:fingerprint';

    protected $description = 'Display the machine fingerprint for license activation';

    public function handle(LicenseService $licenseService): int
    {
        $machineId = $licenseService->getMachineId();

        if ($machineId === null) {
            $this->error('Machine ID file not found at: '.config('license.machine_id_path'));
            $this->line('Run activate.ps1 on the client machine first.');

            return self::FAILURE;
        }

        $fingerprint = $licenseService->fingerprint();

        $this->info('Machine ID: '.$machineId);
        $this->info('Fingerprint: '.$fingerprint);

        return self::SUCCESS;
    }
}
