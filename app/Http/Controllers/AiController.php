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
        $aiResult     = $this->aiService->chat($userMessage, $categories);

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
                $aiResponse .= "\n\n✨ Kategori baru dibuat: **" . implode('**, **', $newCategories) . "**";
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
