<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\SalesInvoice;
use App\Models\Shift;
use App\Policies\EmployeePolicy;
use App\Policies\SalesInvoicePolicy;
use App\Policies\ShiftPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        SalesInvoice::class => SalesInvoicePolicy::class,
        Shift::class => ShiftPolicy::class,
        Employee::class => EmployeePolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
