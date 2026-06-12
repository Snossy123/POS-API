<?php

namespace App\Policies;

use App\Models\Shift;
use App\Support\AuthUser;
use Illuminate\Contracts\Auth\Authenticatable;

class ShiftPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }

    public function open(Authenticatable $user): bool
    {
        return true;
    }

    public function close(Authenticatable $user, Shift $shift): bool
    {
        if (AuthUser::isManagerOrAbove($user)) {
            return true;
        }

        return AuthUser::employeeId($user) === $shift->employee_id;
    }

    public function view(Authenticatable $user, Shift $shift): bool
    {
        if (AuthUser::isManagerOrAbove($user)) {
            return true;
        }

        return AuthUser::employeeId($user) === $shift->employee_id;
    }
}
