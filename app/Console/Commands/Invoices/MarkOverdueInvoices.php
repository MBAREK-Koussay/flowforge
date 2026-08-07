<?php

namespace App\Console\Commands\Invoices;

use App\Domain\Invoice\Services\InvoiceService;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Marks pending invoices whose due date has passed as overdue';

    public function handle(InvoiceService $invoiceService): int
    {
        $count = $invoiceService->markExpiredOverdue();

        $this->info("Marked {$count} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}