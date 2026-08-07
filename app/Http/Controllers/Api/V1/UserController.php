<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\AssignRolesRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $query->where('name', 'like', "%{$request->string('search')}%")
                    ->orWhere('email', 'like', "%{$request->string('search')}%");
            }))
            ->when($request->filled('role'), fn ($query) => $query->whereHas('roles', fn ($query) => $query->where('slug', $request->string('role'))))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success(UserResource::collection($users));
    }

    public function assignRoles(User $user, AssignRolesRequest $request): JsonResponse
    {
        $user->roles()->sync($request->validated('roles'));

        return ApiResponse::success(new UserResource($user->load('roles')), 'Roles updated successfully.');
    }
}