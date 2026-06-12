<?php

namespace App\Policies;

use App\Models\SalesInvoice;
use App\Support\AuthUser;
use Illuminate\Contracts\Auth\Authenticatable;

class SalesInvoicePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function void(Authenticatable $user, SalesInvoice $invoice): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }

    public function refund(Authenticatable $user, SalesInvoice $invoice): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }

    public function updatePaymentStatus(Authenticatable $user, SalesInvoice $invoice): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }

    public function reprint(Authenticatable $user, SalesInvoice $invoice): bool
    {
        return true;
    }
}
