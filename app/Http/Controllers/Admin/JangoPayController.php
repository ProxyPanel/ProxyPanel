<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Services\JangoPay\Reconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JangoPayController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['order' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'string', 'max:40']]);
        $query = JangoPayAttempt::query();
        if (! empty($filters['order'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_id', $filters['order'])->orWhere('invoice_id', $filters['order'])->orWhere('txn_id', $filters['order']);
            });
        }
        if (! empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        return view('admin.jangopay.index', ['attempts' => $query->latest('id')->paginate(20)->withQueryString(),
            'actions' => DB::table('jangopay_actions')->latest('id')->limit(30)->get()]);
    }

    public function check(Request $request, int $id, Reconciliation $reconciliation)
    {
        JangoPayAttempt::findOrFail($id);
        $hints = $request->validate(['invoice_id' => ['nullable', 'string', 'max:120'],
            'txn_id' => ['nullable', 'string', 'max:120']]);
        $result = $reconciliation->check($id, $hints['txn_id'] ?? null, null, $hints['invoice_id'] ?? null);
        $this->audit($id, 'recheck', $result);

        return redirect()->route('admin.jangopay.index', ['order' => JangoPayAttempt::findOrFail($id)->order_id]);
    }

    public function resolve(Request $request, int $id)
    {
        $data = $request->validate(['note' => ['required', 'string', 'min:10', 'max:1000'],
            'confirm' => ['required', 'accepted']]);
        DB::transaction(function () use ($id, $data) {
            $snapshot = JangoPayAttempt::findOrFail($id);
            $payment = Payment::find($snapshot->payment_id);
            $order = $payment ? Order::whereKey($payment->order_id)->lockForUpdate()->first() : null;
            if ($payment) {
                Payment::whereKey($payment->id)->lockForUpdate()->first();
            }
            $attempt = JangoPayAttempt::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($attempt->state, JangoPayAttempt::RESOLVABLE_STATES, true)
                && (! $order || in_array((int) $order->status, [0, -1], true)), 409);
            if ($order && (int) $order->status === 0) {
                $order->close();
            }
            $attempt->update(['state' => 'resolved', 'review_note' => $data['note']]);
            $this->audit($id, 'externally_resolved', $data['note']);
        });

        return redirect()->route('admin.jangopay.index');
    }

    private function audit(int $id, string $action, string $note): void
    {
        DB::table('jangopay_actions')->insert(['attempt_id' => $id, 'user_id' => auth()->id(),
            'action' => $action, 'note' => $note, 'created_at' => now()]);
    }
}
