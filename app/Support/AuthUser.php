<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthUser
{
    public static function type(Authenticatable $user): string
    {
        return $user instanceof User ? 'admin' : 'employee';
    }

    public static function role(Authenticatable $user): string
    {
        if ($user instanceof User) {
            return Role::normalize($user->role ?? Role::ADMIN);
        }

        return Role::normalize($user->role ?? Role::CASHIER);
    }

    public static function isAdmin(Authenticatable $user): bool
    {
        return self::role($user) === Role::ADMIN;
    }

    public static function isManagerOrAbove(Authenticatable $user): bool
    {
        $role = self::role($user);

        return in_array($role, [Role::ADMIN, Role::MANAGER], true);
    }

    public static function employeeId(Authenticatable $user): ?int
    {
        return $user instanceof Employee ? $user->id : null;
    }

    public static function toArray(Authenticatable $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => self::type($user),
            'role' => self::role($user),
        ];
    }
}
