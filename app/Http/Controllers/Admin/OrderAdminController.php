<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PaymentInfo;
use Stripe\Stripe;
use Stripe\Refund as StripeRefund;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $q = trim((string) $request->input('q')) ?: null;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Order::query()
            ->with(['user'])
            ->withCount('items')
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('id', (int) $q)
                  ->orWhereHas('user', function ($wu) use ($q) {
                      $wu->where('name', 'like', "%$q%")
                         ->orWhere('email', 'like', "%$q%");
                  });
            });
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($orders);
        }

        $statuses = ['paid','processing','shipped','canceled','refunded'];
        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => [
                'status' => $status,
                'q' => $q,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'items.variant', 'paymentInfo']);

        if (request()->wantsJson()) {
            return response()->json($order);
        }

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $status = $request->validate([
            'status' => ['required', 'string', 'in:paid,processing,shipped,canceled,refunded'],
        ])['status'];
        $order->status = $status;
        $order->save();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Updated', 'order' => $order]);
        }
        return redirect()->back()->with('status', 'Order status updated');
    }

    public function export(Request $request)
    {
        $status = $request->input('status');
        $q = trim((string) $request->input('q')) ?: null;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Order::query()->with(['user'])->withCount('items')->orderBy('id');
        if ($status) $query->where('status', $status);
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('id', (int) $q)
                  ->orWhereHas('user', function ($wu) use ($q) {
                      $wu->where('name', 'like', "%$q%")
                         ->orWhere('email', 'like', "%$q%");
                  });
            });
        }
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        $rows = $query->get();
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['order_id','user_id','name','email','status','items_count','total_cents','created_at']);
        foreach ($rows as $o) {
            fputcsv($csv, [
                $o->id,
                $o->user?->id,
                $o->user?->name,
                $o->user?->email,
                $o->status,
                (int) $o->items_count,
                (int) $o->total_cents,
                optional($o->created_at)->format('Y-m-d H:i:s'),
            ]);
        }
        rewind($csv);
        $content = stream_get_contents($csv) ?: '';
        fclose($csv);
        $filename = 'orders-'.now()->format('Ymd_His').'.csv';
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'amount' => ['nullable','numeric','min:0'],
        ]);

        $payment = $order->paymentInfo;
        if (! $payment || $payment->provider !== 'stripe' || ! $payment->transaction_id) {
            return redirect()->back()->with('error', 'No refundable Stripe payment found for this order.');
        }

        try {
            Stripe::setApiKey(config('stripe.secret'));
            $payload = [ 'payment_intent' => $payment->transaction_id ];
            if ($request->filled('amount')) {
                $amt = (float) $request->input('amount');
                if ($amt > 0) {
                    $payload['amount'] = (int) round($amt * 100);
                }
            }
            $ref = StripeRefund::create($payload);
            // Mark local records
            $order->status = 'refunded';
            $order->save();
            $payment->status = 'refunded';
            $payment->save();
            return redirect()->back()->with('status', 'Refund processed');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Refund failed: '.$e->getMessage());
        }
    }
}
