<?php

namespace App\Domain\User\Enums;

enum PermissionEnum: string
{
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';

    case CUSTOMERS_VIEW = 'customers.view';
    case CUSTOMERS_CREATE = 'customers.create';
    case CUSTOMERS_UPDATE = 'customers.update';
    case CUSTOMERS_DELETE = 'customers.delete';

    case PRODUCTS_VIEW = 'products.view';
    case PRODUCTS_CREATE = 'products.create';
    case PRODUCTS_UPDATE = 'products.update';
    case PRODUCTS_DELETE = 'products.delete';
    case PRODUCTS_MANAGE_STOCK = 'products.manage_stock';

    case PURCHASE_REQUESTS_VIEW = 'purchase_requests.view';
    case PURCHASE_REQUESTS_CREATE = 'purchase_requests.create';
    case PURCHASE_REQUESTS_UPDATE = 'purchase_requests.update';
    case PURCHASE_REQUESTS_DELETE = 'purchase_requests.delete';
    case PURCHASE_REQUESTS_APPROVE = 'purchase_requests.approve';

    case INVOICES_VIEW = 'invoices.view';
    case INVOICES_CREATE = 'invoices.create';
    case INVOICES_UPDATE = 'invoices.update';
    case INVOICES_DELETE = 'invoices.delete';
    case INVOICES_APPROVE = 'invoices.approve';
    case INVOICES_MARK_PAID = 'invoices.mark_paid';

    case WORKFLOWS_VIEW = 'workflows.view';
    case WORKFLOWS_CREATE = 'workflows.create';
    case WORKFLOWS_UPDATE = 'workflows.update';
    case WORKFLOWS_DELETE = 'workflows.delete';
    case WORKFLOWS_EXECUTE = 'workflows.execute';
    case WORKFLOWS_ACTIVATE = 'workflows.activate';

    case APPROVALS_VIEW = 'approvals.view';
    case APPROVALS_APPROVE = 'approvals.approve';

    case DASHBOARD_VIEW = 'dashboard.view';

    case AUDIT_VIEW = 'audit.view';

    case AI_GENERATE = 'ai.generate';

    public function label(): string
    {
        return ucwords(str_replace('.', ' ', $this->value));
    }
}