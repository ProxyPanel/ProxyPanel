<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class ReportController extends Controller
{
    public function accounting()
    {
        $orders = Order::where('status', '>=', 2)->whereHas('goods')->latest()->get(['created_at', 'amount']);
        $ordersByDay = $orders->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($row) {
            return $row->sum('amount');
        })->toArray();

        $ordersByMonth = $orders->groupBy(function ($item) {
            return $item->created_at->format('Y-m');
        })->map(function ($row) {
            return $row->sum('amount');
        })->toArray();

        $ordersByYear = $orders->groupBy(function ($item) {
            return $item->created_at->format('Y');
        })->map(function ($row) {
            return $row->sum('amount');
        })->sort()->toArray();

        $currentDays = date('j');
        $lastDays = date('t', strtotime('-1 months'));
        $data['days'] = range(1, $currentDays > $lastDays ? $currentDays : $lastDays);
        $data['years'] = range(1, 12);

        for ($i = 1; $i <= $currentDays; $i++) {
            $data['currentMonth'][] = $ordersByDay[date(sprintf('Y-m-%02u', $i))] ?? 0;
        }

        for ($i = 1; $i <= $lastDays; $i++) {
            $data['lastMonth'][] = $ordersByDay[date(sprintf('Y-m-%02u', $i), strtotime('-1 months'))] ?? 0;
        }

        for ($i = 1; $i <= date('m'); $i++) {
            $data['currentYear'][] = $ordersByMonth[date(sprintf('Y-%02u', $i))] ?? 0;
        }

        for ($i = 1; $i <= 12; $i++) {
            $data['lastYear'][] = $ordersByMonth[date(sprintf('Y-%02u', $i), strtotime('-1 years'))] ?? 0;
        }

        ksort($ordersByYear);
        $data['ordersByYear'] = $ordersByYear;

        return view('admin.report.accounting', compact('data'));
    }
}
