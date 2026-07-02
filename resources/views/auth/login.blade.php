<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Fintrac.AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:ital,wght@1,500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: {
                    sans:  ['Inter','ui-sans-serif','system-ui','sans-serif'],
                    serif: ['"Source Serif 4"','ui-serif','Georgia','serif'],
                },
                colors: {
                    'electric-blue': '#5196fe',
                    'ember-orange':  '#f9754e',
                    'ink-black':     '#1b1d20',
                    'parchment':     '#f2f1ec',
                    'sand':          '#e1dfd8',
                    'steel':         '#6e6e6e',
                    'fog':           '#a3a3a3',
                },
                borderRadius: { card: '24px', pill: '9999px', input: '12.8px' },
            }}
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .input-field {
            width: 100%; background: #fff; border: 1.5px solid #a3a3a3;
            border-radius: 12.8px; padding: 12px 16px; font-size: 15px;
            color: #1b1d20; outline: none; transition: border-color 0.15s, box-shadow 0.15s;
        }
        .input-field:focus { border-color: #5196fe; box-shadow: 0 0 0 3px rgba(81,150,254,0.15); }
        .input-field::placeholder { color: #a3a3a3; }
        .btn-primary {
            width: 100%; background: #5196fe; color: #fff; border: none;
            border-radius: 9999px; padding: 13px 24px; font-size: 15px; font-weight: 500;
            cursor: pointer; transition: background 0.15s, transform 0.1s;
        }
        .btn-primary:hover  { background: #3d7fe8; }
        .btn-primary:active { transform: scale(0.98); }
    </style>
</head>
<body class="min-h-screen bg-white flex items-center justify-center px-4">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-electric-blue flex items-center justify-center mb-4 shadow-lg shadow-blue-200">
                <i class="fa-solid fa-wallet text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink-black">
                Selamat <span class="font-serif italic text-electric-blue">datang</span>
            </h1>
            <p class="text-steel text-sm mt-1">Masuk ke akun Fintrac.AI Anda</p>
        </div>

        {{-- Error --}}
        @if($errors->any())
            <div class="bg-[#fef3ee] border border-[#fbc8b0] text-ember-orange rounded-xl px-4 py-3 mb-5 text-sm">
                @foreach($errors->all() as $err)
                    <p class="flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation text-xs"></i>{{ $err }}</p>
                @endforeach
            </div>
        @endif

        {{-- Form --}}
        <div class="bg-white rounded-card border border-sand p-8" style="box-shadow: rgba(0,0,0,0.1) 0px 2px 10px 0px;">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-steel mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="anda@email.com" class="input-field">
                </div>

                <div>
                    <label class="block text-xs font-medium text-steel mb-1.5">Password</label>
                    <input type="password" name="password" required
                           placeholder="••••••••" class="input-field">
                </div>

                <div class="pt-1">
                    <button type="submit" class="btn-primary">
                        Masuk
                    </button>
                </div>
            </form>
        </div>

        {{-- Register Link --}}
        <p class="text-center text-sm text-steel mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-electric-blue font-medium hover:underline">Daftar gratis</a>
        </p>

    </div>

</body>
</html>
