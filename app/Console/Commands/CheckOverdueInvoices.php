<?php

// المسار: app/Console/Commands/CheckOverdueInvoices.php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature   = 'erp:check-overdue-invoices';
    protected $description = 'Check for overdue customer invoices and notify finance managers';

    public function handle(): int
    {
        $this->info('Checking overdue invoices...');

        $overdueInvoices = Invoice::where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->whereRaw('paid_amount < total_amount')
            ->with('customer')
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return self::SUCCESS;
        }

        $count        = $overdueInvoices->count();
        $totalDue     = $overdueInvoices->sum(fn($i) => $i->total_amount - $i->paid_amount);
        $formattedDue = number_format($totalDue, 2);

        $this->warn("Found {$count} overdue invoice(s). Total due: {$formattedDue}");

        // Notify finance managers and admins
        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'finance_manager']))
            ->get();

        foreach ($managers as $manager) {
            Notification::notify(
                userId: $manager->id,
                type: 'overdue_invoice',
                titleAr: "تنبيه: {$count} فاتورة متأخرة السداد",
                titleEn: "Alert: {$count} Overdue Invoice(s)",
                bodyAr: "إجمالي المبالغ المتأخرة: {$formattedDue} جنيه",
                bodyEn: "Total overdue amount: {$formattedDue} SDG",
                url: '/invoices?filter=overdue',
                data: [
                    'count'       => $count,
                    'total_due'   => $totalDue,
                    'invoice_ids' => $overdueInvoices->pluck('id')->toArray(),
                ],
                icon: 'exclamation-circle',
                color: 'red'
            );
        }

        // Also update invoice status to 'overdue' if not already
        Invoice::whereIn('id', $overdueInvoices->pluck('id'))
            ->where('status', 'unpaid')
            ->update(['status' => 'overdue']);

        $this->info("Notifications sent to {$managers->count()} user(s).");

        return self::SUCCESS;
    }
}
