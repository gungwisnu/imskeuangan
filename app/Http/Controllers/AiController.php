<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\DeepSeekService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function __construct(protected DeepSeekService $aiService) {}

    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $userMessage  = $request->input('message');
        $categories   = Category::orderBy('type')->orderBy('name')->get();

        // Build user financial context
        $user = Auth::user();
        $thisMonthStart = now()->startOfMonth()->toDateString();
        $thisMonthEnd = now()->endOfMonth()->toDateString();

        // 1. All-time cumulative balance
        $allTimeIncome = Transaction::where('user_id', $user->id)
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->sum('amount');
        $allTimeExpense = Transaction::where('user_id', $user->id)
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->sum('amount');
        $allTimeBalance = $allTimeIncome - $allTimeExpense;

        // 2. Active month summary
        $incomeThisMonth = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$thisMonthStart, $thisMonthEnd])
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->sum('amount');
        $expenseThisMonth = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$thisMonthStart, $thisMonthEnd])
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->sum('amount');
        $balanceThisMonth = $incomeThisMonth - $expenseThisMonth;

        // 3. Spending by category for active month
        $spendingByCategory = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$thisMonthStart, $thisMonthEnd])
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get()
            ->mapWithKeys(fn($item) => [$item->category_name => (float)$item->total_amount])
            ->toArray();

        // 4. Recent transactions list (last 50)
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($t) => [
                'date' => $t->transaction_date ? $t->transaction_date->format('Y-m-d') : null,
                'type' => $t->category->type ?? 'expense',
                'category' => $t->category->name ?? 'Unknown',
                'amount' => (float)$t->amount,
                'description' => $t->description,
            ])
            ->toArray();

        $financialContext = [
            'user_name' => $user->name,
            'all_time_balance' => (float)$allTimeBalance,
            'current_month_summary' => [
                'month' => now()->format('F Y'),
                'total_income' => (float)$incomeThisMonth,
                'total_expense' => (float)$expenseThisMonth,
                'net_savings' => (float)$balanceThisMonth,
            ],
            'spending_by_category_this_month' => $spendingByCategory,
            'recent_transactions' => $recentTransactions,
        ];

        $aiResult     = $this->aiService->chat($userMessage, $categories, $financialContext);

        $action        = $aiResult['action']       ?? 'chat';
        $aiResponse    = $aiResult['response']     ?? 'Maaf, saya tidak mengerti maksud Anda.';
        $transactions  = $aiResult['transactions'] ?? [];

        $loggedCount  = 0;
        $newCategories = [];
        $failedItems  = [];

        if ($action === 'log_transactions' && !empty($transactions)) {
            foreach ($transactions as $item) {
                try {
                    $categoryName  = trim($item['category'] ?? '');
                    $type          = $item['type']          ?? 'expense';
                    $isNewCategory = $item['is_new_category'] ?? false;

                    // ── Resolve or create category ───────────────────────────
                    if ($isNewCategory && $categoryName) {
                        $category = Category::firstOrCreate(
                            ['name' => $categoryName, 'type' => $type],
                        );
                        if ($category->wasRecentlyCreated) {
                            $newCategories[] = $category->name;
                        }
                    } else {
                        $category = Category::where('name', $categoryName)->where('type', $type)->first();
                        if (!$category) {
                            $category = Category::where('name', 'like', '%' . $categoryName . '%')
                                ->where('type', $type)->first();
                        }
                        if (!$category) {
                            $fallback = $type === 'income' ? 'Lainnya (Pemasukan)' : 'Lainnya (Pengeluaran)';
                            $category = Category::where('name', $fallback)->first();
                        }
                    }

                    if ($category) {
                        Transaction::create([
                            'user_id'          => Auth::id(),
                            'category_id'      => $category->id,
                            'amount'           => $item['amount'] ?? 0,
                            'transaction_date' => $item['date'] ?? date('Y-m-d'),
                            'description'      => ($item['description'] ?? null) ?: 'Dicatat otomatis oleh AI',
                        ]);
                        $loggedCount++;
                    } else {
                        $failedItems[] = $item['description'] ?? 'item tidak diketahui';
                    }
                } catch (\Exception $e) {
                    Log::error('AI Auto-Log failed: ' . $e->getMessage() . ' | ' . json_encode($item));
                    $failedItems[] = $item['description'] ?? 'item tidak diketahui';
                }
            }

            if (!empty($newCategories)) {
                $aiResponse .= "\n\n✨ Kategori baru dibuat: " . implode(', ', $newCategories);
            }
            if (!empty($failedItems)) {
                $aiResponse .= "\n\n⚠️ Gagal menyimpan: " . implode(', ', $failedItems);
            }
        }

        // ── Handle delete request ────────────────────────────────────────────
        if ($action === 'delete_transaction') {
            $target = $aiResult['delete_target'] ?? null;
            $deleted = false;

            if ($target) {
                $hint   = trim($target['description_hint'] ?? '');
                $amount = $target['amount'] ?? null;
                $type   = $target['type'] ?? null;
                $date   = $target['date'] ?? null;

                // Build query: must belong to this user, match as many hints as possible
                $query = Transaction::where('user_id', Auth::id())
                    ->with('category')
                    ->orderBy('created_at', 'desc'); // most recent first

                if ($hint) {
                    $query->where('description', 'like', '%' . $hint . '%');
                }
                if ($amount) {
                    $query->where('amount', $amount);
                }
                if ($date) {
                    $query->where('transaction_date', $date);
                }
                if ($type) {
                    $query->whereHas('category', fn($q) => $q->where('type', $type));
                }

                $transaction = $query->first();

                if ($transaction) {
                    $transaction->delete();
                    $deleted = true;
                } else {
                    // Relax: try only description hint if strict search failed
                    if ($hint) {
                        $transaction = Transaction::where('user_id', Auth::id())
                            ->where('description', 'like', '%' . $hint . '%')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($transaction) {
                            $transaction->delete();
                            $deleted = true;
                        }
                    }
                }
            }

            if (!$deleted) {
                $aiResponse = 'Maaf, saya tidak menemukan transaksi yang dimaksud untuk dihapus. Silakan hapus secara manual dari halaman Transaksi.';
            }
        }

        return response()->json([
            'success'            => true,
            'message'            => $aiResponse,
            'action'             => $action,
            'transaction_logged' => $loggedCount > 0,
            'logged_count'       => $loggedCount,
            'new_categories'     => $newCategories,
        ]);
    }
}
