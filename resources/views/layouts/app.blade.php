<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fintrac.AI') — Asisten Keuangan Pintar</title>

    <!-- Google Fonts: Inter + Gambetta substitute (Source Serif 4) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=Source+Serif+4:ital,wght@1,500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:     ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        serif:    ['"Source Serif 4"', 'ui-serif', 'Georgia', 'serif'],
                    },
                    colors: {
                        'electric-blue': '#5196fe',
                        'ember-orange':  '#f9754e',
                        'ink-black':     '#1b1d20',
                        'midnight':      '#101828',
                        'parchment':     '#f2f1ec',
                        'sand':          '#e1dfd8',
                        'graphite':      '#27272a',
                        'steel':         '#6e6e6e',
                        'ash':           '#797876',
                        'fog':           '#a3a3a3',
                    },
                    borderRadius: {
                        'card': '24px',
                        'badge': '12.8px',
                        'input': '12.8px',
                        'pill': '12.8px',
                    },
                    boxShadow: {
                        'parker': 'rgba(0, 0, 0, 0.1) 0px 2px 10px 0px',
                    },
                    maxWidth: {
                        'page': '1200px',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        function formatRupiah(value) {
            if (!value) return '';
            let clean = value.toString().replace(/[^0-9]/g, '');
            if (!clean) return '';
            return new Intl.NumberFormat('id-ID').format(parseInt(clean, 10));
        }

        document.addEventListener("DOMContentLoaded", function() {
            const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([href*="logout"])');
            links.forEach(link => {
                if (link.hostname === window.location.hostname) {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');
                        if (!href || href === '#') return;
                        
                        e.preventDefault();
                        const main = document.querySelector('main');
                        if (main) {
                            main.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                            main.style.opacity = '0';
                            main.style.transform = 'translateY(-8px)';
                        }
                        setTimeout(() => {
                            window.location.href = href;
                        }, 200);
                    });
                }
            });
        });
    </script>

    <style>
        /* ── Design System Tokens ─────────────────────────────────── */
        :root {
            --color-electric-blue: #5196fe;
            --color-ember-orange:  #f9754e;
            --color-ink-black:     #1b1d20;
            --color-midnight:      #101828;
            --color-paper-white:   #ffffff;
            --color-parchment:     #f2f1ec;
            --color-sand:          #e1dfd8;
            --color-steel:         #6e6e6e;
            --color-fog:           #a3a3a3;
            --shadow-md:           rgba(0,0,0,0.1) 0px 2px 10px 0px;
            --radius-card:         24px;
            --radius-pill:         9999px;
            --radius-badge:        12.8px;
            --radius-input:        12.8px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background-color: #ffffff;
            color: #1b1d20;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Typography Helpers ──────────────────────────────────── */
        .font-serif-italic {
            font-family: 'Source Serif 4', ui-serif, Georgia, serif;
            font-style: italic;
            font-weight: 500;
        }

        /* ── Buttons ─────────────────────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #5196fe; color: #fff;
            border-radius: 12.8px;
            padding: 12px 24px;
            font-size: 15px; font-weight: 500;
            border: none; cursor: pointer;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
            box-shadow: 0 1px 4px rgba(81,150,254,0.25);
        }
        .btn-primary:hover  { background: #3d7fe8; }
        .btn-primary:active { transform: scale(0.98); }

        .btn-ember {
            display: inline-flex; align-items: center; gap: 8px;
            background: #f9754e; color: #fff;
            border-radius: 12.8px;
            padding: 12px 24px;
            font-size: 15px; font-weight: 500;
            border: none; cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-ember:hover  { background: #e85e38; }
        .btn-ember:active { transform: scale(0.98); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent; color: #1b1d20;
            border-radius: 12.8px;
            padding: 10px 22px;
            font-size: 15px; font-weight: 500;
            border: 1.5px solid #1b1d20; cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-ghost:hover  { background: #f2f1ec; }
        .btn-ghost:active { transform: scale(0.98); }

        /* ── Cards ───────────────────────────────────────────────── */
        .card {
            background: #fff;
            border-radius: 24px;
            padding: 24px;
            box-shadow: rgba(0,0,0,0.1) 0px 2px 10px 0px;
        }
        .card-soft {
            background: #f2f1ec;
            border-radius: 24px;
            padding: 24px;
        }

        /* ── Form Inputs ─────────────────────────────────────────── */
        .input-field {
            width: 100%;
            background: #fff;
            border: 1.5px solid #a3a3a3;
            border-radius: 12.8px;
            padding: 12px 16px;
            font-size: 15px;
            color: #1b1d20;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .input-field:focus {
            border-color: #5196fe;
            box-shadow: 0 0 0 3px rgba(81,150,254,0.15);
        }
        .input-field::placeholder { color: #a3a3a3; }

        /* ── Scrollbar ───────────────────────────────────────────── */
        ::-webkit-scrollbar       { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f2f1ec; }
        ::-webkit-scrollbar-thumb { background: #e1dfd8; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a3a3a3; }

        /* ── Period Tabs ─────────────────────────────────────────── */
        .period-tab {
            padding: 6px 16px;
            border-radius: 12.8px;
            font-size: 13px; font-weight: 500;
            color: #6e6e6e;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .period-tab:hover    { color: #1b1d20; background: #f2f1ec; }
        .period-tab.active   { background: #5196fe; color: #fff; }

        /* ── Page Transitions ────────────────────────────────────── */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-page-in {
            animation: fadeInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ── Badge ───────────────────────────────────────────────── */
        .badge-income  { background: #e8f5e9; color: #2e7d32; border-radius: 12.8px; padding: 4px 10px; font-size: 12px; font-weight: 500; }
        .badge-expense { background: #fef3ee; color: #c2430a; border-radius: 12.8px; padding: 4px 10px; font-size: 12px; font-weight: 500; }
    </style>

    @yield('styles')
</head>

<body class="min-h-screen flex flex-col" x-data>

    <!-- ── Top Navigation ──────────────────────────────────────────────── -->
    <nav class="sticky top-0 z-40 bg-white border-b border-sand">
        <div class="max-w-page mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo -->
                <a href="{{ Auth::check() ? route('dashboard') : '/' }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-electric-blue flex items-center justify-center">
                        <i class="fa-solid fa-wallet text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-semibold tracking-tight text-ink-black">
                        Fintrac<span class="text-electric-blue">.AI</span>
                    </span>
                </a>

                <!-- Nav Links -->
                <div class="hidden md:flex items-center gap-1">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="px-4 py-2 rounded-pill text-sm font-medium transition-all
                                  {{ Route::is('dashboard') ? 'bg-electric-blue text-white' : 'text-steel hover:text-ink-black hover:bg-parchment' }}">
                            <i class="fa-solid fa-chart-pie mr-1.5 text-xs"></i>Dashboard
                        </a>
                        <a href="{{ route('transactions.index') }}"
                           class="px-4 py-2 rounded-pill text-sm font-medium transition-all
                                  {{ Route::is('transactions.*') ? 'bg-electric-blue text-white' : 'text-steel hover:text-ink-black hover:bg-parchment' }}">
                            <i class="fa-solid fa-list-ul mr-1.5 text-xs"></i>Transaksi
                        </a>
                    @endauth
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-3">
                    @auth
                        <!-- AI Trigger -->
                        <button @click="$dispatch('toggle-chat')"
                                class="btn-primary text-sm py-2 px-4 gap-1.5">
                            <i class="fa-solid fa-robot text-xs"></i>
                            <span>Tanya AI</span>
                        </button>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-pill border border-sand hover:bg-parchment transition-all text-sm font-medium text-ink-black">
                                <span class="w-6 h-6 rounded-full bg-electric-blue text-white text-xs flex items-center justify-center font-semibold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-fog"></i>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-52 bg-white rounded-card border border-sand shadow-parker py-1 z-50"
                                 style="display:none;">
                                <div class="px-4 py-3 border-b border-sand">
                                    <p class="text-xs text-fog">Masuk sebagai</p>
                                    <p class="text-sm font-medium text-ink-black truncate mt-0.5">{{ Auth::user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-sm text-ember-orange hover:bg-parchment transition-colors flex items-center gap-2">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost text-sm py-2 px-5">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm py-2 px-5">Daftar</a>
                    @endauth
                </div>

            </div>
        </div>
    </nav>

    <!-- ── Flash Messages ──────────────────────────────────────────────── -->
    @if(session('success'))
        <div class="max-w-page mx-auto w-full px-6 lg:px-8 pt-5"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center justify-between bg-[#e8f5e9] border border-[#a5d6a7] text-[#2e7d32] rounded-badge px-4 py-3 text-sm font-medium">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-[#2e7d32] opacity-60 hover:opacity-100">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-page mx-auto w-full px-6 lg:px-8 pt-5"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)">
            <div class="flex items-center justify-between bg-[#fef3ee] border border-[#fbc8b0] text-ember-orange rounded-badge px-4 py-3 text-sm font-medium">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="opacity-60 hover:opacity-100">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- ── Page Content ────────────────────────────────────────────────── -->
    <main class="flex-grow max-w-page mx-auto w-full px-6 lg:px-8 py-8 animate-page-in">
        @yield('content')
    </main>

    <!-- ── Footer ─────────────────────────────────────────────────────── -->
    <footer class="border-t border-sand py-6 mt-8">
        <div class="max-w-page mx-auto px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-electric-blue flex items-center justify-center">
                    <i class="fa-solid fa-wallet text-white text-[10px]"></i>
                </div>
                <span class="text-sm font-medium text-ink-black">Fintrac.AI</span>
            </div>
            <p class="text-xs text-fog">© 2026 Fintrac.AI · 053.089.098.106.150</p>
        </div>
    </footer>

    <!-- ── AI Chat Drawer ──────────────────────────────────────────────── -->
    @auth
        @include('partials.ai_chat_drawer')
    @endauth

    @yield('scripts')
</body>
</html>
