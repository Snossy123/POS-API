<?php

namespace Tests\Feature;

use App\Services\LicenseService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LicenseMiddlewareTest extends TestCase
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

    public function test_api_returns_forbidden_without_valid_license(): void
    {
        File::put($this->machineIdPath, 'blocked-machine');

        $response = $this->postJson('/api/auth/admin/login', [
            'email' => 'admin@pos.local',
            'password' => 'password',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('error', 'license_invalid');
    }

    public function test_api_allows_requests_with_valid_license(): void
    {
        File::put($this->machineIdPath, 'allowed-machine');

        $service = app(LicenseService::class);
        $licenseKey = $service->signPayload([
            'client' => 'AlSaidi Drink',
            'fingerprint' => $service->fingerprint(),
            'issued_at' => now()->toIso8601String(),
        ]);

        putenv('LICENSE_KEY='.$licenseKey);
        $_ENV['LICENSE_KEY'] = $licenseKey;

        $this->actingAsAdmin();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_health_endpoint_does_not_require_license(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
