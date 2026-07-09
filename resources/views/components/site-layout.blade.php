@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ICAN Eduspace' : 'ICAN Eduspace Booking' }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=ican-mark-20260701">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Montserrat:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (app(\App\Services\TurnstileVerifier::class)->enabled())
        <x-turnstile.scripts />
    @endif
    <style>
        body { background-color:#07112f; color:#fff; font-family:'Montserrat',sans-serif; -webkit-font-smoothing:antialiased; }
        .glass-panel { background:linear-gradient(145deg, rgba(34,59,133,0.40), rgba(13,28,76,0.70)); border:1px solid rgba(217,167,47,0.30); box-shadow:0 24px 70px rgba(7,17,47,0.35); backdrop-filter:blur(6px); }
        .eyebrow { color:#D9A72F; letter-spacing:0.3em; text-transform:uppercase; font-size:0.7rem; }
        .btn-gold { background:#D9A72F; color:#07112f; padding:0.85rem 2rem; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; font-size:0.72rem; display:inline-flex; align-items:center; justify-content:center; transition:background-color 250ms ease, color 250ms ease; }
        .btn-gold:hover { background:#fff; color:#07112f; }
        .btn-ghost { border:1px solid rgba(255,255,255,0.45); color:#fff; padding:0.7rem 1.5rem; font-size:0.72rem; letter-spacing:0.18em; text-transform:uppercase; transition:all 250ms ease; }
        .btn-ghost:hover { background:#fff; color:#07112f; }
        .link-gold { color:#D9A72F; }
        .link-gold:hover { color:#fff; }
        select:not([class*='bg-']), input[type='text']:not([class*='bg-']), input[type='email']:not([class*='bg-']), input[type='password']:not([class*='bg-']), input[type='number']:not([class*='bg-']), input[type='date']:not([class*='bg-']), textarea:not([class*='bg-']) {
            background: rgba(7,17,47,0.55) !important; border:1px solid rgba(217,167,47,0.30) !important; color:#fff !important; border-radius:0 !important; padding:0.65rem 0.85rem; width:100%;
        }
        input:focus, select:focus, textarea:focus { outline:none !important; border-color:rgba(217,167,47,0.85) !important; box-shadow:0 0 0 2px rgba(217,167,47,0.15) !important; }
        .status-badge { display:inline-block; padding:0.25rem 0.7rem; font-size:0.65rem; letter-spacing:0.2em; text-transform:uppercase; border:1px solid; }
        .status-pending { color:#D9A72F; border-color:rgba(217,167,47,0.6); background:rgba(217,167,47,0.08); }
        .status-approved { color:#6ee7b7; border-color:rgba(110,231,183,0.5); background:rgba(16,185,129,0.10); }
        .status-rejected { color:#fca5a5; border-color:rgba(252,165,165,0.5); background:rgba(239,68,68,0.10); }
        .status-cancelled { color:rgba(255,255,255,0.55); border-color:rgba(255,255,255,0.30); background:rgba(255,255,255,0.05); }
        .status-completed { color:#93c5fd; border-color:rgba(147,197,253,0.5); background:rgba(59,130,246,0.10); }
        .top-nav { border-bottom:1px solid rgba(255,255,255,0.10); background:rgba(7,17,47,0.85); backdrop-filter:blur(8px); position:sticky; top:0; z-index:30; }
        .room-card { position:relative; overflow:hidden; min-height:420px; border:1px solid rgba(217,167,47,0.30); transition:transform 250ms ease, border-color 250ms ease, box-shadow 250ms ease; }
        .room-card:hover { transform:translateY(-4px); border-color:rgba(217,167,47,0.85); box-shadow:0 30px 90px rgba(7,17,47,0.4); }
        .room-card .img-bg { position:absolute; inset:0; background-size:cover; background-position:center; transition:transform 600ms ease; }
        .room-card:hover .img-bg { transform:scale(1.06); }
        .room-card .scrim { position:absolute; inset:0; background:linear-gradient(180deg, rgba(7,17,47,0.10) 0%, rgba(7,17,47,0.65) 60%, rgba(7,17,47,0.95) 100%); }
    </style>
    @livewireStyles
</head>
<body>
    <nav class="top-nav">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between gap-6 flex-wrap">
            <a href="{{ route('home') }}" class="flex flex-col leading-tight">
                <span class="eyebrow">ICAN Eduspace</span>
                <span class="font-serif text-xl text-white">{{ __("Booking") }}</span>
            </a>
            <div class="flex items-center gap-6 text-xs uppercase tracking-[0.22em]">
                <a href="{{ route('home') }}" class="text-white/70 hover:text-white">{{ __("Home") }}</a>
                <a href="{{ route('rooms.index') }}" class="text-white/70 hover:text-white">{{ __("Rooms") }}</a>
                <a href="{{ route('home') }}#packages" class="text-white/70 hover:text-white">{{ __("Packages") }}</a>
                <a href="{{ route('home') }}#contact" class="text-white/70 hover:text-white">{{ __("Book") }}</a>
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('locale.switch', 'en') }}" class="@if(app()->getLocale()==='en') text-brand-gold @else text-white/55 hover:text-white @endif">EN</a>
                    <span class="text-white/30">·</span>
                    <a href="{{ route('locale.switch', 'ko') }}" class="@if(app()->getLocale()==='ko') text-brand-gold @else text-white/55 hover:text-white @endif">KO</a>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}" class="link-gold">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost">{{ __('Sign in') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>{{ $slot }}</main>

    <footer class="mt-20 border-t border-white/10 py-8">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-xs text-white/45 tracking-wider gap-3">
            <p>&copy; {{ date('Y') }} ICAN Eduspace. All rights reserved.</p>
            <div class="space-x-6">
                <a href="{{ route('rooms.index') }}" class="hover:text-white">{{ __("Rooms") }}</a>
                <a href="{{ route('login') }}" class="hover:text-white">{{ __("Login") }}</a>
                <a href="/admin" class="hover:text-white">{{ __("Admin") }}</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
