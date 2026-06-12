<?php

namespace App\Enums;

class Role
{
    public const ADMIN = 'admin';
    public const MANAGER = 'manager';
    public const CASHIER = 'cashier';

    public static function all(): array
    {
        return [self::ADMIN, self::MANAGER, self::CASHIER];
    }

    public static function normalize(?string $role): string
    {
        $role = strtolower(trim((string) $role));

        if (in_array($role, self::all(), true)) {
            return $role;
        }

        if (in_array($role, ['مدير', 'manager'], true)) {
            return self::MANAGER;
        }

        if (in_array($role, ['كاشير', 'cashier', 'بائع'], true)) {
            return self::CASHIER;
        }

        if (in_array($role, ['admin', 'مسؤول', 'مدير نظام'], true)) {
            return self::ADMIN;
        }

        return self::CASHIER;
    }
}
