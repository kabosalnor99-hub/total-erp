<?php

// المسار الكامل: app/Models/PosSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'total_sales',
        'total_cash',
        'total_credit',
        'total_discount',
        'cash_in',
        'cash_out',
        'transactions_count',
        'status',
        'closing_notes',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opening_balance'    => 'decimal:2',
        'closing_balance'    => 'decimal:2',
        'expected_balance'   => 'decimal:2',
        'total_sales'        => 'decimal:2',
        'total_cash'         => 'decimal:2',
        'total_credit'       => 'decimal:2',
        'total_discount'     => 'decimal:2',
        'cash_in'            => 'decimal:2',
        'cash_out'           => 'decimal:2',
        'transactions_count' => 'integer',
        'opened_at'          => 'datetime',
        'closed_at'          => 'datetime',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class, 'session_id');
    }

    public function completedTransactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class, 'session_id')
                    ->where('status', 'completed');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getDurationAttribute(): string
    {
        $end = $this->closed_at ?? now();
        $diff = $this->opened_at->diff($end);

        if ($diff->h > 0) {
            return "{$diff->h}س {$diff->i}د";
        }
        return "{$diff->i} دقيقة";
    }

    public function getDifferenceAttribute(): float
    {
        if (is_null($this->closing_balance)) return 0;
        return (float)$this->closing_balance - (float)$this->expected_balance;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * تحديث إحصائيات الجلسة بعد كل معاملة
     */
    public function recalculate(): void
    {
        $completed = $this->completedTransactions();

        $this->update([
            'total_sales'        => $completed->sum('total'),
            'total_cash'         => $completed->sum('cash_amount'),
            'total_credit'       => $completed->sum('credit_amount'),
            'total_discount'     => $completed->sum('discount_amount'),
            'transactions_count' => $completed->count(),
            'expected_balance'   => $this->opening_balance
                                    + $completed->sum('cash_amount')
                                    + $this->cash_in
                                    - $this->cash_out,
        ]);
    }

    /**
     * إغلاق الجلسة
     */
    public function close(float $closingBalance, ?string $notes = null): void
    {
        $this->recalculate();
        $this->update([
            'status'          => 'closed',
            'closing_balance' => $closingBalance,
            'closing_notes'   => $notes,
            'closed_at'       => now(),
        ]);
    }

    /**
     * الجلسة المفتوحة للمستخدم الحالي
     */
    public static function currentOpen(): ?self
    {
        return static::where('user_id', auth()->id())
                     ->where('status', 'open')
                     ->latest()
                     ->first();
    }
}
