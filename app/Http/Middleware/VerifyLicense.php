<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLicense
{
    public function __construct(
        private readonly LicenseService $licenseService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        if ($this->licenseService->isValid()) {
            return $next($request);
        }

        return response()->json([
            'message' => 'هذا النظام مُرخّص لجهاز محدد. للدعم اتصل بالمطوّر.',
            'error' => 'license_invalid',
            'details' => $this->licenseService->validationMessage(),
        ], Response::HTTP_FORBIDDEN);
    }
}
