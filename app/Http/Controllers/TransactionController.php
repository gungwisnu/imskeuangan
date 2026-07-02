<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display the transactions CRUD page with filters.
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $period = $request->get('period', 'month');
        $type   = $request->get('type', 'all'); // all | income | expense

        [$startDate, $endDate] = $this->getPeriodRange($period);

        $transactions = Transaction::where('user_id', $user->id)
            ->with('category')
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->when($type === 'income',  fn($q) => $q->whereHas('category', fn($sq) => $sq->where('type', 'income')))
            ->when($type === 'expense', fn($q) => $q->whereHas('category', fn($sq) => $sq->where('type', 'expense')))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('type')->orderBy('name')->get();

        // Period summary for the filter bar
        $periodIncome  = Transaction::where('user_id', $user->id)
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->sum('amount');

        $periodExpense = Transaction::where('user_id', $user->id)
            ->when($startDate, fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->sum('amount');

        return view('transactions.index', compact(
            'transactions', 'categories', 'period', 'type',
            'periodIncome', 'periodExpense'
        ));
    }

    /**
     * Store a manually entered transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'amount'           => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description'      => 'nullable|string|max:255',
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'amount.required'      => 'Jumlah nominal wajib diisi.',
            'amount.numeric'       => 'Jumlah nominal harus berupa angka.',
            'amount.min'           => 'Jumlah nominal harus lebih dari 0.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date'     => 'Format tanggal tidak valid.',
        ]);

        Transaction::create([
            'user_id'          => Auth::id(),
            'category_id'      => $request->category_id,
            'amount'           => $request->amount,
            'transaction_date' => $request->transaction_date,
            'description'      => $request->description,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    /**
     * Delete a transaction (owner-only).
     */
    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Tindakan tidak sah.');
        }
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    // ── Helper ─────────────────────────────────────────────────────────────────

    private function getPeriodRange(string $period): array
    {
        if ($period === 'today') {
            return [now()->toDateString(), now()->toDateString()];
        } elseif ($period === 'week') {
            return [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()];
        } elseif ($period === 'month') {
            return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        } elseif ($period === 'year') {
            return [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()];
        }
        return [null, null];
    }
}
