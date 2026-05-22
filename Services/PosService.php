<?php

// المسار الكامل: app/Services/PosService.php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PosSession;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PosService
{
    /**
     * فتح جلسة كاشير جديدة
     */
    public function openSession(float $openingBalance): PosSession
    {
        // تحقق لا توجد جلسة مفتوحة للمستخدم الحالي
        $existing = PosSession::currentOpen();
        if ($existing) {
            throw new \Exception('لديك جلسة مفتوحة بالفعل. يجب إغلاقها أولاً.');
        }

        return PosSession::create([
            'user_id'         => auth()->id(),
            'opening_balance' => $openingBalance,
            'expected_balance'=> $openingBalance,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);
    }

    /**
     * إتمام عملية البيع من الكاشير
     *
     * @param PosSession $session   الجلسة الحالية
     * @param array      $cartItems [['product_id'=>1,'quantity'=>2,'price'=>50,'discount_percent'=>0], ...]
     * @param array      $payData   بيانات الدفع
     * @param int|null   $customerId معرف العميل (اختياري)
     * @return PosTransaction
     */
    public function processSale(
        PosSession $session,
        array      $cartItems,
        array      $payData,
        ?int       $customerId = null
    ): PosTransaction {

        if ($session->status !== 'open') {
            throw new \Exception('الجلسة مغلقة. لا يمكن إجراء عمليات بيع.');
        }

        return DB::transaction(function () use ($session, $cartItems, $payData, $customerId) {

            // ── 1. حساب الإجماليات ──────────────────────────────────
            $subtotal        = 0;
            $totalDiscount   = 0;
            $processedItems  = [];

            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);

                // التحقق من الكمية
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception(
                        "الكمية المطلوبة ({$item['quantity']}) من «{$product->name_ar}» غير متوفرة. المتاح: {$product->quantity}"
                    );
                }

                $unitPrice       = (float)($item['price'] ?? $product->sale_price);
                $qty             = (float)$item['quantity'];
                $discPct         = (float)($item['discount_percent'] ?? 0);
                $discAmt         = round($unitPrice * $qty * ($discPct / 100), 2);
                $lineTotal       = round($unitPrice * $qty - $discAmt, 2);

                $subtotal       += $unitPrice * $qty;
                $totalDiscount  += $discAmt;

                $processedItems[] = [
                    'product'          => $product,
                    'quantity'         => $qty,
                    'unit_price'       => $product->sale_price,
                    'price'            => $unitPrice,
                    'discount_percent' => $discPct,
                    'discount_amount'  => $discAmt,
                    'total'            => $lineTotal,
                ];
            }

            // خصم على مستوى الفاتورة
            $invoiceDiscPct = (float)($payData['discount_percent'] ?? 0);
            $invoiceDiscAmt = round(($subtotal - $totalDiscount) * ($invoiceDiscPct / 100), 2);
            $afterDiscount  = $subtotal - $totalDiscount - $invoiceDiscAmt;
            $totalDiscount += $invoiceDiscAmt;

            // الضريبة
            $taxPct    = (float)($payData['tax_percent'] ?? 0);
            $taxAmount = round($afterDiscount * ($taxPct / 100), 2);
            $total     = $afterDiscount + $taxAmount;

            // ── 2. حساب مبالغ الدفع ─────────────────────────────────
            $paymentType  = $payData['payment_type'] ?? 'cash';
            $cashReceived = (float)($payData['cash_received'] ?? 0);
            $cashAmount   = 0;
            $creditAmount = 0;
            $changeAmount = 0;

            switch ($paymentType) {
                case 'cash':
                    $cashAmount   = $total;
                    $changeAmount = max(0, $cashReceived - $total);
                    break;
                case 'credit':
                    $creditAmount = $total;
                    break;
                case 'split':
                    $cashAmount   = (float)($payData['cash_amount'] ?? 0);
                    $creditAmount = $total - $cashAmount;
                    $changeAmount = max(0, $cashReceived - $cashAmount);
                    break;
            }

            // ── 3. إنشاء معاملة POS ──────────────────────────────────
            $transaction = PosTransaction::create([
                'receipt_number'   => PosTransaction::generateReceiptNumber(),
                'session_id'       => $session->id,
                'customer_id'      => $customerId,
                'user_id'          => auth()->id(),
                'subtotal'         => $subtotal,
                'discount_amount'  => $totalDiscount,
                'discount_percent' => $invoiceDiscPct,
                'tax_percent'      => $taxPct,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'payment_type'     => $paymentType,
                'cash_amount'      => $cashAmount,
                'credit_amount'    => $creditAmount,
                'cash_received'    => $cashReceived,
                'change_amount'    => $changeAmount,
                'status'           => 'completed',
                'notes'            => $payData['notes'] ?? null,
            ]);

            // ── 4. إضافة بنود المعاملة وخصم المخزون ──────────────────
            $defaultWarehouseId = \App\Models\Warehouse::where('is_default', true)->value('id')
                                ?? \App\Models\Warehouse::first()?->id;

            foreach ($processedItems as $item) {
                PosTransactionItem::create([
                    'transaction_id'   => $transaction->id,
                    'product_id'       => $item['product']->id,
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'price'            => $item['price'],
                    'discount_percent' => $item['discount_percent'],
                    'discount_amount'  => $item['discount_amount'],
                    'total'            => $item['total'],
                ]);

                // خصم المخزون
                $item['product']->decrement('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id'     => $item['product']->id,
                    'warehouse_id'   => $defaultWarehouseId,
                    'type'           => 'out',
                    'quantity'       => $item['quantity'],
                    'reference_type' => PosTransaction::class,
                    'reference_id'   => $transaction->id,
                    'user_id'        => auth()->id(),
                    'notes'          => "مبيعات POS — إيصال: {$transaction->receipt_number}",
                ]);
            }

            // ── 5. إنشاء فاتورة في وحدة المبيعات ────────────────────
            $invoiceStatus = match ($paymentType) {
                'cash'   => 'paid',
                'credit' => 'confirmed',
                'split'  => $creditAmount > 0 ? 'partial' : 'paid',
                default  => 'paid',
            };

            $invoice = Invoice::create([
                'invoice_number'   => Invoice::generateNumber(),
                'customer_id'      => $customerId,
                'user_id'          => auth()->id(),
                'type'             => $paymentType === 'credit' ? 'credit' : ($paymentType === 'split' ? 'partial' : 'cash'),
                'status'           => $invoiceStatus,
                'subtotal'         => $subtotal,
                'discount_amount'  => $totalDiscount,
                'discount_percent' => $invoiceDiscPct,
                'tax_percent'      => $taxPct,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'paid_amount'      => $cashAmount,
                'remaining_amount' => $creditAmount,
                'due_date'         => $creditAmount > 0 ? now()->addDays(30) : null,
                'reference'        => $transaction->receipt_number,
            ]);

            foreach ($processedItems as $item) {
                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'product_id'       => $item['product']->id,
                    'quantity'         => $item['quantity'],
                    'price'            => $item['price'],
                    'discount_percent' => $item['discount_percent'],
                    'total'            => $item['total'],
                ]);
            }

            // تسجيل الدفعة النقدية في وحدة المدفوعات
            if ($cashAmount > 0) {
                Payment::create([
                    'invoice_id'  => $invoice->id,
                    'customer_id' => $customerId,
                    'amount'      => $cashAmount,
                    'method'      => 'cash',
                    'notes'       => "دفع POS — إيصال: {$transaction->receipt_number}",
                ]);
            }

            // ربط الفاتورة بالمعاملة
            $transaction->update(['invoice_id' => $invoice->id]);

            // تحديث رصيد العميل إن وجد
            if ($customerId && $creditAmount > 0) {
                $customer = Customer::find($customerId);
                $customer?->increment('balance', $creditAmount);
            }

            // ── 6. تحديث إحصائيات الجلسة ────────────────────────────
            $session->recalculate();

            return $transaction->load(['items.product', 'customer', 'user']);
        });
    }

    /**
     * إضافة نقدي للصندوق (Petty Cash In)
     */
    public function cashIn(PosSession $session, float $amount, string $reason): void
    {
        $session->increment('cash_in', $amount);
        $session->recalculate();
    }

    /**
     * سحب نقدي من الصندوق (Petty Cash Out)
     */
    public function cashOut(PosSession $session, float $amount, string $reason): void
    {
        if ($amount > $session->expected_balance) {
            throw new \Exception('المبلغ المطلوب سحبه أكبر من رصيد الصندوق المتاح.');
        }
        $session->increment('cash_out', $amount);
        $session->recalculate();
    }

    /**
     * إلغاء معاملة وإعادة المخزون
     */
    public function cancelTransaction(PosTransaction $transaction): void
    {
        if ($transaction->status !== 'completed') {
            throw new \Exception('لا يمكن إلغاء هذه المعاملة.');
        }

        DB::transaction(function () use ($transaction) {
            $defaultWarehouseId = \App\Models\Warehouse::where('is_default', true)->value('id')
                                ?? \App\Models\Warehouse::first()?->id;

            // إعادة المخزون
            foreach ($transaction->items as $item) {
                $item->product->increment('quantity', $item->quantity);

                StockMovement::create([
                    'product_id'     => $item->product_id,
                    'warehouse_id'   => $defaultWarehouseId,
                    'type'           => 'in',
                    'quantity'       => $item->quantity,
                    'reference_type' => PosTransaction::class,
                    'reference_id'   => $transaction->id,
                    'user_id'        => auth()->id(),
                    'notes'          => "إلغاء مبيعات POS — إيصال: {$transaction->receipt_number}",
                ]);
            }

            // إلغاء الفاتورة المرتبطة
            if ($transaction->invoice) {
                $transaction->invoice->update(['status' => 'cancelled']);
            }

            // تحديث رصيد العميل
            if ($transaction->customer_id && $transaction->credit_amount > 0) {
                $transaction->customer?->decrement('balance', $transaction->credit_amount);
            }

            $transaction->update(['status' => 'cancelled']);
            $transaction->session->recalculate();
        });
    }

    /**
     * إغلاق الجلسة
     */
    public function closeSession(PosSession $session, float $closingBalance, ?string $notes = null): void
    {
        if ($session->status !== 'open') {
            throw new \Exception('الجلسة مغلقة بالفعل.');
        }
        $session->close($closingBalance, $notes);
    }
}
