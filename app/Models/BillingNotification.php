<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BillingNotification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_read'     => 'boolean',
        'notified_at' => 'datetime',
        'amount'      => 'float',
    ];

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Drops notifications whose underlying invoice no longer warrants them:
     * an invoice_due / overdue_reminder once the due is settled, or a
     * pending_approval once it has been approved. payment_reference records
     * are always kept. Applied at read time so historically-stale rows
     * (created before this fix) stop showing without manual cleanup.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNotIn('type', ['invoice_due', 'overdue_reminder', 'pending_approval'])
              ->orWhere(function (Builder $q2): void {
                  $q2->whereIn('type', ['invoice_due', 'overdue_reminder'])
                     ->whereExists(function ($sub): void {
                         $sub->select(DB::raw(1))
                             ->from('investigation_payments')
                             ->whereColumn('investigation_payments.ID', 'billing_notifications.invoice_id')
                             ->where('investigation_payments.DueAmount', '>', 0);
                     });
              })
              ->orWhere(function (Builder $q2): void {
                  $q2->where('type', 'pending_approval')
                     ->whereExists(function ($sub): void {
                         $sub->select(DB::raw(1))
                             ->from('investigation_payments')
                             ->whereColumn('investigation_payments.ID', 'billing_notifications.invoice_id')
                             ->where('investigation_payments.approval_status', 'pending');
                     });
              });
        });
    }

    public function markRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
