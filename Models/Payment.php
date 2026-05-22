<?php

// المسار الكامل: app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'customer_id',
        'user_id',
        'amount',
        'method',
        'reference',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'cash'   => 'نقدي',
            'bank'   => 'تحويل بنكي',
            'cheque' => 'شيك',
            'other'  => 'أخرى',
            default  => '—',
        ];
    }

    public function getMethodIconAttribute(): string
    {
        return match ($this->method) {
            'cash'   => 'fa-money-bill',
            'bank'   => 'fa-building-columns',
            'cheque' => 'fa-file-invoice-dollar',
            default  => 'fa-circle-question',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /** توليد رقم سند قبض تلقائي */
    public static function generateNumber(): string
    {
        $last = self::latest('id')->value('id') ?? 0;
        $year = date('Y');
        return 'RCV-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
