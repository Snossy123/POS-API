<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LicenseGenerateKeypair extends Command
{
    protected $signature = 'license:generate-keypair';

    protected $description = 'Generate an RSA key pair for license signing (developer only)';

    public function handle(): int
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            $this->error('Failed to generate RSA key pair.');

            return self::FAILURE;
        }

        openssl_pkey_export($resource, $privateKey);
        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['key'] ?? null;

        if (! is_string($privateKey) || ! is_string($publicKey)) {
            $this->error('Failed to export RSA key pair.');

            return self::FAILURE;
        }

        $this->info('RSA key pair generated successfully.');
        $this->newLine();
        $this->comment('Add this to your developer .env as LICENSE_PRIVATE_KEY (keep secret):');
        $this->line($this->formatEnvValue($privateKey));
        $this->newLine();
        $this->comment('Add this to config/license.php via LICENSE_PUBLIC_KEY (safe to ship):');
        $this->line($this->formatEnvValue($publicKey));

        return self::SUCCESS;
    }

    private function formatEnvValue(string $value): string
    {
        return '"'.str_replace("\n", '\n', $value).'"';
    }
}
