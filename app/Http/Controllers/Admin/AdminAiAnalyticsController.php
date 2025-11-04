<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentInfo;
use App\Models\Setting;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAiAnalyticsController extends Controller
{
    public function index()
    {
        $defaultStart = now()->subDays(30)->toDateString();
        $defaultEnd = now()->toDateString();
        return view('admin.ai-analytics', [
            'defaultStart' => $defaultStart,
            'defaultEnd' => $defaultEnd,
        ]);
    }

    public function assist(Request $request, AiChatService $ai)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'scope' => ['nullable', 'in:overview,customer,order,product'],
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $currency = strtoupper(config('stripe.currency', 'usd'));
        $start = isset($data['start_date']) ? (string) $data['start_date'] : now()->subDays(30)->toDateString();
        $end = isset($data['end_date']) ? (string) $data['end_date'] : now()->toDateString();
        $startTs = $start.' 00:00:00';
        $endTs = $end.' 23:59:59';
        $scope = $data['scope'] ?? 'overview';

        $sales = DB::table('payment_infos')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startTs, $endTs])
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(amount),0) as revenue_cents')
            ->first();

        $byStatus = DB::table('payment_infos')
            ->whereBetween('paid_at', [$startTs, $endTs])
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->get();

        $topCustomers = DB::table('orders')
            ->join('payment_infos', 'payment_infos.order_id', '=', 'orders.id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->whereNotNull('payment_infos.paid_at')
            ->whereBetween('payment_infos.paid_at', [$startTs, $endTs])
            ->groupBy('orders.user_id', 'users.name', 'users.email')
            ->select('orders.user_id as id', 'users.name', 'users.email', DB::raw('COUNT(orders.id) as orders'), DB::raw('COALESCE(SUM(payment_infos.amount),0) as revenue_cents'))
            ->orderByDesc('revenue_cents')
            ->limit(10)
            ->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payment_infos', 'payment_infos.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereNotNull('payment_infos.paid_at')
            ->whereBetween('payment_infos.paid_at', [$startTs, $endTs])
            ->groupBy('order_items.product_id', 'products.name')
            ->select('order_items.product_id as id', 'products.name', DB::raw('COALESCE(SUM(order_items.quantity),0) as quantity'), DB::raw('COALESCE(SUM(order_items.unit_price_cents * order_items.quantity),0) as revenue_cents'))
            ->orderByDesc('revenue_cents')
            ->limit(10)
            ->get();

        $contextBlob = [
            'currency' => $currency,
            'date_range' => [ 'start' => $start, 'end' => $end ],
            'scope' => $scope,
            'sales' => [
                'orders' => (int) ($sales->orders ?? 0),
                'revenue_cents' => (int) ($sales->revenue_cents ?? 0),
            ],
            'statuses' => $byStatus->map(fn ($r) => ['status' => (string) $r->status, 'count' => (int) $r->c])->all(),
            'top_customers' => $topCustomers->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'email' => (string) $r->email,
                'orders' => (int) $r->orders,
                'revenue_cents' => (int) $r->revenue_cents,
            ])->all(),
            'top_products' => $topProducts->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'quantity' => (int) $r->quantity,
                'revenue_cents' => (int) $r->revenue_cents,
            ])->all(),
        ];

        if ($scope === 'customer' && !empty($data['customer_id'])) {
            $customerId = (int) $data['customer_id'];
            $cust = DB::table('users')->where('id', $customerId)->first();
            $custAgg = DB::table('orders')
                ->join('payment_infos', 'payment_infos.order_id', '=', 'orders.id')
                ->where('orders.user_id', $customerId)
                ->whereNotNull('payment_infos.paid_at')
                ->whereBetween('payment_infos.paid_at', [$startTs, $endTs])
                ->selectRaw('COUNT(orders.id) as orders, COALESCE(SUM(payment_infos.amount),0) as revenue_cents')
                ->first();
            $custTopProducts = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('payment_infos', 'payment_infos.order_id', '=', 'orders.id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.user_id', $customerId)
                ->whereNotNull('payment_infos.paid_at')
                ->whereBetween('payment_infos.paid_at', [$startTs, $endTs])
                ->groupBy('order_items.product_id', 'products.name')
                ->select('order_items.product_id as id', 'products.name', DB::raw('COALESCE(SUM(order_items.quantity),0) as quantity'), DB::raw('COALESCE(SUM(order_items.unit_price_cents * order_items.quantity),0) as revenue_cents'))
                ->orderByDesc('revenue_cents')
                ->limit(10)
                ->get();
            $contextBlob['customer'] = [
                'id' => $customerId,
                'name' => (string) ($cust->name ?? ''),
                'email' => (string) ($cust->email ?? ''),
                'orders' => (int) ($custAgg->orders ?? 0),
                'revenue_cents' => (int) ($custAgg->revenue_cents ?? 0),
                'top_products' => $custTopProducts->map(fn ($r) => [
                    'id' => (int) $r->id,
                    'name' => (string) $r->name,
                    'quantity' => (int) $r->quantity,
                    'revenue_cents' => (int) $r->revenue_cents,
                ])->all(),
            ];
        }

        if ($scope === 'order' && !empty($data['order_id'])) {
            $orderId = (int) $data['order_id'];
            $order = DB::table('orders')->where('id', $orderId)->first();
            $items = DB::table('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('order_items.order_id', $orderId)
                ->select('order_items.id', 'products.name', 'order_items.quantity', 'order_items.unit_price_cents')
                ->get();
            $payment = DB::table('payment_infos')->where('order_id', $orderId)->first();
            $contextBlob['order'] = [
                'id' => $orderId,
                'status' => (string) ($order->status ?? ''),
                'total_cents' => (int) ($order->total_cents ?? 0),
                'items' => $items->map(fn ($i) => [
                    'id' => (int) $i->id,
                    'name' => (string) $i->name,
                    'quantity' => (int) $i->quantity,
                    'unit_price_cents' => (int) $i->unit_price_cents,
                ])->all(),
                'payment' => $payment ? [
                    'provider' => (string) $payment->provider,
                    'amount' => (int) $payment->amount,
                    'status' => (string) $payment->status,
                    'paid_at' => (string) ($payment->paid_at ?? ''),
                ] : null,
            ];
        }

        if ($scope === 'product' && !empty($data['product_id'])) {
            $productId = (int) $data['product_id'];
            $product = DB::table('products')->where('id', $productId)->first();
            $prodAgg = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('payment_infos', 'payment_infos.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $productId)
                ->whereNotNull('payment_infos.paid_at')
                ->whereBetween('payment_infos.paid_at', [$startTs, $endTs])
                ->selectRaw('COALESCE(SUM(order_items.quantity),0) as quantity, COALESCE(SUM(order_items.unit_price_cents * order_items.quantity),0) as revenue_cents')
                ->first();
            $variantRows = DB::table('order_items')
                ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('payment_infos', 'payment_infos.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $productId)
                ->whereNotNull('payment_infos.paid_at')
                ->whereBetween('payment_infos.paid_at', [$startTs, $endTs])
                ->groupBy('order_items.product_variant_id', 'product_variants.attributes')
                ->select(
                    'order_items.product_variant_id as id',
                    DB::raw('COALESCE(SUM(order_items.quantity),0) as quantity'),
                    DB::raw('COALESCE(SUM(order_items.unit_price_cents * order_items.quantity),0) as revenue_cents'),
                    'product_variants.attributes as attributes'
                )
                ->orderByDesc(DB::raw('COALESCE(SUM(order_items.unit_price_cents * order_items.quantity),0)'))
                ->limit(10)
                ->get();
            $contextBlob['product'] = [
                'id' => $productId,
                'name' => (string) ($product->name ?? ''),
                'slug' => (string) ($product->slug ?? ''),
                'category' => (string) ($product->category ?? ''),
                'price_cents' => (int) ($product->price_cents ?? 0),
                'selling_price_cents' => (int) ($product->selling_price_cents ?? 0),
                'quantity_sold' => (int) ($prodAgg->quantity ?? 0),
                'revenue_cents' => (int) ($prodAgg->revenue_cents ?? 0),
                'top_variants' => $variantRows->map(function ($r) {
                    $attrs = null;
                    try { $attrs = $r->attributes ? json_decode($r->attributes, true) : null; } catch (\Throwable $e) { $attrs = null; }
                    return [
                        'id' => (int) ($r->id ?? 0),
                        'attributes' => $attrs,
                        'quantity' => (int) $r->quantity,
                        'revenue_cents' => (int) $r->revenue_cents,
                    ];
                })->all(),
            ];
        }

        $system = trim(<<<TXT
You are an analytics assistant for the shop admin. Follow strictly:
- Only answer using the JSON context provided.
- Show currency in {$currency} and keep answers concise.
- If asked for unavailable details, say you don't have that data.
TXT);

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => "Here is the context in JSON. Only answer based on this.\n\n".json_encode($contextBlob, JSON_PRETTY_PRINT)],
            ['role' => 'user', 'content' => $data['message']],
        ];

        $reply = $ai->respond($messages, ['provider' => 'ollama']);

        return response()->json(['reply' => $reply, 'provider' => 'ollama']);
    }
}
