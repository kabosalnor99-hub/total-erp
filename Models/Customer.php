<?php

// المسار الكامل: app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'phone_alt',
        'email',
        'address',
        'type',
        'company_name',
        'tax_number',
        'classification',
        'credit_limit',
        'balance',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance'      => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVip($query)
    {
        return $query->where('classification', 'vip');
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    // ─── Accessors ───────────────────────────────────────────────

    /** تصنيف العميل بالعربية */
    public function getClassificationLabelAttribute(): string
    {
        return match ($this->classification) {
            'vip'      => 'VIP',
            'regular'  => 'عادي',
            'inactive' => 'غير نشط',
            default    => '—',
        };
    }

    /** لون تصنيف العميل */
    public function getClassificationColorAttribute(): string
    {
        return match ($this->classification) {
            'vip'      => 'yellow',
            'regular'  => 'blue',
            'inactive' => 'gray',
            default    => 'gray',
        };
    }

    /** نوع العميل بالعربية */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'individual' => 'فرد',
            'company'    => 'شركة',
            default      => '—',
        };
    }

    /** هل تجاوز حد الائتمان */
    public function getOverCreditAttribute(): bool
    {
        return $this->credit_limit > 0 && $this->balance > $this->credit_limit;
    }

    /** إجمالي المشتريات */
    public function getTotalPurchasesAttribute(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['confirmed', 'paid', 'partial'])
            ->sum('total');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /** تحديث رصيد العميل بعد الفاتورة أو الدفعة */
    public function recalculateBalance(): void
    {
        $totalInvoiced = $this->invoices()
            ->whereIn('status', ['confirmed', 'partial'])
            ->sum('remaining_amount');

        $this->update(['balance' => max(0, $totalInvoiced)]);
    }
}
