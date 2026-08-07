<?php

namespace Database\Seeders;

use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            ['name' => 'System Administrator', 'email' => 'admin@flowforge.dev', 'role' => RoleEnum::ADMIN],
            ['name' => 'Jane Manager', 'email' => 'manager@flowforge.dev', 'role' => RoleEnum::MANAGER],
            ['name' => 'Sarah Finance', 'email' => 'finance@flowforge.dev', 'role' => RoleEnum::FINANCE],
            ['name' => 'John Employee', 'email' => 'employee@flowforge.dev', 'role' => RoleEnum::EMPLOYEE],
        ];

        foreach ($demoUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'department' => $data['role']->label(),
                ]
            );

            $role = Role::where('slug', $data['role']->value)->first();

            if ($role !== null && ! $user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role);
            }
        }
    }
}