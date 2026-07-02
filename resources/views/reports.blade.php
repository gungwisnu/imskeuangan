@extends('layouts.app')

@section('title', 'Laporan Database Global')

@section('content')
<!-- Header Section -->
<div class="mb-8">
    <h1 class="font-outfit text-4xl font-extrabold tracking-tight text-white">Laporan Database Ekstensif</h1>
    <p class="text-slate-400 mt-1 text-sm">Rekapitulasi performa finansial global dan aktivitas seluruh pengguna sistem.</p>
</div>

<!-- Global Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-5 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pengguna</span>
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                <i class="fa-solid fa-users text-sm"></i>
            </div>
        </div>
        <h3 class="font-outfit text-2xl font-bold text-white">{{ $totalUsers }}</h3>
        <p class="text-[10px] text-slate-500 mt-1">Pengguna terdaftar di platform</p>
    </div>

    <!-- Total Transactions -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-5 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <i class="fa-solid fa-receipt text-sm"></i>
            </div>
        </div>
        <h3 class="font-outfit text-2xl font-bold text-white">{{ $totalTransactions }}</h3>
        <p class="text-[10px] text-slate-500 mt-1">Jumlah transaksi terekam otomatis & manual</p>
    </div>

    <!-- Global Income -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-5 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Income</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                <i class="fa-solid fa-arrow-trend-up text-sm"></i>
            </div>
        </div>
        <h3 class="font-outfit text-2xl font-bold text-emerald-400">Rp {{ number_format($globalIncome, 0, ',', '.') }}</h3>
        <p class="text-[10px] text-slate-500 mt-1">Total pemasukan kumulatif semua pengguna</p>
    </div>

    <!-- Global Expense -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-5 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Expense</span>
            <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-400">
                <i class="fa-solid fa-arrow-trend-down text-sm"></i>
            </div>
        </div>
        <h3 class="font-outfit text-2xl font-bold text-red-400">Rp {{ number_format($globalExpense, 0, ',', '.') }}</h3>
        <p class="text-[10px] text-slate-500 mt-1">Total pengeluaran kumulatif semua pengguna</p>
    </div>
</div>

<!-- Tabs Section or Double Grid -->
<div class="space-y-8">
    <!-- User Agregates Table -->
    <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800 rounded-3xl p-6 shadow-lg">
        <h2 class="font-outfit text-xl font-bold text-white mb-6 flex items-center">
            <i class="fa-solid fa-users-gear mr-2.5 text-blue-500"></i>
            Ringkasan Aktivitas Finansial per Pengguna
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="pb-3">Nama Pengguna</th>
                        <th class="pb-3">Email</th>
                        <th class="pb-3 text-center">Jumlah Transaksi</th>
                        <th class="pb-3 text-right">Total Pemasukan</th>
                        <th class="pb-3 text-right">Total Pengeluaran</th>
                        <th class="pb-3 text-right">Saldo Saat Ini</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-sm">
                    @foreach($userStats as $stat)
                        @php
                            $balance = $stat->total_income - $stat->total_expense;
                        @endphp
                        <tr class="hover:bg-slate-800/20 transition-colors">
                            <td class="py-4 font-semibold text-slate-200">{{ $stat->name }}</td>
                            <td class="py-4 text-slate-400">{{ $stat->email }}</td>
                            <td class="py-4 text-center font-mono text-slate-300">{{ $stat->trx_count }}</td>
                            <td class="py-4 text-right text-emerald-400 font-medium">Rp {{ number_format($stat->total_income, 0, ',', '.') }}</td>
                            <td class="py-4 text-right text-red-400 font-medium">Rp {{ number_format($stat->total_expense, 0, ',', '.') }}</td>
                            <td class="py-4 text-right font-bold {{ $balance >= 0 ? 'text-blue-400' : 'text-rose-500' }}">
                                Rp {{ number_format($balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Global Transaction Logs across all users -->
    <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800 rounded-3xl p-6 shadow-lg">
        <h2 class="font-outfit text-xl font-bold text-white mb-6 flex items-center">
            <i class="fa-solid fa-list-check mr-2.5 text-indigo-500"></i>
            Log Transaksi Global Sistem
        </h2>

        @if($globalTransactions->isEmpty())
            <div class="text-center py-12">
                <p class="text-slate-400">Belum ada transaksi terekam di sistem.</p>
            </div>
        @else
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="pb-3">Nama Pengguna</th>
                            <th class="pb-3">Tanggal</th>
                            <th class="pb-3">Kategori</th>
                            <th class="pb-3">Keterangan</th>
                            <th class="pb-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40 text-sm">
                        @foreach($globalTransactions as $trx)
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="py-4 font-medium text-slate-300">{{ $trx->user->name }}</td>
                                <td class="py-4 text-slate-400">{{ $trx->transaction_date->format('d M Y') }}</td>
                                <td class="py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $trx->category->type === 'income' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                                        {{ $trx->category->name }}
                                    </span>
                                </td>
                                <td class="py-4 text-slate-200">{{ $trx->description ?: '-' }}</td>
                                <td class="py-4 text-right font-bold {{ $trx->category->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $trx->category->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $globalTransactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
