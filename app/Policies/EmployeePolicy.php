<?php

namespace App\Policies;

use App\Support\AuthUser;
use Illuminate\Contracts\Auth\Authenticatable;

class EmployeePolicy
{
    public function manage(Authenticatable $user): bool
    {
        return AuthUser::isAdmin($user);
    }
}
