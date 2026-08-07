<?php

namespace App\Domain\User\Models;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'job_title',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function allPermissions(): Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions);
    }

    public function hasRole(string|RoleEnum $role): bool
    {
        $slug = $role instanceof RoleEnum ? $role->value : $role;

        return $this->roles->contains('slug', $slug);
    }

    public function hasAnyRole(array $roles): bool
    {
        $slugs = array_map(fn (string|RoleEnum $role): string => $role instanceof RoleEnum ? $role->value : $role, $roles);

        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function hasPermission(string|PermissionEnum $permission): bool
    {
        $slug = $permission instanceof PermissionEnum ? $permission->value : $permission;

        return $this->allPermissions()->contains('slug', $slug);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        $slugs = array_map(fn (string|PermissionEnum $permission): string => $permission instanceof PermissionEnum ? $permission->value : $permission, $permissions);

        return $this->allPermissions()->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::ADMIN);
    }
}