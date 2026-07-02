@extends('layouts.app')
@section('title', 'Transaksi')

@section('content')
<div x-data="{
    showForm:     {{ $errors->any() ? 'true' : 'false' }},
    showCatModal: false,
    catName:      '',
    catType:      'expense',
    categories:   {{ $categories->toJson() }},

    async createCategory() {
        if (!this.catName.trim()) return;
        const res = await fetch('{{ route('categories.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ name: this.catName.trim(), type: this.catType })
        });
        const data = await res.json();
        if (data.success) {
            this.categories.push({ id: data.category.id, name: data.category.name, type: data.category.type });
            this.$nextTick(() => {
                const sel = document.getElementById('category_id');
                if (sel) sel.value = data.category.id;
            });
            this.catName = '';
            this.showCatModal = false;
        }
    }
}">

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink-black">Transaksi</h1>
        <p class="text-steel text-sm mt-0.5">Catat dan kelola pemasukan serta pengeluaran.</p>
    </div>
    <button @click="showForm = !showForm" class="btn-ember self-start sm:self-auto text-sm">
        <i class="fa-solid fa-plus text-xs"></i>
        Tambah Transaksi
    </button>
</div>

{{-- ── Add Form (slide-down) ───────────────────────────────────────────── --}}
<div x-show="showForm" x-collapse
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="mb-6" style="display:none;">

    <div class="card border border-sand">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-semibold text-ink-black">Tambah Transaksi Manual</h2>
            <button @click="showForm = false" class="text-fog hover:text-ink-black transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        @if($errors->any())
            <div class="bg-[#fef3ee] border border-[#fbc8b0] rounded-input px-4 py-3 mb-5 text-sm text-ember-orange">
                <ul class="space-y-1">
                    @foreach($errors->all() as $err)
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation text-xs"></i>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-medium text-steel mb-1.5">Kategori</label>
                    <div class="flex gap-2">
                        <select name="category_id" id="category_id"
                                class="input-field flex-1 text-sm" required>
                            <option value="">Pilih kategori...</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name + ' (' + (cat.type === 'income' ? '↑' : '↓') + ')'"></option>
                            </template>
                        </select>
                        <button type="button" @click="showCatModal = true"
                                class="btn-ghost text-xs py-2 px-3 whitespace-nowrap" title="Buat kategori baru">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-xs font-medium text-steel mb-1.5">Nominal (Rp)</label>
                    <input type="number" name="amount" step="100" min="1"
                           value="{{ old('amount') }}" placeholder="50000"
                           class="input-field text-sm" required>
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-xs font-medium text-steel mb-1.5">Tanggal</label>
                    <input type="date" name="transaction_date"
                           value="{{ old('transaction_date', date('Y-m-d')) }}"
                           class="input-field text-sm" required>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-medium text-steel mb-1.5">Keterangan (opsional)</label>
                    <input type="text" name="description" placeholder="Makan siang, dll."
                           value="{{ old('description') }}" maxlength="255"
                           class="input-field text-sm">
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="btn-primary text-sm">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>Simpan
                </button>
                <button type="button" @click="showForm = false" class="btn-ghost text-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ── New Category Modal ───────────────────────────────────────────────── --}}
