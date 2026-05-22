<?php

// المسار الكامل: app/Models/JournalEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'date',
        'description',
        'user_id',
        'status',
        'reference_type',
        'reference_id',
        'source',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'entry_id')->orderBy('sort_order');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'مسودة',
            'posted' => 'مُرحَّل',
            default  => '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'yellow',
            'posted' => 'green',
            default  => 'gray',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'manual'     => 'يدوي',
            'invoice'    => 'فاتورة بيع',
            'payment'    => 'دفعة عميل',
            'purchase'   => 'مشتريات',
            'payroll'    => 'رواتب',
            'pos'        => 'نقطة البيع',
            'adjustment' => 'تسوية',
            default      => '—',
        };
    }

    public function getTotalDebitAttribute(): float
    {
        return round($this->lines->sum('debit'), 2);
    }

    public function getTotalCreditAttribute(): float
    {
        return round($this->lines->sum('credit'), 2);
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * توليد رقم قيد تلقائي
     */
    public static function generateNumber(): string
    {
        $last = self::withoutGlobalScopes()->latest('id')->value('id') ?? 0;
        $year = date('Y');
        return 'JE-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * ترحيل القيد (تغيير الحالة من مسودة إلى مُرحَّل)
     */
    public function post(): void
    {
        if ($this->status === 'posted') {
            throw new \Exception('القيد مُرحَّل مسبقاً.');
        }

        if (! $this->is_balanced) {
            throw new \Exception('لا يمكن ترحيل قيد غير متوازن. المدين ≠ الدائن.');
        }

        $this->update(['status' => 'posted']);
    }

    /**
     * إلغاء ترحيل القيد (رجوع لمسودة)
     */
    public function unpost(): void
    {
        if ($this->status === 'draft') {
            throw new \Exception('القيد في حالة مسودة مسبقاً.');
        }

        $this->update(['status' => 'draft']);
    }
}
