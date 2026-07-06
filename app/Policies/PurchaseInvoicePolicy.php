<?php

namespace App\Policies;

use App\Support\AuthUser;
use Illuminate\Contracts\Auth\Authenticatable;

class PurchaseInvoicePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }

    public function create(Authenticatable $user): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }
}