<div x-show="showCatModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
    <div class="absolute inset-0 bg-ink-black/30 backdrop-blur-sm" @click="showCatModal = false"></div>
    <div class="relative bg-white rounded-card shadow-parker w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-ink-black">Buat Kategori Baru</h3>
            <button @click="showCatModal = false" class="text-fog hover:text-ink-black">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-steel mb-1.5">Nama Kategori</label>
                <input type="text" x-model="catName" placeholder="cth. Orang Tua, Bayar Hutang…"
                       @keydown.enter="createCategory()"
                       class="input-field text-sm" maxlength="50">
            </div>
            <div>
                <label class="block text-xs font-medium text-steel mb-2">Jenis</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="catType = 'expense'"
                            :class="catType === 'expense'
                                ? 'bg-ember-orange text-white border-ember-orange'
                                : 'bg-white text-steel border-sand hover:bg-parchment'"
                            class="py-2.5 rounded-pill border-2 text-sm font-medium transition-all">
                        <i class="fa-solid fa-arrow-down mr-1 text-xs"></i>Pengeluaran
                    </button>
                    <button type="button" @click="catType = 'income'"
                            :class="catType === 'income'
                                ? 'bg-electric-blue text-white border-electric-blue'
                                : 'bg-white text-steel border-sand hover:bg-parchment'"
                            class="py-2.5 rounded-pill border-2 text-sm font-medium transition-all">
                        <i class="fa-solid fa-arrow-up mr-1 text-xs"></i>Pemasukan
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button @click="createCategory()" class="btn-primary text-sm flex-1">
                <i class="fa-solid fa-plus text-xs"></i>Buat Kategori
            </button>
            <button @click="showCatModal = false" class="btn-ghost text-sm">Batal</button>
        </div>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    {{-- Type --}}
    <div class="flex items-center gap-1 bg-parchment rounded-pill p-1">
        @foreach(['all' => 'Semua', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran'] as $t => $l)
            <a href="{{ route('transactions.index', ['period' => $period, 'type' => $t]) }}"
               class="period-tab {{ $type === $t ? 'active' : '' }}">{{ $l }}</a>
        @endforeach
    </div>

    {{-- Period --}}
    <div class="flex items-center gap-1 bg-parchment rounded-pill p-1">
        @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini', 'all' => 'Semua'] as $p => $l)
            <a href="{{ route('transactions.index', ['period' => $p, 'type' => $type]) }}"
               class="period-tab {{ $period === $p ? 'active' : '' }}">{{ $l }}</a>
        @endforeach
    </div>
</div>

{{-- ── Mini Summary Bar ─────────────────────────────────────────────────── --}}
<div class="flex items-center gap-6 mb-5 px-1">
    <div class="flex items-center gap-2 text-sm">
        <span class="w-2 h-2 rounded-full bg-electric-blue inline-block"></span>
        <span class="text-steel">Pemasukan:</span>
        <span class="font-semibold text-ink-black">Rp {{ number_format($periodIncome, 0, ',', '.') }}</span>
    </div>
    <div class="w-px h-4 bg-sand"></div>
    <div class="flex items-center gap-2 text-sm">
        <span class="w-2 h-2 rounded-full bg-ember-orange inline-block"></span>
        <span class="text-steel">Pengeluaran:</span>
        <span class="font-semibold text-ink-black">Rp {{ number_format($periodExpense, 0, ',', '.') }}</span>
    </div>
    <div class="w-px h-4 bg-sand"></div>
    <div class="flex items-center gap-2 text-sm">
        @php $balance = $periodIncome - $periodExpense; @endphp
        <span class="text-steel">Selisih:</span>
        <span class="font-semibold {{ $balance >= 0 ? 'text-[#2e7d32]' : 'text-ember-orange' }}">
            {{ $balance >= 0 ? '+' : '' }}Rp {{ number_format($balance, 0, ',', '.') }}
        </span>
    </div>
</div>

{{-- ── Transaction Table ────────────────────────────────────────────────── --}}
<div class="card p-0 overflow-hidden">
    @if($transactions->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-fog">
            <i class="fa-solid fa-receipt text-5xl mb-4 opacity-20"></i>
            <p class="text-base font-medium">Belum ada transaksi di periode ini.</p>
            <p class="text-sm mt-1 opacity-70">Coba ubah filter atau tambah transaksi baru.</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-sand">
                    <th class="text-left text-xs font-medium text-fog px-6 py-3.5">Tanggal</th>
                    <th class="text-left text-xs font-medium text-fog px-3 py-3.5">Kategori</th>
                    <th class="text-left text-xs font-medium text-fog px-3 py-3.5">Keterangan</th>
                    <th class="text-right text-xs font-medium text-fog px-6 py-3.5">Nominal</th>
                    <th class="px-4 py-3.5 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sand">
                @foreach($transactions as $trx)
                    @php $isIncome = $trx->category->type === 'income'; @endphp
                    <tr class="group hover:bg-parchment transition-colors">
                        <td class="px-6 py-4 text-steel text-xs whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($trx->transaction_date)->isoFormat('D MMM YYYY') }}
                        </td>
                        <td class="px-3 py-4">
                            @if($isIncome)
                                <span class="badge-income">
                                    <i class="fa-solid fa-arrow-up text-[10px] mr-1"></i>{{ $trx->category->name }}
                                </span>
                            @else
                                <span class="badge-expense">
                                    <i class="fa-solid fa-arrow-down text-[10px] mr-1"></i>{{ $trx->category->name }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-ink-black">
                            {{ $trx->description ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold whitespace-nowrap
                                   {{ $isIncome ? 'text-electric-blue' : 'text-ember-orange' }}">
                            {{ $isIncome ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            <form method="POST" action="{{ route('transactions.destroy', $trx->id) }}"
                                  onsubmit="return confirm('Hapus transaksi ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-fog hover:text-ember-orange transition-colors opacity-0 group-hover:opacity-100"
                                        title="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-sand flex items-center justify-between">
                <p class="text-xs text-fog">
                    Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
                    dari {{ $transactions->total() }} transaksi
                </p>
                <div class="flex items-center gap-1">
                    @if($transactions->onFirstPage())
                        <span class="px-3 py-1.5 rounded-pill text-xs text-fog border border-sand cursor-not-allowed opacity-40">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </span>
                    @else
                        <a href="{{ $transactions->previousPageUrl() }}"
                           class="px-3 py-1.5 rounded-pill text-xs text-steel border border-sand hover:bg-parchment transition-all">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </a>
                    @endif

                    <span class="px-3 py-1.5 rounded-pill text-xs bg-electric-blue text-white font-medium">
                        {{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}
                    </span>

                    @if($transactions->hasMorePages())
                        <a href="{{ $transactions->nextPageUrl() }}"
                           class="px-3 py-1.5 rounded-pill text-xs text-steel border border-sand hover:bg-parchment transition-all">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded-pill text-xs text-fog border border-sand cursor-not-allowed opacity-40">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>

</div>{{-- end x-data --}}
@endsection
