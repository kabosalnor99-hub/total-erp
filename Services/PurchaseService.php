<?php

// المسار الكامل: app/Services/PurchaseService.php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected AccountingService $accounting
    ) {}

    // ─── إنشاء أمر شراء ─────────────────────────────────────────────

    public function createOrder(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {

            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            }

            $tax   = ($data['tax_rate'] ?? 0) / 100 * $subtotal;
            $total = $subtotal - ($data['discount'] ?? 0) + $tax;

            $order = PurchaseOrder::create([
                'order_number'        => PurchaseOrder::generateNumber(),
                'supplier_id'         => $data['supplier_id'],
                'user_id'             => auth()->id(),
                'purchase_request_id' => $data['purchase_request_id'] ?? null,
                'status'              => 'draft',
                'subtotal'            => $subtotal,
                'discount'            => $data['discount'] ?? 0,
                'tax'                 => $tax,
                'total'               => $total,
                'amount_paid'         => 0,
                'expected_date'       => $data['expected_date'] ?? null,
                'notes'               => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'received_quantity' => 0,
                    'unit_price'        => $item['unit_price'],
                    'discount'          => $item['discount'] ?? 0,
                    'total'             => $lineTotal,
                    'notes'             => $item['notes'] ?? null,
                ]);
            }

            // إذا كان من طلب شراء — تحديث حالته
            if (!empty($data['purchase_request_id'])) {
                \App\Models\PurchaseRequest::where('id', $data['purchase_request_id'])
                    ->update(['status' => 'ordered']);
            }

            return $order;
        });
    }

    // ─── تأكيد استلام البضاعة ────────────────────────────────────────

    /**
     * استلام البضاعة → تحديث المخزون + تحديث الأمر + قيد محاسبي
     */
    public function confirmReceipt(GoodsReceipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {

            if ($receipt->status === 'confirmed') {
                throw new \Exception('هذا الاستلام مؤكد مسبقاً');
            }

            $receipt->load(['items.product', 'purchaseOrder']);
            $order = $receipt->purchaseOrder;

            foreach ($receipt->items as $receiptItem) {
                // 1. تحديث الكمية المستلمة في بند الأمر
                $orderItem = $receiptItem->purchaseOrderItem;
                $orderItem->increment('received_quantity', $receiptItem->quantity_received);

                // 2. تحديث مخزون المنتج
                $product = $receiptItem->product;
                $product->increment('quantity', $receiptItem->quantity_received);

                // 3. تحديث سعر الشراء إذا تغيّر
                if ($receiptItem->unit_price > 0) {
                    $product->update(['purchase_price' => $receiptItem->unit_price]);
                }

                // 4. تسجيل حركة المخزون
                StockMovement::create([
                    'product_id'     => $product->id,
                    'warehouse_id'   => $receipt->warehouse_id,
                    'type'           => 'in',
                    'quantity'       => $receiptItem->quantity_received,
                    'reference_type' => GoodsReceipt::class,
                    'reference_id'   => $receipt->id,
                    'user_id'        => auth()->id() ?? $receipt->user_id,
                ]);
            }

            // 5. تحديث حالة أمر الشراء
            $order->load('items');
            $allReceived = $order->items->every(fn($i) => $i->received_quantity >= $i->quantity);
            $order->update(['status' => $allReceived ? 'received' : 'partial']);

            // 6. تأكيد الاستلام
            $receipt->update(['status' => 'confirmed']);

            // 7. قيد محاسبي تلقائي: من حـ/المخزون → إلى حـ/الذمم الدائنة (موردون)
            $this->accounting->createPurchaseEntry($order, $receipt);
        });
    }

    // ─── تسجيل دفعة للمورد ──────────────────────────────────────────

    public function paySupplier(array $data): SupplierPayment
    {
        return DB::transaction(function () use ($data) {

            $payment = SupplierPayment::create([
                'payment_number'  => SupplierPayment::generateNumber(),
                'supplier_id'     => $data['supplier_id'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'user_id'         => auth()->id(),
                'amount'          => $data['amount'],
                'method'          => $data['method'],
                'reference'       => $data['reference'] ?? null,
                'payment_date'    => $data['payment_date'],
                'notes'           => $data['notes'] ?? null,
            ]);

            // تحديث المبلغ المدفوع في أمر الشراء إن وُجد
            if (!empty($data['purchase_order_id'])) {
                $order = PurchaseOrder::findOrFail($data['purchase_order_id']);
                $order->increment('amount_paid', $data['amount']);
            }

            // تحديث رصيد المورد
            $payment->supplier->decrement('balance', $data['amount']);

            // قيد محاسبي: من حـ/الذمم الدائنة → إلى حـ/الصندوق
            $this->accounting->createSupplierPaymentEntry($payment);

            return $payment;
        });
    }

    // ─── مرتجع مشتريات ──────────────────────────────────────────────

    public function returnItems(PurchaseOrder $order, array $items, int $warehouseId): void
    {
        DB::transaction(function () use ($order, $items, $warehouseId) {

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // خصم من المخزون
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("الكمية المرتجعة تتجاوز المخزون المتاح للمنتج: {$product->name_ar}");
                }

                $product->decrement('quantity', $item['quantity']);

                // تسجيل حركة مخزون سالبة
                StockMovement::create([
                    'product_id'     => $product->id,
                    'warehouse_id'   => $warehouseId,
                    'type'           => 'out',
                    'quantity'       => $item['quantity'],
                    'reference_type' => PurchaseOrder::class,
                    'reference_id'   => $order->id,
                    'user_id'        => auth()->id(),
                ]);
            }

            // قيد عكسي: من حـ/الذمم الدائنة → إلى حـ/المخزون
            $returnTotal = collect($items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $this->accounting->createPurchaseReturnEntry($order, $returnTotal);
        });
    }
}
