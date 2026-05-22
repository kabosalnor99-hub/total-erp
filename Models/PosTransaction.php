<?php

// المسار الكامل: app/Models/PosTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'session_id',
        'customer_id',
        'user_id',
        'invoice_id',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'tax_percent',
        'tax_amount',
        'total',
        'payment_type',
        'cash_amount',
        'credit_amount',
        'cash_received',
        'change_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total'            => 'decimal:2',
        'cash_amount'      => 'decimal:2',
        'credit_amount'    => 'decimal:2',
        'cash_received'    => 'decimal:2',
        'change_amount'    => 'decimal:2',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'session_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosTransactionItem::class, 'transaction_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'cash'   => 'نقدي',
            'credit' => 'آجل',
            'split'  => 'مختلط',
            default  => '—',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
            'held'      => 'معلقة',
            default     => '—',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'cancelled' => 'red',
            'held'      => 'yellow',
            default     => 'gray',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * توليد رقم إيصال تلقائي
     */
    public static function generateReceiptNumber(): string
    {
        $prefix = 'POS-' . date('Ymd') . '-';
        $last = static::where('receipt_number', 'like', $prefix . '%')
                       ->orderByDesc('id')
                       ->value('receipt_number');

        if ($last) {
            $seq = (int) substr($last, strrpos($last, '-') + 1);
            return $prefix . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }
}
