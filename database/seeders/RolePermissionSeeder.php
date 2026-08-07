<?php

namespace Database\Seeders;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\Permission;
use App\Domain\User\Models\Role;
use App\Domain\User\Services\RbacService;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::updateOrCreate(
                ['slug' => $role->value],
                ['name' => $role->label(), 'description' => $role->label().' role']
            );
        }

        foreach (PermissionEnum::cases() as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission->value],
                ['name' => $permission->label(), 'group' => explode('.', $permission->value)[0]]
            );
        }

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::where('slug', $roleEnum->value)->firstOrFail();
            $permissionIds = Permission::whereIn('slug', RbacService::permissionsForRole($roleEnum))->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}