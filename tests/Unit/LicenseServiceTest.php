<?php

namespace Tests\Unit;

use App\Services\LicenseService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LicenseServiceTest extends TestCase
{
    private string $machineIdPath;

  private string $privateKey;

  private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->machineIdPath = storage_path('app/license/machine-id.txt');
        File::ensureDirectoryExists(dirname($this->machineIdPath));

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $privateKey = '';
        openssl_pkey_export($resource, $privateKey);
        $this->privateKey = $privateKey;
        $details = openssl_pkey_get_details($resource);
        $this->publicKey = $details['key'];

        config([
            'license.enforce' => true,
            'license.public_key' => $this->publicKey,
            'license.machine_id_path' => $this->machineIdPath,
        ]);

        putenv('LICENSE_PRIVATE_KEY='.$this->privateKey);
        putenv('LICENSE_PUBLIC_KEY='.$this->publicKey);
        $_ENV['LICENSE_PRIVATE_KEY'] = $this->privateKey;
        $_ENV['LICENSE_PUBLIC_KEY'] = $this->publicKey;
    }

    protected function tearDown(): void
    {
        if (File::exists($this->machineIdPath)) {
            File::delete($this->machineIdPath);
        }

        parent::tearDown();
    }

    public function test_valid_license_accepts_matching_machine(): void
    {
        File::put($this->machineIdPath, 'client-machine-uuid-001');

        $service = app(LicenseService::class);
        $licenseKey = $service->signPayload([
            'client' => 'AlSaidi Drink',
            'fingerprint' => $service->fingerprint(),
            'issued_at' => now()->toIso8601String(),
        ]);

        putenv('LICENSE_KEY='.$licenseKey);
        $_ENV['LICENSE_KEY'] = $licenseKey;

        $this->assertTrue($service->isValid($licenseKey));
    }

    public function test_license_rejects_different_machine(): void
    {
        File::put($this->machineIdPath, 'client-machine-uuid-001');

        $service = app(LicenseService::class);
        $licenseKey = $service->signPayload([
            'client' => 'AlSaidi Drink',
            'fingerprint' => hash('sha256', 'another-machine-uuid'),
            'issued_at' => now()->toIso8601String(),
        ]);

        $this->assertFalse($service->isValid($licenseKey));
        $this->assertStringContainsString(
            'not valid for this machine',
            $service->validationMessage($licenseKey)
        );
    }

    public function test_license_is_skipped_when_enforcement_disabled(): void
    {
        config(['license.enforce' => false]);

        $service = app(LicenseService::class);

        $this->assertTrue($service->isValid(null));
    }
}
