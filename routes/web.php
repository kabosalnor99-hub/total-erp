<?php

// المسار الكامل: routes/web.php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PosSessionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Total ERP
|--------------------------------------------------------------------------
*/

// ─── تغيير اللغة ──────────────────────────────────────────────────────
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

// ─── المصادقة (بدون تسجيل دخول) ──────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── الصفحات المحمية (تتطلب تسجيل دخول) ──────────────────────────────
Route::middleware(['auth', 'setlocale'])->group(function () {

    // الرئيسية → لوحة التحكم
    Route::get('/', fn() => redirect()->route('dashboard'));

    // لوحة التحكم
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── إدارة المستخدمين ─────────────────────────────────────────────
    Route::get('/profile',          [UserController::class, 'profile'])->name('profile');
    Route::put('/profile',          [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users',        [UserController::class, 'index'])->name('users.index');
        // ✅ الثابتة أولاً
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    });
    Route::middleware('permission:users.create')->group(function () {
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:users.edit')->group(function () {
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ─── المرحلة 2: المخزون والمنتجات ────────────────────────────────

    Route::middleware('permission:categories.view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    });
    Route::middleware('permission:categories.create')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });
    Route::middleware('permission:categories.edit')->group(function () {
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    });
    Route::middleware('permission:categories.delete')->group(function () {
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware('permission:warehouses.view')->group(function () {
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/warehouses/{warehouse}/movements', [WarehouseController::class, 'movements'])
             ->name('warehouses.movements');
    });
    Route::middleware('permission:warehouses.create')->group(function () {
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    });
    Route::middleware('permission:warehouses.edit')->group(function () {
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    });
    Route::middleware('permission:warehouses.delete')->group(function () {
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // ✅ Products — الثابتة (search, create) قبل المتغيرة ({product})
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products',        [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    });
    Route::middleware('permission:products.create')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products',       [ProductController::class, 'store'])->name('products.store');
    });
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });
    Route::middleware('permission:products.edit')->group(function () {
        Route::get('/products/{product}/edit',    [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}',         [ProductController::class, 'update'])->name('products.update');
        Route::post('/products/{product}/adjust', [ProductController::class, 'adjust'])->name('products.adjust');
    });
    Route::middleware('permission:products.delete')->group(function () {
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // ✅ Stock Movements — الثابتة قبل المتغيرة
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/stock-movements',          [StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('/stock-movements/critical', [StockMovementController::class, 'critical'])->name('stock-movements.critical');
        Route::get('/stock-movements/stagnant', [StockMovementController::class, 'stagnant'])->name('stock-movements.stagnant');
    });
    Route::middleware('permission:stock.create')->group(function () {
        Route::get('/stock-movements/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
        Route::post('/stock-movements',       [StockMovementController::class, 'store'])->name('stock-movements.store');
    });
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/stock-movements/{stockMovement}', [StockMovementController::class, 'show'])->name('stock-movements.show');
    });

    // ─── المرحلة 3: المبيعات والعملاء ────────────────────────────────

    // ✅ Customers — search و create قبل {customer}
    Route::middleware('auth')->group(function () {
        Route::get('/customers',                      [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/search',               [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/customers/create',               [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers',                     [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}',           [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
        Route::get('/customers/{customer}/edit',      [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}',           [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}',        [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // ✅ Invoices — الثابتة قبل المتغيرة
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices',        [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/report', [InvoiceController::class, 'report'])->name('invoices.report');
        Route::get('/invoices/aging',  [InvoiceController::class, 'aging'])->name('invoices.aging');
    });
    Route::middleware('permission:invoices.create')->group(function () {
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices',       [InvoiceController::class, 'store'])->name('invoices.store');
    });
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices/{invoice}',       [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{invoice}/pdf',   [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    });
    Route::middleware('permission:invoices.edit')->group(function () {
        Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    });

    // ✅ Payments — الثابتة قبل المتغيرة
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });
    Route::middleware('permission:payments.create')->group(function () {
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments',       [PaymentController::class, 'store'])->name('payments.store');
    });
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments/{payment}',       [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
    });

    // ─── المرحلة 4: نقطة البيع (POS) ────────────────────────────────

    Route::middleware('auth')->group(function () {
        Route::get('/pos',                               [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/report',                        [PosController::class, 'report'])->name('pos.report');
        Route::get('/pos/products/search',               [PosController::class, 'searchProducts'])->name('pos.products.search');
        Route::get('/pos/products/smart-search',         [PosController::class, 'smartSearch'])->name('pos.products.smart-search');
        Route::get('/pos/products/barcode',              [PosController::class, 'findByBarcode'])->name('pos.products.barcode');
        Route::get('/pos/customers/search',              [PosController::class, 'searchCustomers'])->name('pos.customers.search');
        Route::post('/pos/sale',                         [PosController::class, 'processSale'])->name('pos.sale');
        Route::post('/pos/draft',                        [PosController::class, 'saveDraftInvoice'])->name('pos.draft');
        Route::post('/pos/session/open',                 [PosSessionController::class, 'open'])->name('pos.session.open');
        Route::get('/pos/sessions',                      [PosSessionController::class, 'index'])->name('pos.sessions.index');
        Route::get('/pos/receipt/{transaction}',         [PosController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/reprint/{transaction}',         [PosController::class, 'reprint'])->name('pos.reprint');
        Route::post('/pos/cancel/{transaction}',         [PosController::class, 'cancelTransaction'])->name('pos.cancel');
        Route::get('/pos/sessions/{session}',            [PosSessionController::class, 'show'])->name('pos.sessions.show');
        Route::post('/pos/sessions/{session}/close',     [PosSessionController::class, 'close'])->name('pos.sessions.close');
        Route::post('/pos/sessions/{session}/cash-in',   [PosSessionController::class, 'cashIn'])->name('pos.sessions.cash-in');
        Route::post('/pos/sessions/{session}/cash-out',  [PosSessionController::class, 'cashOut'])->name('pos.sessions.cash-out');
    });

    // ─── المرحلة 5: المحاسبة والمالية ────────────────────────────────

    // ✅ Accounts — الثابتة قبل المتغيرة
    Route::middleware('permission:accounts.view')->group(function () {
        Route::get('/accounts',        [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/search', [AccountController::class, 'search'])->name('accounts.search');
    });
    Route::middleware('permission:accounts.create')->group(function () {
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts',       [AccountController::class, 'store'])->name('accounts.store');
    });
    Route::middleware('permission:accounts.view')->group(function () {
        Route::get('/accounts/{account}/ledger', [AccountController::class, 'ledger'])->name('accounts.ledger');
    });
    Route::middleware('permission:accounts.edit')->group(function () {
        Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{account}',      [AccountController::class, 'update'])->name('accounts.update');
    });
    Route::middleware('permission:accounts.delete')->group(function () {
        Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    });

    // ✅ Journal — الثابتة قبل المتغيرة
    Route::middleware('permission:journal.view')->group(function () {
        Route::get('/journal', [JournalEntryController::class, 'index'])->name('journal.index');
    });
    Route::middleware('permission:journal.create')->group(function () {
        Route::get('/journal/create', [JournalEntryController::class, 'create'])->name('journal.create');
        Route::post('/journal',       [JournalEntryController::class, 'store'])->name('journal.store');
    });
    Route::middleware('permission:journal.view')->group(function () {
        Route::get('/journal/{journalEntry}', [JournalEntryController::class, 'show'])->name('journal.show');
    });
    Route::middleware('permission:journal.post')->group(function () {
        Route::post('/journal/{journalEntry}/post',   [JournalEntryController::class, 'post'])->name('journal.post');
        Route::post('/journal/{journalEntry}/unpost', [JournalEntryController::class, 'unpost'])->name('journal.unpost');
    });
    Route::middleware('permission:journal.delete')->group(function () {
        Route::delete('/journal/{journalEntry}', [JournalEntryController::class, 'destroy'])->name('journal.destroy');
    });

    // ✅ Vouchers — الثابتة قبل المتغيرة
    Route::middleware('permission:vouchers.view')->group(function () {
        Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    });
    Route::middleware('permission:vouchers.create')->group(function () {
        Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
        Route::post('/vouchers',       [VoucherController::class, 'store'])->name('vouchers.store');
    });
    Route::middleware('permission:vouchers.view')->group(function () {
        Route::get('/vouchers/{voucher}',       [VoucherController::class, 'show'])->name('vouchers.show');
        Route::get('/vouchers/{voucher}/print', [VoucherController::class, 'print'])->name('vouchers.print');
    });
    Route::middleware('permission:vouchers.delete')->group(function () {
        Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
    });

    // ── التقارير المالية القديمة (تُبقى للتوافق) ──
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/trial-balance',    [AccountController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('/reports/income-statement', [AccountController::class, 'incomeStatement'])->name('reports.income-statement');
        Route::get('/reports/balance-sheet',    [AccountController::class, 'balanceSheet'])->name('reports.balance-sheet');
    });

    // ─── المرحلة 6: المشتريات والموردين ──────────────────────────────

    // ✅ Suppliers — search و create قبل {supplier}
    Route::middleware('permission:suppliers.view')->group(function () {
        Route::get('/suppliers',                      [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/search',               [SupplierController::class, 'search'])->name('suppliers.search');
    });
    Route::middleware('permission:suppliers.create')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers',       [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('permission:suppliers.view')->group(function () {
        Route::get('/suppliers/{supplier}',           [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('/suppliers/{supplier}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');
        Route::get('/suppliers/{supplier}/edit',      [SupplierController::class, 'edit'])->name('suppliers.edit');
    });
    Route::middleware('permission:suppliers.edit')->group(function () {
        Route::put('/suppliers/{supplier}',      [SupplierController::class, 'update'])->name('suppliers.update');
        Route::post('/suppliers/{supplier}/pay', [SupplierController::class, 'pay'])->name('suppliers.pay');
    });
    Route::middleware('permission:suppliers.delete')->group(function () {
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // ✅ Purchase Requests — الثابتة قبل المتغيرة
    Route::middleware('permission:purchase-requests.view')->group(function () {
        Route::get('/purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
    });
    Route::middleware('permission:purchase-requests.create')->group(function () {
        Route::get('/purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
        Route::post('/purchase-requests',       [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
    });
    Route::middleware('permission:purchase-requests.view')->group(function () {
        Route::get('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
    });
    Route::middleware('permission:purchase-requests.approve')->group(function () {
        Route::post('/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
        Route::post('/purchase-requests/{purchaseRequest}/reject',  [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject');
        // تحويل GET إلى صفحة الطلب بدلاً من إظهار 405
        Route::get('/purchase-requests/{purchaseRequest}/approve', fn($purchaseRequest) => redirect()->route('purchase-requests.show', $purchaseRequest));
        Route::get('/purchase-requests/{purchaseRequest}/reject',  fn($purchaseRequest) => redirect()->route('purchase-requests.show', $purchaseRequest));
    });
    Route::middleware('permission:purchase-requests.delete')->group(function () {
        Route::delete('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])->name('purchase-requests.destroy');
    });

    // ✅ Purchase Orders — الثابتة قبل المتغيرة
    Route::middleware('permission:purchase-orders.view')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    });
    Route::middleware('permission:purchase-orders.create')->group(function () {
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders',       [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    });
    Route::middleware('permission:purchase-orders.view')->group(function () {
        Route::get('/purchase-orders/{purchaseOrder}',     [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'pdf'])->name('purchase-orders.pdf');
    });
    Route::middleware('permission:purchase-orders.edit')->group(function () {
        Route::post('/purchase-orders/{purchaseOrder}/mark-sent', [PurchaseOrderController::class, 'markSent'])->name('purchase-orders.mark-sent');
        Route::post('/purchase-orders/{purchaseOrder}/cancel',    [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });
    Route::middleware('permission:purchase-orders.delete')->group(function () {
        Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
    });

    // ✅ Goods Receipts — الثابتة قبل المتغيرة
    Route::middleware('permission:goods-receipts.view')->group(function () {
        Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    });
    Route::middleware('permission:goods-receipts.create')->group(function () {
        Route::get('/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
        Route::post('/goods-receipts',       [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    });
    Route::middleware('permission:goods-receipts.view')->group(function () {
        Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
    });
    Route::middleware('permission:goods-receipts.confirm')->group(function () {
        Route::post('/goods-receipts/{goodsReceipt}/confirm', [GoodsReceiptController::class, 'confirm'])->name('goods-receipts.confirm');
    });
    Route::middleware('permission:goods-receipts.delete')->group(function () {
        Route::delete('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'destroy'])->name('goods-receipts.destroy');
    });

    // ─── المرحلة 7: الموارد البشرية والرواتب ─────────────────────────

    // ✅ Employees — search و create قبل {employee}
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees',        [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');
    });
    Route::middleware('permission:employees.create')->group(function () {
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees',       [EmployeeController::class, 'store'])->name('employees.store');
    });
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    });
    Route::middleware('permission:employees.edit')->group(function () {
        Route::get('/employees/{employee}/edit',       [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}',            [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('/employees/{employee}/salary',    [EmployeeController::class, 'storeSalary'])->name('employees.salary.store');
        Route::post('/employees/{employee}/terminate', [EmployeeController::class, 'terminate'])->name('employees.terminate');
    });
    Route::middleware('permission:employees.delete')->group(function () {
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // ✅ Payroll — الثابتة قبل المتغيرة
    Route::middleware('permission:payroll.view')->group(function () {
        Route::get('/payroll',        [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/report', [PayrollController::class, 'report'])->name('payroll.report');
    });
    Route::middleware('permission:payroll.create')->group(function () {
        Route::get('/payroll/generate',  [PayrollController::class, 'generateForm'])->name('payroll.generate');
        Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate.store');
    });
    Route::middleware('permission:payroll.edit')->group(function () {
        Route::post('/payroll/approve-batch', [PayrollController::class, 'approveBatch'])->name('payroll.approve-batch');
    });
    Route::middleware('permission:payroll.view')->group(function () {
        Route::get('/payroll/{payroll}',     [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('/payroll/{payroll}/pdf', [PayrollController::class, 'pdf'])->name('payroll.pdf');
    });
    Route::middleware('permission:payroll.edit')->group(function () {
        Route::put('/payroll/{payroll}',          [PayrollController::class, 'update'])->name('payroll.update');
        Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
        Route::post('/payroll/{payroll}/pay',     [PayrollController::class, 'markPaid'])->name('payroll.pay');
    });
    Route::middleware('permission:payroll.delete')->group(function () {
        Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
    });

    // ✅ Leaves — الثابتة قبل المتغيرة
    Route::middleware('permission:leaves.view')->group(function () {
        Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    });
    Route::middleware('permission:leaves.create')->group(function () {
        Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
        Route::post('/leaves',       [LeaveController::class, 'store'])->name('leaves.store');
    });
    Route::middleware('permission:leaves.view')->group(function () {
        Route::get('/leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
    });
    Route::middleware('permission:leaves.approve')->group(function () {
        Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject',  [LeaveController::class, 'reject'])->name('leaves.reject');
    });
    Route::middleware('permission:leaves.delete')->group(function () {
        Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy'])->name('leaves.destroy');
    });

    // ✅ Attendance — الثابتة قبل المتغيرة
    Route::middleware('permission:attendance.view')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    });
    Route::middleware('permission:attendance.create')->group(function () {
        Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance',       [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/bulk',  [AttendanceController::class, 'bulkStore'])->name('attendance.bulk');
    });
    Route::middleware('permission:attendance.view')->group(function () {
        Route::get('/attendance/employee/{employee}', [AttendanceController::class, 'employeeReport'])->name('attendance.employee-report');
        Route::get('/attendance/{attendance}',        [AttendanceController::class, 'show'])->name('attendance.show');
    });
    Route::middleware('permission:attendance.edit')->group(function () {
        Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/attendance/{attendance}',      [AttendanceController::class, 'update'])->name('attendance.update');
    });
    Route::middleware('permission:attendance.delete')->group(function () {
        Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    });

    // ─── المرحلة 8: الإعدادات والتقارير والإشعارات ───────────────────

    // ── الإعدادات ──
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',              [SettingController::class, 'index'])->name('index');
        Route::patch('/{key}',       [SettingController::class, 'update'])->name('update');
        Route::patch('/group/{group}', [SettingController::class, 'updateGroup'])->name('update-group');
        Route::post('/backup',       [SettingController::class, 'backup'])->name('backup');
        Route::post('/clear-cache',  [SettingController::class, 'clearCache'])->name('clear-cache');
        Route::get('/api/public',    [SettingController::class, 'publicSettings'])->name('api.public');
    });

    // ── التقارير ──
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::get('/trial-balance',    [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/balance-sheet',    [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/general-ledger',   [ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('/cash-flow',        [ReportController::class, 'cashFlow'])->name('cash-flow');

        Route::get('/sales/summary',     [ReportController::class, 'salesSummary'])->name('sales-summary');
        Route::get('/sales/by-customer', [ReportController::class, 'salesByCustomer'])->name('sales-by-customer');
        Route::get('/sales/by-product',  [ReportController::class, 'salesByProduct'])->name('sales-by-product');
        Route::get('/sales/overdue',     [ReportController::class, 'overdueInvoices'])->name('overdue-invoices');

        Route::get('/inventory/stock',       [ReportController::class, 'stockStatus'])->name('stock-status');
        Route::get('/inventory/low-stock',   [ReportController::class, 'lowStockReport'])->name('low-stock');
        Route::get('/inventory/movements',   [ReportController::class, 'stockMovements'])->name('stock-movements');
        Route::get('/inventory/slow-moving', [ReportController::class, 'slowMovingProducts'])->name('slow-moving');

        Route::get('/hr/payroll',    [ReportController::class, 'payrollSummary'])->name('payroll-summary');
        Route::get('/hr/leave',      [ReportController::class, 'leaveReport'])->name('leave');
        Route::get('/hr/attendance', [ReportController::class, 'attendanceReport'])->name('attendance');

        Route::get('/purchases/summary',            [ReportController::class, 'purchaseSummary'])->name('purchase-summary');
        Route::get('/purchases/supplier-statement', [ReportController::class, 'supplierStatement'])->name('supplier-statement');

        Route::get('/export/pdf/{reportType}',   [ReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export/excel/{reportType}', [ReportController::class, 'exportExcel'])->name('export-excel');
    });

    // ── الإشعارات ──
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',               [NotificationController::class, 'index'])->name('index');
        Route::get('/api/recent',     [NotificationController::class, 'recent'])->name('api.recent');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/clear-read',    [NotificationController::class, 'clearRead'])->name('clear-read');
        Route::post('/{id}/read',     [NotificationController::class, 'markRead'])->name('mark-read');
        Route::delete('/{id}',        [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ─── أسعار الصرف (USD / SDG) ──────────────────────────────────────
    Route::middleware('permission:settings.edit')->group(function () {
        Route::get('/exchange-rates',                    [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
        Route::post('/exchange-rates',                   [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
        Route::post('/exchange-rates/{id}/activate',    [ExchangeRateController::class, 'activate'])->name('exchange-rates.activate');
        Route::delete('/exchange-rates/{id}',           [ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');
    });

    // API عام لجلب السعر الحالي من JavaScript
    Route::get('/api/exchange-rate/current', [ExchangeRateController::class, 'current'])->name('exchange-rates.current');


    // ─── الذكاء الاصطناعي ──────────────────────────────────────────────
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/ask',              [AiController::class, 'ask'])->name('ask');
        Route::post('/chat',             [AiController::class, 'chat'])->name('chat');
        Route::get('/inventory-alert',   [AiController::class, 'inventoryAlert'])->name('inventory.alert');
        Route::get('/sales-insight',     [AiController::class, 'salesInsight'])->name('sales.insight');
        Route::get('/purchase-forecast', [AiController::class, 'purchaseForecast'])->name('purchase.forecast');
    });

}); // نهاية middleware(['auth', 'setlocale'])
