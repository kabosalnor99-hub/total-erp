<?php

// المسار الكامل: app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'tax_number',
        'payment_terms',
        'rating',
        'balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'rating'  => 'integer',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getPaymentTermsLabelAttribute(): string
    {
        return match ($this->payment_terms) {
            'cash'   => 'نقدي',
            'net_7'  => 'صافي 7 أيام',
            'net_15' => 'صافي 15 يوم',
            'net_30' => 'صافي 30 يوم',
            'net_60' => 'صافي 60 يوم',
            default  => $this->payment_terms,
        };
    }

    public function getRatingStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'نشط' : 'غير نشط';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status === 'active' ? 'success' : 'danger';
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('company_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /** إجمالي المشتريات من هذا المورد */
    public function totalPurchases(): float
    {
        return (float) $this->purchaseOrders()
            ->whereIn('status', ['received', 'partial'])
            ->sum('total');
    }

    /** إجمالي ما دُفع للمورد */
    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /** المبلغ المستحق للمورد */
    public function outstandingBalance(): float
    {
        return $this->totalPurchases() - $this->totalPaid();
    }
}
