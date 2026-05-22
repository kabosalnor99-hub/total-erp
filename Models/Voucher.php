<?php

// المسار الكامل: app/Models/Voucher.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'type',
        'date',
        'account_id',
        'cash_account_id',
        'amount',
        'description',
        'payment_method',
        'cheque_number',
        'bank_reference',
        'reference_type',
        'reference_id',
        'journal_entry_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeReceipts($query)
    {
        return $query->where('type', 'receipt');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'receipt' => 'سند قبض',
            'payment' => 'سند صرف',
            default   => '—',
        ];
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'receipt' => 'green',
            'payment' => 'red',
            default   => 'gray',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'   => 'نقدي',
            'bank'   => 'تحويل بنكي',
            'cheque' => 'شيك',
            default  => '—',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * توليد رقم سند تلقائي
     */
    public static function generateNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'RV' : 'PV';
        $last   = self::where('type', $type)->latest('id')->value('id') ?? 0;
        $year   = date('Y');
        return $prefix . '-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
