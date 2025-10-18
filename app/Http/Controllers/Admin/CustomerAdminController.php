<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q')) ?: null;

        $query = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->withSum('orders', 'total_cents')
            ->latest('id');

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($customers);
        }

        return view('admin.customers', [
            'customers' => $customers,
            'q' => $q,
        ]);
    }

    public function show(User $user)
    {
        abort_if($user->is_admin, 404);

        $user->load(['addresses' => function ($q) {
            $q->orderByDesc('is_default')->orderBy('id');
        }]);

        $orders = $user->orders()
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totals = [
            'orders_count' => (int) $user->orders()->count(),
            'orders_sum_total_cents' => (int) $user->orders()->sum('total_cents'),
        ];

        if (request()->wantsJson()) {
            return response()->json([
                'user' => $user,
                'orders' => $orders,
                'totals' => $totals,
            ]);
        }

        return view('admin.customers-show', [
            'customer' => $user,
            'orders' => $orders,
            'totals' => $totals,
        ]);
    }

    public function export(Request $request)
    {
        $q = trim((string) $request->input('q')) ?: null;
        $query = User::query()->where('is_admin', false);
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%");
            });
        }
        $rows = $query->get();

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['id','name','email','orders_count','total_spent_cents','joined']);
        foreach ($rows as $u) {
            $count = (int) $u->orders()->count();
            $sum = (int) $u->orders()->sum('total_cents');
            fputcsv($csv, [$u->id, $u->name, $u->email, $count, $sum, optional($u->created_at)->format('Y-m-d H:i:s')]);
        }
        rewind($csv);
        $content = stream_get_contents($csv) ?: '';
        fclose($csv);
        $filename = 'customers-'.now()->format('Ymd_His').'.csv';
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportOrders(User $user)
    {
        abort_if($user->is_admin, 404);
        $orders = $user->orders()->withCount('items')->orderBy('id')->get();
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['order_id','status','items_count','total_cents','created_at']);
        foreach ($orders as $o) {
            fputcsv($csv, [$o->id, $o->status, (int) $o->items_count, (int) $o->total_cents, optional($o->created_at)->format('Y-m-d H:i:s')]);
        }
        rewind($csv);
        $content = stream_get_contents($csv) ?: '';
        fclose($csv);
        $filename = 'customer-'.$user->id.'-orders-'.now()->format('Ymd_His').'.csv';
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
