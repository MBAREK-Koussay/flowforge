<?php

namespace App\Domain\User\Services;

use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

final class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'department' => $data['department'] ?? null,
            'job_title' => $data['job_title'] ?? null,
        ]);

        $employeeRole = Role::where('slug', RoleEnum::EMPLOYEE->value)->first();

        if ($employeeRole !== null) {
            $user->roles()->attach($employeeRole);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [$user, $token];
    }

    public function login(array $credentials): ?array
    {
        if (! auth()->attempt($credentials)) {
            return null;
        }

        $user = auth()->user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [$user, $token];
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}