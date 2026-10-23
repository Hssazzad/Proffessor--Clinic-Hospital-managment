<?php

namespace App\Console\Commands;

use App\Services\Notification\BillingNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifyOverdueInvoices extends Command
{
    protected $signature = 'billing:notify-overdue';

    protected $description = 'Raise an in-app notification for each overdue (due > 0) invoice older than today.';

    public function handle(BillingNotificationService $service): int
    {
        $limit = 200;

        try {
            $invoices = DB::table('investigation_payments')
                ->where('DueAmount', '>', 0)
                ->where(function ($q) {
                    $q->where('approval_status', '!=', 'rejected')
                      ->orWhereNull('approval_status');
                })
                ->whereDate('PaymentDate', '<', now()->toDateString())
                ->orderBy('ID')
                ->limit($limit)
                ->get();

            $count = 0;
            foreach ($invoices as $invoice) {
                $service->overdueReminder($invoice);
                $count++;
            }

            Log::info('Billing overdue reminder run complete', ['processed' => $count]);
            $this->info("Processed {$count} overdue invoice(s).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('billing:notify-overdue failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
