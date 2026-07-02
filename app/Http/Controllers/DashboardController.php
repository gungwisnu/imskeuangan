<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $period = $request->get('period', 'month'); // today | month | year | all

        // ── All-time cumulative balance (never filtered) ──────────────────────
        $allTimeIncome  = Transaction::where('user_id', $user->id)
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->sum('amount');
        $allTimeExpense = Transaction::where('user_id', $user->id)
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->sum('amount');
        $allTimeBalance = $allTimeIncome - $allTimeExpense;

        // ── Period-based income / expense ────────────────────────────────────
        [$startDate, $endDate] = $this->getPeriodRange($period);

        $periodIncome  = Transaction::where('user_id', $user->id)
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->sum('amount');

        $periodExpense = Transaction::where('user_id', $user->id)
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->sum('amount');

        // ── Trend bar chart data ──────────────────────────────────────────────
        $trendData = $this->getTrendData($user->id, $period);

        // ── Expense donut chart (by category, period) ─────────────────────────
        $expenseByCategory = Transaction::where('user_id', $user->id)
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        // ── Income pie chart (by source, period) ─────────────────────────────
        $incomeByCategory = Transaction::where('user_id', $user->id)
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        return view('dashboard', compact(
            'allTimeBalance', 'periodIncome', 'periodExpense',
            'period', 'trendData', 'expenseByCategory', 'incomeByCategory'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getPeriodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'year'  => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default => [null, null], // 'all'
        };
    }

    private function getTrendData(int $userId, string $period): array
    {
        if ($period === 'year') {
            // Monthly breakdown for this year
            $labels      = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $incomeData  = array_fill(0, 12, 0);
            $expenseData = array_fill(0, 12, 0);

            $results = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()])
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->selectRaw('MONTH(transaction_date) as period_key, categories.type, SUM(transactions.amount) as total')
                ->groupBy('period_key', 'categories.type')
                ->get();

            foreach ($results as $r) {
                $idx = $r->period_key - 1;
                if ($r->type === 'income') $incomeData[$idx]  = (float) $r->total;
                else                        $expenseData[$idx] = (float) $r->total;
            }

        } elseif ($period === 'all') {
            // Last 12 months
            $labels      = [];
            $incomeData  = array_fill(0, 12, 0);
            $expenseData = array_fill(0, 12, 0);
            $periodMap   = [];

            for ($i = 11; $i >= 0; $i--) {
                $m           = now()->subMonths($i);
                $labels[]    = $m->format('M \'y');
                $periodMap[$m->format('Y-m')] = 11 - $i;
            }

            $results = Transaction::where('user_id', $userId)
                ->where('transaction_date', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as period_key, categories.type, SUM(transactions.amount) as total")
                ->groupBy('period_key', 'categories.type')
                ->get();

            foreach ($results as $r) {
                if (!isset($periodMap[$r->period_key])) continue;
                $idx = $periodMap[$r->period_key];
                if ($r->type === 'income') $incomeData[$idx]  = (float) $r->total;
                else                        $expenseData[$idx] = (float) $r->total;
            }

        } else {
            // today / month → daily breakdown of the current month
            $daysInMonth = now()->daysInMonth;
            $labels      = array_map('strval', range(1, $daysInMonth));
            $incomeData  = array_fill(0, $daysInMonth, 0);
            $expenseData = array_fill(0, $daysInMonth, 0);

            $results = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->selectRaw('DAY(transaction_date) as period_key, categories.type, SUM(transactions.amount) as total')
                ->groupBy('period_key', 'categories.type')
                ->get();

            foreach ($results as $r) {
                $idx = $r->period_key - 1;
                if ($r->type === 'income') $incomeData[$idx]  = (float) $r->total;
                else                        $expenseData[$idx] = (float) $r->total;
            }
        }

        return compact('labels', 'incomeData', 'expenseData');
    }
}
