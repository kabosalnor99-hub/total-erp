<?php

// المسار الكامل: app/Models/JournalEntryLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getTypeAttribute(): string
    {
        if ($this->debit > 0) return 'debit';
        if ($this->credit > 0) return 'credit';
        return 'zero';
    }

    public function getAmountAttribute(): float
    {
        return $this->debit > 0 ? (float) $this->debit : (float) $this->credit;
    }
}
