<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Platform-wide high-level metrics
        $totalUsers = User::count();
        $totalTransactions = Transaction::count();

        $globalIncome = Transaction::whereHas('category', function ($query) {
            $query->where('type', 'income');
        })->sum('amount');

        $globalExpense = Transaction::whereHas('category', function ($query) {
            $query->where('type', 'expense');
        })->sum('amount');

        $globalVolume = $globalIncome + $globalExpense;

        // 2. Aggregate metrics per individual user
        $userStats = User::select('users.id', 'users.name', 'users.email')
            ->leftJoin('transactions', 'users.id', '=', 'transactions.user_id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('count(transactions.id) as trx_count')
            ->selectRaw('sum(case when categories.type = "income" then transactions.amount else 0 end) as total_income')
            ->selectRaw('sum(case when categories.type = "expense" then transactions.amount else 0 end) as total_expense')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->get();

        // 3. Global transactions log across all users
        $globalTransactions = Transaction::with(['user', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reports', compact(
            'totalUsers',
            'totalTransactions',
            'globalIncome',
            'globalExpense',
            'globalVolume',
            'userStats',
            'globalTransactions'
        ));
    }
}
