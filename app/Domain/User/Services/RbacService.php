<?php

namespace App\Domain\User\Services;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;

final class RbacService
{
    public static function permissionsForRole(RoleEnum $role): array
    {
        return match ($role) {
            RoleEnum::ADMIN => array_column(PermissionEnum::cases(), 'value'),

            RoleEnum::MANAGER => [
                PermissionEnum::USERS_VIEW->value,

                PermissionEnum::CUSTOMERS_VIEW->value,
                PermissionEnum::CUSTOMERS_CREATE->value,
                PermissionEnum::CUSTOMERS_UPDATE->value,

                PermissionEnum::PRODUCTS_VIEW->value,
                PermissionEnum::PRODUCTS_CREATE->value,
                PermissionEnum::PRODUCTS_UPDATE->value,
                PermissionEnum::PRODUCTS_MANAGE_STOCK->value,

                PermissionEnum::PURCHASE_REQUESTS_VIEW->value,
                PermissionEnum::PURCHASE_REQUESTS_APPROVE->value,

                PermissionEnum::INVOICES_VIEW->value,
                PermissionEnum::INVOICES_CREATE->value,
                PermissionEnum::INVOICES_UPDATE->value,
                PermissionEnum::INVOICES_APPROVE->value,

                PermissionEnum::WORKFLOWS_VIEW->value,
                PermissionEnum::WORKFLOWS_CREATE->value,
                PermissionEnum::WORKFLOWS_UPDATE->value,
                PermissionEnum::WORKFLOWS_EXECUTE->value,
                PermissionEnum::WORKFLOWS_ACTIVATE->value,

                PermissionEnum::APPROVALS_VIEW->value,
                PermissionEnum::APPROVALS_APPROVE->value,

                PermissionEnum::DASHBOARD_VIEW->value,
                PermissionEnum::AUDIT_VIEW->value,
                PermissionEnum::AI_GENERATE->value,
            ],

            RoleEnum::FINANCE => [
                PermissionEnum::CUSTOMERS_VIEW->value,

                PermissionEnum::PRODUCTS_VIEW->value,

                PermissionEnum::PURCHASE_REQUESTS_VIEW->value,
                PermissionEnum::PURCHASE_REQUESTS_APPROVE->value,

                PermissionEnum::INVOICES_VIEW->value,
                PermissionEnum::INVOICES_CREATE->value,
                PermissionEnum::INVOICES_UPDATE->value,
                PermissionEnum::INVOICES_APPROVE->value,
                PermissionEnum::INVOICES_MARK_PAID->value,

                PermissionEnum::WORKFLOWS_VIEW->value,
                PermissionEnum::WORKFLOWS_EXECUTE->value,

                PermissionEnum::APPROVALS_VIEW->value,
                PermissionEnum::APPROVALS_APPROVE->value,

                PermissionEnum::DASHBOARD_VIEW->value,
            ],

            RoleEnum::EMPLOYEE => [
                PermissionEnum::DASHBOARD_VIEW->value,

                PermissionEnum::PRODUCTS_VIEW->value,

                PermissionEnum::PURCHASE_REQUESTS_VIEW->value,
                PermissionEnum::PURCHASE_REQUESTS_CREATE->value,
                PermissionEnum::PURCHASE_REQUESTS_UPDATE->value,

                PermissionEnum::INVOICES_VIEW->value,

                PermissionEnum::APPROVALS_VIEW->value,
            ],
        };
    }
}