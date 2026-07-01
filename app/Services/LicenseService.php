<?php

namespace App\Services;

use RuntimeException;

class LicenseService
{
    public function getMachineId(): ?string
    {
        $path = config('license.machine_id_path');

        if (! is_readable($path)) {
            return null;
        }

        $machineId = trim((string) file_get_contents($path));

        return $machineId !== '' ? $machineId : null;
    }

    public function fingerprint(): ?string
    {
        $machineId = $this->getMachineId();

        if ($machineId === null) {
            return null;
        }

        return hash('sha256', $machineId);
    }

    /**
     * @return array{client: string, fingerprint: string, issued_at: string}
     */
    public function decodeLicenseKey(string $licenseKey): array
    {
        $parts = explode('.', $licenseKey, 2);

        if (count($parts) !== 2) {
            throw new RuntimeException('Invalid license key format.');
        }

        [$payloadB64, $signatureB64] = $parts;

        $payloadJson = $this->base64UrlDecode($payloadB64);
        $signature = $this->base64UrlDecode($signatureB64);

        if ($payloadJson === false || $signature === false) {
            throw new RuntimeException('Invalid license key encoding.');
        }

        $publicKey = config('license.public_key');

        if (! is_string($publicKey) || trim($publicKey) === '') {
            throw new RuntimeException('License public key is not configured.');
        }

        $verified = openssl_verify(
            $payloadJson,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            throw new RuntimeException('License signature is invalid.');
        }

        $payload = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            throw new RuntimeException('Invalid license payload.');
        }

        foreach (['client', 'fingerprint', 'issued_at'] as $field) {
            if (! isset($payload[$field]) || ! is_string($payload[$field]) || $payload[$field] === '') {
                throw new RuntimeException("License payload is missing {$field}.");
            }
        }

        return $payload;
    }

    public function signPayload(array $payload): string
    {
        $privateKey = env('LICENSE_PRIVATE_KEY');

        if (! is_string($privateKey) || trim($privateKey) === '') {
            throw new RuntimeException('LICENSE_PRIVATE_KEY is not configured on this machine.');
        }

        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);

        $signature = '';

        if (! openssl_sign($payloadJson, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Failed to sign license payload.');
        }

        return $this->base64UrlEncode($payloadJson).'.'.$this->base64UrlEncode($signature);
    }

    public function isValid(?string $licenseKey = null): bool
    {
        if (! config('license.enforce')) {
            return true;
        }

        $licenseKey ??= env('LICENSE_KEY');

        if (! is_string($licenseKey) || trim($licenseKey) === '') {
            return false;
        }

        try {
            $payload = $this->decodeLicenseKey($licenseKey);
            $currentFingerprint = $this->fingerprint();

            if ($currentFingerprint === null) {
                return false;
            }

            return hash_equals($payload['fingerprint'], $currentFingerprint);
        } catch (RuntimeException) {
            return false;
        }
    }

    public function validationMessage(?string $licenseKey = null): string
    {
        if (! config('license.enforce')) {
            return 'License enforcement is disabled.';
        }

        $licenseKey ??= env('LICENSE_KEY');

        if (! is_string($licenseKey) || trim($licenseKey) === '') {
            return 'License key is missing.';
        }

        if ($this->getMachineId() === null) {
            return 'Machine ID file was not found. Run activate.ps1 on the client machine.';
        }

        try {
            $payload = $this->decodeLicenseKey($licenseKey);
            $currentFingerprint = $this->fingerprint();

            if ($currentFingerprint === null) {
                return 'Unable to read machine fingerprint.';
            }

            if (! hash_equals($payload['fingerprint'], $currentFingerprint)) {
                return 'License is not valid for this machine.';
            }

            return "License is valid for {$payload['client']}.";
        } catch (RuntimeException $exception) {
            return $exception->getMessage();
        }
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string|false
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($value, '-_', '+/'), true);
    }
}
