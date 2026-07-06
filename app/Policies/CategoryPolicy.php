<?php

namespace App\Policies;

use App\Support\AuthUser;
use Illuminate\Contracts\Auth\Authenticatable;

class CategoryPolicy
{
    public function manage(Authenticatable $user): bool
    {
        return AuthUser::isManagerOrAbove($user);
    }
}
