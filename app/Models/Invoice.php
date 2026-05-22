<?php

// المسار الكامل: app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'type',
        'status',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'tax_percent',
        'tax_amount',
        'total',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'notes',
        'reference',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total'            => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date'         => 'date',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['confirmed', 'partial'])
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now()->toDateString());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'مسودة',
            'confirmed' => 'مؤكدة',
            'paid'      => 'مدفوعة',
            'partial'   => 'جزئية',
            'cancelled' => 'ملغاة',
            default     => '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'gray',
            'confirmed' => 'blue',
            'paid'      => 'green',
            'partial'   => 'yellow',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'cash'    => 'نقدي',
            'credit'  => 'آجل',
            'partial' => 'جزئي',
            default   => '—',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, ['confirmed', 'partial'])
            && $this->due_date
            && $this->due_date->isPast();
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /** توليد رقم فاتورة تلقائي */
    public static function generateNumber(): string
    {
        $last = self::withTrashed()->latest('id')->value('id') ?? 0;
        $year = date('Y');
        return 'INV-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    /** إعادة حساب مبالغ الفاتورة */
    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('total');

        $discountAmount = $this->discount_percent > 0
            ? round($subtotal * $this->discount_percent / 100, 2)
            : $this->discount_amount;

        $afterDiscount = $subtotal - $discountAmount;

        $taxAmount = $this->tax_percent > 0
            ? round($afterDiscount * $this->tax_percent / 100, 2)
            : 0;

        $total = $afterDiscount + $taxAmount;
        $remaining = $total - $this->paid_amount;

        $this->update([
            'subtotal'         => $subtotal,
            'discount_amount'  => $discountAmount,
            'tax_amount'       => $taxAmount,
            'total'            => $total,
            'remaining_amount' => max(0, $remaining),
        ]);
    }

    /** تحديث حالة الفاتورة بعد دفعة */
    public function updateStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $this->paid_amount      = $paid;
        $this->remaining_amount = max(0, $this->total - $paid);

        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($paid > 0) {
            $this->status = 'partial';
        }

        $this->save();

        // تحديث رصيد العميل
        $this->customer?->recalculateBalance();
    }
}
