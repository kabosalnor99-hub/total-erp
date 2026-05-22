<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'type',
        'normal_balance',
        'parent_id',
        'level',
        'is_leaf',
        'is_active',
        'description',
        'opening_balance',
        'opening_balance_type',
    ];

    protected $casts = [
        'is_leaf'          => 'boolean',
        'is_active'        => 'boolean',
        'opening_balance'  => 'decimal:2',
        'level'            => 'integer',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function cashVouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'cash_account_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLeaf($query)
    {
        return $query->where('is_leaf', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAssets($query)
    {
        return $query->where('type', 'asset');
    }

    public function scopeLiabilities($query)
    {
        return $query->where('type', 'liability');
    }

    public function scopeRevenues($query)
    {
        return $query->where('type', 'revenue');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'asset'     => 'أصول',
            'liability' => 'خصوم',
            'equity'    => 'حقوق ملكية',
            'revenue'   => 'إيرادات',
            'expense'   => 'مصروفات',
        ];
        return $labels[$this->type] ?? '—';
    }

    public function getTypeColorAttribute(): string
    {
        $colors = [
            'asset'     => 'blue',
            'liability' => 'red',
            'equity'    => 'purple',
            'revenue'   => 'green',
            'expense'   => 'orange',
        ];
        return $colors[$this->type] ?? 'gray';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->code} — {$this->name_ar}";
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * حساب الرصيد الحالي للحساب (مجموع القيود المرحّلة)
     */
    public function getBalance(): float
    {
        $debit  = $this->journalLines()
            ->whereHas('entry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');

        $credit = $this->journalLines()
            ->whereHas('entry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit');

        $openingDebit  = $this->opening_balance_type === 'debit'  ? $this->opening_balance : 0;
        $openingCredit = $this->opening_balance_type === 'credit' ? $this->opening_balance : 0;

        $totalDebit  = $debit  + $openingDebit;
        $totalCredit = $credit + $openingCredit;

        if ($this->normal_balance === 'debit') {
            return round($totalDebit - $totalCredit, 2);
        }

        return round($totalCredit - $totalDebit, 2);
    }

    /**
     * توليد كود حساب تلقائي بناءً على النوع والأب
     */
    public static function generateCode(string $type, ?int $parentId = null): string
    {
        $prefixes = [
            'asset'     => 1,
            'liability' => 2,
            'equity'    => 3,
            'revenue'   => 4,
            'expense'   => 5,
        ];

        $prefix = $prefixes[$type] ?? 9;

        if ($parentId) {
            $parent = self::find($parentId);
            $siblings = self::where('parent_id', $parentId)->count();
            return $parent->code . str_pad($siblings + 1, 2, '0', STR_PAD_LEFT);
        }

        $count = self::where('type', $type)->whereNull('parent_id')->count();
        return $prefix . '0' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }
}
