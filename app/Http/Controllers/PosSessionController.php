<?php

// المسار الكامل: app/Http/Controllers/PosSessionController.php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Models\PosTransaction;
use App\Services\PosService;
use Illuminate\Http\Request;

class PosSessionController extends Controller
{
    public function __construct(protected PosService $posService) {}

    /**
     * قائمة الجلسات (للمدير)
     */
    public function index(Request $request)
    {
        $query = PosSession::with('user')->latest('opened_at');

        if ($userId = $request->user_id) {
            $query->where('user_id', $userId);
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($from = $request->from) {
            $query->whereDate('opened_at', '>=', $from);
        }

        if ($to = $request->to) {
            $query->whereDate('opened_at', '<=', $to);
        }

        $sessions = $query->paginate(15)->withQueryString();

        $stats = [
            'today_sales'  => PosSession::whereDate('opened_at', today())->sum('total_sales'),
            'open_sessions'=> PosSession::open()->count(),
            'total_today'  => PosTransaction::completed()->today()->sum('total'),
        ];

        return view('pos.sessions.index', compact('sessions', 'stats'));
    }

    /**
     * فتح جلسة جديدة
     */
    public function open(Request $request)
    {
        $request->validate([
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ], [
            'opening_balance.required' => 'رصيد الافتتاح مطلوب',
            'opening_balance.min'      => 'رصيد الافتتاح لا يمكن أن يكون سالباً',
        ]);

        $session = $this->posService->openSession((float)$request->opening_balance);

        return redirect()->route('pos.index')
            ->with('success', "تم فتح الجلسة بنجاح. رصيد الافتتاح: {$request->opening_balance} ج.س");
    }

    /**
     * إغلاق الجلسة
     */
    public function close(Request $request, PosSession $session)
    {
        // التحقق أن الجلسة للمستخدم نفسه أو مدير
        if ($session->user_id !== auth()->id() && !auth()->user()->hasRole('مدير عام')) {
            abort(403, 'غير مصرح لك بإغلاق هذه الجلسة.');
        }

        $request->validate([
            'closing_balance' => ['required', 'numeric', 'min:0'],
            'closing_notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $this->posService->closeSession(
            $session,
            (float)$request->closing_balance,
            $request->closing_notes
        );

        return redirect()->route('pos.sessions.show', $session)
            ->with('success', 'تم إغلاق الجلسة بنجاح.');
    }

    /**
     * تفاصيل جلسة
     */
    public function show(PosSession $session)
    {
        $session->load(['user', 'transactions.items.product', 'transactions.customer']);

        $summary = [
            'total_sales'       => $session->total_sales,
            'total_cash'        => $session->total_cash,
            'total_credit'      => $session->total_credit,
            'total_discount'    => $session->total_discount,
            'transactions_count'=> $session->transactions_count,
            'expected_balance'  => $session->expected_balance,
            'difference'        => $session->difference,
        ];

        return view('pos.sessions.show', compact('session', 'summary'));
    }

    /**
     * إضافة نقدي للصندوق
     */
    public function cashIn(Request $request, PosSession $session)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->posService->cashIn($session, (float)$request->amount, $request->reason);

        return response()->json([
            'success'          => true,
            'message'          => 'تمت إضافة المبلغ للصندوق',
            'expected_balance' => $session->fresh()->expected_balance,
        ]);
    }

    /**
     * سحب نقدي من الصندوق
     */
    public function cashOut(Request $request, PosSession $session)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->posService->cashOut($session, (float)$request->amount, $request->reason);

        return response()->json([
            'success'          => true,
            'message'          => 'تم سحب المبلغ من الصندوق',
            'expected_balance' => $session->fresh()->expected_balance,
        ]);
    }
}
