<?php

namespace Database\Seeders;

use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employeeRole = Role::where('slug', RoleEnum::EMPLOYEE->value)->firstOrFail();

        $employeeRole->users()
            ->attach(
                User::factory()
                    ->count(20)
                    ->create()
                    ->pluck('id')
                    ->all()
            );
    }
}