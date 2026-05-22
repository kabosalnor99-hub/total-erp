<?php

// المسار الكامل: app/Services/InvoiceService.php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * إنشاء فاتورة جديدة مع بنودها وخصم المخزون
     */
    public function createInvoice(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {

            // إنشاء الفاتورة
            $invoice = Invoice::create([
                'invoice_number'   => Invoice::generateNumber(),
                'customer_id'      => $data['customer_id'] ?? null,
                'user_id'          => auth()->id(),
                'type'             => $data['type'] ?? 'cash',
                'status'           => 'confirmed',
                'discount_percent' => $data['discount_percent'] ?? 0,
                'discount_amount'  => $data['discount_amount'] ?? 0,
                'tax_percent'      => $data['tax_percent'] ?? 0,
                'due_date'         => $data['due_date'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'reference'        => $data['reference'] ?? null,
                'subtotal'         => 0,
                'tax_amount'       => 0,
                'total'            => 0,
                'paid_amount'      => 0,
                'remaining_amount' => 0,
            ]);

            // إضافة البنود وخصم المخزون
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // التحقق من توفر الكمية
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("الكمية المطلوبة من {$product->name_ar} غير متوفرة في المخزون.");
                }

                $lineTotal = $this->calcLineTotal(
                    $item['quantity'],
                    $item['unit_price'],
                    $item['discount_percent'] ?? 0,
                    $item['discount_amount'] ?? 0,
                );

                // إنشاء بند الفاتورة
                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'product_id'       => $product->id,
                    'product_name'     => $product->name_ar,
                    'product_sku'      => $product->sku,
                    'unit'             => $product->unit,
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount'  => $item['discount_amount'] ?? 0,
                    'total'            => $lineTotal,
                ]);

                // خصم من المخزون
                StockMovement::record(
                    $product->id,
                    $data['warehouse_id'] ?? 1,
                    'out',
                    $item['quantity'],
                    [
                        'reference_type' => Invoice::class,
                        'reference_id'   => $invoice->id,
                        'unit_cost'      => $item['unit_price'],
                        'notes'          => "فاتورة بيع #{$invoice->invoice_number}",
                    ]
                );
            }

            // إعادة حساب الإجماليات
            $invoice->recalculate();

            // إذا كانت نقدية: تسجيل دفعة تلقائية بالكامل
            if ($invoice->type === 'cash' && isset($data['cash_paid'])) {
                $this->addPayment($invoice, [
                    'amount'       => $invoice->total,
                    'method'       => 'cash',
                    'payment_date' => today()->toDateString(),
                    'notes'        => 'دفع نقدي عند إنشاء الفاتورة',
                ]);
            }

            // تحديث رصيد العميل
            $invoice->customer?->recalculateBalance();

            return $invoice->fresh(['items', 'customer', 'payments']);
        });
    }

    /**
     * إضافة دفعة لفاتورة موجودة
     */
    public function addPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {

            $payment = Payment::create([
                'payment_number' => Payment::generateNumber(),
                'invoice_id'     => $invoice->id,
                'customer_id'    => $invoice->customer_id,
                'user_id'        => auth()->id(),
                'amount'         => $data['amount'],
                'method'         => $data['method'] ?? 'cash',
                'reference'      => $data['reference'] ?? null,
                'payment_date'   => $data['payment_date'] ?? today()->toDateString(),
                'notes'          => $data['notes'] ?? null,
            ]);

            // تحديث حالة الفاتورة
            $invoice->updateStatus();

            return $payment;
        });
    }

    /**
     * إلغاء فاتورة وإعادة المخزون
     */
    public function cancelInvoice(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {

            if ($invoice->status === 'cancelled') {
                throw new \Exception('الفاتورة ملغاة مسبقاً.');
            }

            if ($invoice->paid_amount > 0) {
                throw new \Exception('لا يمكن إلغاء فاتورة تم دفع جزء منها.');
            }

            // إعادة المخزون
            foreach ($invoice->items as $item) {
                if ($item->product_id) {
                    StockMovement::record(
                        $item->product_id,
                        1,
                        'return_out',
                        $item->quantity,
                        [
                            'reference_type' => Invoice::class,
                            'reference_id'   => $invoice->id,
                            'notes'          => "إلغاء فاتورة #{$invoice->invoice_number}",
                        ]
                    );
                }
            }

            $invoice->update(['status' => 'cancelled']);

            $invoice->customer?->recalculateBalance();
        });
    }

    /**
     * مرتجع بيع جزئي أو كلي
     */
    public function returnItems(Invoice $invoice, array $returnItems): Invoice
    {
        return DB::transaction(function () use ($invoice, $returnItems) {

            foreach ($returnItems as $item) {
                if (! $item['product_id'] || ! $item['quantity']) {
                    continue;
                }

                StockMovement::record(
                    $item['product_id'],
                    1,
                    'return_out',
                    $item['quantity'],
                    [
                        'reference_type' => Invoice::class,
                        'reference_id'   => $invoice->id,
                        'notes'          => "مرتجع من فاتورة #{$invoice->invoice_number}",
                    ]
                );
            }

            return $invoice->fresh();
        });
    }

    // ─── Private Helpers ─────────────────────────────────────────

    private function calcLineTotal(
        int $qty,
        float $unitPrice,
        float $discountPct = 0,
        float $discountAmt = 0
    ): float {
        $subtotal = $qty * $unitPrice;
        $discount = $discountPct > 0
            ? round($subtotal * $discountPct / 100, 2)
            : $discountAmt;

        return round($subtotal - $discount, 2);
    }
}
