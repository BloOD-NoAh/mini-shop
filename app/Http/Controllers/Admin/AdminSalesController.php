<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSalesController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'succeeded');
        $group = (string) $request->query('group', 'day'); // day|week|month
        $from = $request->query('from');
        $to = $request->query('to');

        $q = DB::table('payment_infos')->whereNotNull('paid_at');
        if ($status && $status !== 'all') {
            $q->where('status', $status);
        }
        if ($from) {
            $q->whereDate('paid_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('paid_at', '<=', $to);
        }

        if ($group === 'day') {
            $rows = $q->selectRaw('DATE(paid_at) as d, COUNT(*) as orders, SUM(amount) as revenue_cents')
                ->groupBy(DB::raw('DATE(paid_at)'))
                ->orderBy('d', 'desc')
                ->limit(30)
                ->get();
        } else {
            $raw = $q->select('paid_at', 'amount')->orderBy('paid_at', 'desc')->get();
            $buckets = [];
            foreach ($raw as $r) {
                $dt = \Illuminate\Support\Carbon::parse($r->paid_at);
                if ($group === 'week') {
                    $key = $dt->isoWeekYear.'-W'.str_pad((string) $dt->isoWeek, 2, '0', STR_PAD_LEFT);
                } else { // month
                    $key = $dt->format('Y-m');
                }
                if (!isset($buckets[$key])) {
                    $buckets[$key] = ['d' => $key, 'orders' => 0, 'revenue_cents' => 0];
                }
                $buckets[$key]['orders'] += 1;
                $buckets[$key]['revenue_cents'] += (int) $r->amount;
            }
            // sort descending by key
            krsort($buckets, SORT_STRING);
            $rows = collect(array_values(array_slice($buckets, 0, 12)));
        }

        $allStatuses = DB::table('payment_infos')->select('status')->distinct()->pluck('status');
        $max = max(1, (int) ($rows->max('revenue_cents') ?? 1));

        $totals = $q->clone()->selectRaw('COUNT(*) as orders, SUM(amount) as revenue_cents')->first();

        // Prepare chart series
        $rowsAsc = $rows->reverse()->values();
        $labels = $rowsAsc->pluck('d')->values();
        $seriesRevenue = $rowsAsc->pluck('revenue_cents')->map(fn ($v) => round(((int) $v) / 100, 2))->values();

        $seriesByStatus = null;
        if ($request->boolean('stack')) {
            $qSeries = DB::table('payment_infos')->whereNotNull('paid_at');
            if ($from) {
                $qSeries->whereDate('paid_at', '>=', $from);
            }
            if ($to) {
                $qSeries->whereDate('paid_at', '<=', $to);
            }

            if ($group === 'day') {
                $rowsStatus = $qSeries->selectRaw('DATE(paid_at) as d, status, SUM(amount) as revenue_cents')
                    ->groupBy(DB::raw('DATE(paid_at)'), 'status')
                    ->orderBy('d')
                    ->get();
            } else {
                $raw = $qSeries->select('paid_at', 'status', 'amount')->orderBy('paid_at')->get();
                $tmp = [];
                foreach ($raw as $r) {
                    $dt = \Illuminate\Support\Carbon::parse($r->paid_at);
                    if ($group === 'week') {
                        $key = $dt->isoWeekYear.'-W'.str_pad((string) $dt->isoWeek, 2, '0', STR_PAD_LEFT);
                    } else {
                        $key = $dt->format('Y-m');
                    }
                    $st = (string) $r->status;
                    $tmp[$key][$st] = ($tmp[$key][$st] ?? 0) + (int) $r->amount;
                }
                $rowsStatus = collect();
                foreach ($tmp as $d => $map) {
                    foreach ($map as $st => $sum) {
                        $rowsStatus->push((object) ['d' => $d, 'status' => $st, 'revenue_cents' => $sum]);
                    }
                }
            }

            $statusKeys = $rowsStatus->pluck('status')->unique()->values();
            $labelIndex = array_flip($labels->all());
            $seriesByStatus = [];
            foreach ($statusKeys as $st) {
                $seriesByStatus[$st] = array_fill(0, count($labels), 0);
            }
            foreach ($rowsStatus as $r) {
                $i = $labelIndex[$r->d] ?? null;
                if ($i === null) continue;
                $st = (string) $r->status;
                if (!array_key_exists($st, $seriesByStatus)) continue;
                $seriesByStatus[$st][$i] = round(((int) $r->revenue_cents) / 100, 2);
            }
        }

        return view('admin.sales', [
            'rows' => $rows,
            'allStatuses' => $allStatuses,
            'filters' => [
                'status' => $status,
                'group' => $group,
                'from' => $from,
                'to' => $to,
                'stack' => $request->boolean('stack'),
            ],
            'maxRevenue' => $max,
            'totals' => $totals,
            'chart' => [
                'labels' => $labels,
                'revenue' => $seriesRevenue,
                'byStatus' => $seriesByStatus,
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $status = (string) $request->query('status', 'succeeded');
        $group = (string) $request->query('group', 'day');
        $from = $request->query('from');
        $to = $request->query('to');

        $q = DB::table('payment_infos')->whereNotNull('paid_at');
        if ($status && $status !== 'all') {
            $q->where('status', $status);
        }
        if ($from) {
            $q->whereDate('paid_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('paid_at', '<=', $to);
        }

        if ($group === 'day') {
            $rows = $q->selectRaw('DATE(paid_at) as d, COUNT(*) as orders, SUM(amount) as revenue_cents')
                ->groupBy(DB::raw('DATE(paid_at)'))
                ->orderBy('d', 'desc')
                ->get();
        } else {
            $raw = $q->select('paid_at', 'amount')->orderBy('paid_at', 'desc')->get();
            $buckets = [];
            foreach ($raw as $r) {
                $dt = \Illuminate\Support\Carbon::parse($r->paid_at);
                if ($group === 'week') {
                    $key = $dt->isoWeekYear.'-W'.str_pad((string) $dt->isoWeek, 2, '0', STR_PAD_LEFT);
                } else { // month
                    $key = $dt->format('Y-m');
                }
                if (!isset($buckets[$key])) {
                    $buckets[$key] = ['d' => $key, 'orders' => 0, 'revenue_cents' => 0];
                }
                $buckets[$key]['orders'] += 1;
                $buckets[$key]['revenue_cents'] += (int) $r->amount;
            }
            krsort($buckets, SORT_STRING);
            $rows = collect(array_values($buckets));
        }

        $filename = 'sales_report_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['date', 'orders', 'revenue_cents', 'revenue']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->d,
                    (int) $r->orders,
                    (int) $r->revenue_cents,
                    number_format(((int) $r->revenue_cents) / 100, 2),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
