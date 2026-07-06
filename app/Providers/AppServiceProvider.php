<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Shift;
use App\Policies\CategoryPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseInvoicePolicy;
use App\Policies\SalesInvoicePolicy;
use App\Policies\ShiftPolicy;
use App\Support\AuthUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        SalesInvoice::class => SalesInvoicePolicy::class,
        Shift::class => ShiftPolicy::class,
        Employee::class => EmployeePolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        PurchaseInvoice::class => PurchaseInvoicePolicy::class,
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

        Gate::define('viewReports', fn ($user) => AuthUser::isManagerOrAbove($user));
    }
}
