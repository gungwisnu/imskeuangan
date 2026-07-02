@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- ── Header Row ─────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink-black">
            Halo, <span class="font-serif-italic text-electric-blue">{{ Auth::user()->name }}</span> 👋
        </h1>
        <p class="text-steel text-sm mt-1">Ringkasan keuangan Anda.</p>
    </div>

    {{-- Period Tabs --}}
    <div class="flex items-center gap-1 bg-parchment rounded-pill p-1">
        @foreach(['today' => 'Hari Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini', 'all' => 'Semua'] as $key => $label)
            <a href="{{ route('dashboard', ['period' => $key]) }}"
               class="period-tab {{ $period === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

{{-- ── Summary Cards ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">

    {{-- Balance --}}
    <div class="card" style="background: #1b1d20;">
        <div class="flex items-center justify-between mb-5">
            <div class="w-10 h-10 rounded-xl bg-electric-blue/20 flex items-center justify-center">
                <i class="fa-solid fa-wallet text-electric-blue"></i>
            </div>
            <span class="text-xs font-medium text-fog bg-white/10 px-3 py-1 rounded-pill">Sepanjang Waktu</span>
        </div>
        <p class="text-sm text-fog mb-1">Saldo Total</p>
        <p class="text-2xl font-bold tracking-tight {{ $allTimeBalance >= 0 ? 'text-white' : 'text-ember-orange' }}">
            Rp {{ number_format($allTimeBalance, 0, ',', '.') }}
        </p>
    </div>

    {{-- Income --}}
    <div class="card-soft">
        <div class="flex items-center justify-between mb-5">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-parker">
                <i class="fa-solid fa-arrow-trend-up text-[#2e7d32]"></i>
            </div>
            <span class="text-xs font-medium text-ash bg-white px-3 py-1 rounded-pill shadow-parker">
                {{ ['today'=>'Hari Ini','month'=>'Bulan Ini','year'=>'Tahun Ini','all'=>'Semua'][$period] }}
            </span>
        </div>
        <p class="text-sm text-ash mb-1">Pemasukan</p>
        <p class="text-2xl font-bold tracking-tight text-ink-black">
            Rp {{ number_format($periodIncome, 0, ',', '.') }}
        </p>
    </div>

    {{-- Expense --}}
    <div class="card-soft">
        <div class="flex items-center justify-between mb-5">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-parker">
                <i class="fa-solid fa-arrow-trend-down text-ember-orange"></i>
            </div>
            <span class="text-xs font-medium text-ash bg-white px-3 py-1 rounded-pill shadow-parker">
                {{ ['today'=>'Hari Ini','month'=>'Bulan Ini','year'=>'Tahun Ini','all'=>'Semua'][$period] }}
            </span>
        </div>
        <p class="text-sm text-ash mb-1">Pengeluaran</p>
        <p class="text-2xl font-bold tracking-tight text-ink-black">
            Rp {{ number_format($periodExpense, 0, ',', '.') }}
        </p>
    </div>
</div>

{{-- ── Trend Chart ──────────────────────────────────────────────────────── --}}
<div class="card mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-ink-black">Tren Keuangan</h2>
            <p class="text-xs text-steel mt-0.5">
                @if($period === 'year') Bulanan — {{ now()->year }}
                @elseif($period === 'all') 12 Bulan Terakhir
                @else Harian — {{ now()->format('F Y') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-4 text-xs text-steel">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-electric-blue inline-block"></span>Pemasukan
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-ember-orange inline-block"></span>Pengeluaran
            </span>
        </div>
    </div>
    <div style="height: 240px;">
        <canvas id="trendChart"></canvas>
    </div>
</div>

{{-- ── Donut Charts Row ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Expense Donut --}}
    <div class="card">
        <h2 class="text-base font-semibold text-ink-black mb-0.5">Pengeluaran per Kategori</h2>
        <p class="text-xs text-steel mb-5">
            {{ ['today'=>'Hari Ini','month'=>'Bulan Ini','year'=>'Tahun Ini','all'=>'Semua Waktu'][$period] }}
        </p>

        @if($expenseByCategory->isEmpty())
            <div class="flex flex-col items-center justify-center h-40 text-fog">
                <i class="fa-solid fa-chart-pie text-3xl mb-2 opacity-30"></i>
                <p class="text-sm">Belum ada data pengeluaran.</p>
            </div>
        @else
            <div class="flex items-center gap-6">
                <div style="width:160px; height:160px; flex-shrink:0;">
                    <canvas id="expenseDonut"></canvas>
                </div>
                <ul class="flex-1 space-y-2 text-sm">
                    @foreach($expenseByCategory->take(6) as $i => $item)
                        @php $colors = ['#5196fe','#f9754e','#fbbf24','#34d399','#a78bfa','#f43f5e']; @endphp
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-ink-black">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                      style="background:{{ $colors[$i % 6] }}"></span>
                                {{ $item->category_name }}
                            </span>
                            <span class="text-steel font-medium text-xs">
                                Rp {{ number_format($item->total_amount, 0, ',', '.') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Income Donut --}}
    <div class="card">
        <h2 class="text-base font-semibold text-ink-black mb-0.5">Pemasukan per Sumber</h2>
        <p class="text-xs text-steel mb-5">
            {{ ['today'=>'Hari Ini','month'=>'Bulan Ini','year'=>'Tahun Ini','all'=>'Semua Waktu'][$period] }}
        </p>

        @if($incomeByCategory->isEmpty())
            <div class="flex flex-col items-center justify-center h-40 text-fog">
                <i class="fa-solid fa-sack-dollar text-3xl mb-2 opacity-30"></i>
                <p class="text-sm">Belum ada data pemasukan.</p>
            </div>
        @else
            <div class="flex items-center gap-6">
                <div style="width:160px; height:160px; flex-shrink:0;">
                    <canvas id="incomePie"></canvas>
                </div>
                <ul class="flex-1 space-y-2 text-sm">
                    @foreach($incomeByCategory->take(6) as $i => $item)
                        @php $icolors = ['#1b1d20','#5196fe','#f9754e','#34d399','#fbbf24','#a78bfa']; @endphp
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-ink-black">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                      style="background:{{ $icolors[$i % 6] }}"></span>
                                {{ $item->category_name }}
                            </span>
                            <span class="text-steel font-medium text-xs">
                                Rp {{ number_format($item->total_amount, 0, ',', '.') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
const trendLabels  = @json($trendData['labels']);
const incomeData   = @json($trendData['incomeData']);
const expenseData  = @json($trendData['expenseData']);

// ── Trend Bar Chart ──────────────────────────────────────────────────────
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: trendLabels,
        datasets: [
            {
                label: 'Pemasukan',
                data: incomeData,
                backgroundColor: 'rgba(81,150,254,0.85)',
                borderRadius: 6,
            },
            {
                label: 'Pengeluaran',
                data: expenseData,
                backgroundColor: 'rgba(249,117,78,0.85)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1b1d20',
                titleColor: '#fff',
                bodyColor: '#a3a3a3',
                callbacks: { label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID') }
            }
        },
        scales: {
            x: {
                ticks: { color: '#a3a3a3', font: { size: 11 }, maxTicksLimit: 16 },
                grid:  { display: false },
            },
            y: {
                ticks: {
                    color: '#a3a3a3',
                    font: { size: 11 },
                    callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v >= 1000 ? (v/1000).toFixed(0)+'rb' : v)
                },
                grid: { color: '#f2f1ec' },
            }
        }
    }
});

// ── Expense Donut ────────────────────────────────────────────────────────
@if($expenseByCategory->isNotEmpty())
new Chart(document.getElementById('expenseDonut').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: @json($expenseByCategory->pluck('category_name')),
        datasets: [{
            data: @json($expenseByCategory->pluck('total_amount')),
            backgroundColor: ['#5196fe','#f9754e','#fbbf24','#34d399','#a78bfa','#f43f5e'],
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID') } }
        }
    }
});
@endif

// ── Income Pie ───────────────────────────────────────────────────────────
@if($incomeByCategory->isNotEmpty())
new Chart(document.getElementById('incomePie').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: @json($incomeByCategory->pluck('category_name')),
        datasets: [{
            data: @json($incomeByCategory->pluck('total_amount')),
            backgroundColor: ['#1b1d20','#5196fe','#f9754e','#34d399','#fbbf24','#a78bfa'],
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID') } }
        }
    }
});
@endif
</script>
@endsection
