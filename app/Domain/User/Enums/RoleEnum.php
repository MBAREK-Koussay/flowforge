<?php

namespace App\Domain\User\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';
    case FINANCE = 'finance';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::EMPLOYEE => 'Employee',
            self::FINANCE => 'Finance',
        };
    }
}