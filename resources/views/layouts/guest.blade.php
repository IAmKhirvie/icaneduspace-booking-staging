<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ICAN Eduspace') }}</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=ican-mark-20260701">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Montserrat:wght@200;300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (app(\App\Services\TurnstileVerifier::class)->enabled())
        <x-turnstile.scripts />
    @endif

    <style>
        *, *::before, *::after { box-sizing:border-box; }
        html, body { width:100%; max-width:100%; overflow-x:hidden; }
        body { font-family:'Montserrat',sans-serif; -webkit-font-smoothing:antialiased; color:#0D1C4C; background:#F5F7FB; }
        .auth-card { background:#fff; border:1px solid rgba(13,28,76,0.08); box-shadow:0 24px 70px rgba(7,17,47,0.10); border-radius:2px; }
        .auth-input,
        input[type='text']:not([class*='bg-']),
        input[type='email']:not([class*='bg-']),
        input[type='password']:not([class*='bg-']),
        input[type='number']:not([class*='bg-']),
        input[type='date']:not([class*='bg-']),
        select:not([class*='bg-']),
        textarea:not([class*='bg-']) {
            background:#fff !important;
            border:1px solid rgba(13,28,76,0.18) !important;
            color:#0D1C4C !important;
            border-radius:0 !important;
            padding:0.7rem 0.9rem;
            width:100%;
        }
        input:focus, select:focus, textarea:focus {
            outline:none !important;
            border-color:#D9A72F !important;
            box-shadow:0 0 0 2px rgba(217,167,47,0.18) !important;
        }
        .password-field .auth-input { padding-right:3.5rem !important; }
        .password-toggle-button {
            width:3rem;
            border:1px solid rgba(13,28,76,0.24) !important;
            border-radius:0;
            background:#FAFBFD;
            color:rgba(13,28,76,0.68);
            box-shadow:inset 0 0 0 1px rgba(255,255,255,0.55);
            transition:background 200ms ease, color 200ms ease;
        }
        .password-toggle-button:hover {
            color:#0D1C4C;
            background:rgba(217,167,47,0.07);
        }
        label { color:rgba(13,28,76,0.55); font-size:0.7rem; letter-spacing:0.18em; text-transform:uppercase; font-weight:500; }
        .btn-gold { background:#D9A72F; color:#07112f; padding:0.85rem 2rem; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; font-size:0.72rem; display:inline-flex; align-items:center; justify-content:center; transition:all 250ms ease; }
        .btn-gold:hover { background:#07112f; color:#D9A72F; }
        .btn-ghost { border:1px solid #0D1C4C; color:#0D1C4C; padding:0.7rem 1.5rem; font-size:0.72rem; letter-spacing:0.18em; text-transform:uppercase; transition:all 250ms ease; }
        .btn-ghost:hover { background:#0D1C4C; color:#fff; }
        a.link-gold, .link-gold { color:#0D1C4C; border-bottom:1px solid #D9A72F; }
        a.link-gold:hover { color:#D9A72F; }
        .eyebrow { color:#D9A72F; letter-spacing:0.3em; text-transform:uppercase; font-size:0.7rem; font-weight:600; }
        .auth-shell { background: radial-gradient(circle at 12% 18%, rgba(217,167,47,0.10), transparent 45%), radial-gradient(circle at 88% 82%, rgba(23,36,93,0.06), transparent 60%), #F5F7FB; min-height:100vh; }
        .auth-side { background:linear-gradient(180deg,#0D1C4C,#07112F); color:#fff; }
        .cf-turnstile { max-width:100%; overflow:hidden; }
        @media (max-width:640px) {
            .auth-shell { min-height:100svh; }
            main { padding-left:1rem !important; padding-right:1rem !important; }
            .auth-card { padding:1.5rem !important; box-shadow:0 16px 45px rgba(7,17,47,0.12); }
            .btn-gold, .btn-ghost { width:100%; min-height:48px; }
        }
    </style>

    @livewireStyles
</head>
@php
    $portal = $portal ?? request('portal');
    $isStaffPortal = in_array($portal, ['admin', 'staff'], true)
        || request()->is('admin/login', 'staff/login')
        || request()->boolean('staff')
        || request()->boolean('admin');
@endphp
<body class="auth-shell">
    <div class="min-h-screen grid lg:grid-cols-2">
        {{-- Brand panel (navy) --}}
        <aside class="auth-side hidden lg:flex flex-col justify-between p-12 {{ $isStaffPortal ? 'lg:order-2' : '' }}">
            <a href="{{ route('home') }}" class="block">
                <div class="eyebrow mb-2">ICAN</div>
                <div class="font-serif text-3xl">Eduspace</div>
            </a>
            <div>
                @if($isStaffPortal)
                    <p class="font-serif text-4xl leading-tight mb-4">Manage the rooms.<br>Guide the booking.</p>
                    <p class="text-white/65 text-sm font-light max-w-sm">Staff and admin access for booking support, room operations, and account management.</p>
                @else
                    <p class="font-serif text-4xl leading-tight mb-4">Book the room.<br>Hold the moment.</p>
                    <p class="text-white/65 text-sm font-light max-w-sm">Classroom and mentoring space, ready for AI workshops, lectures, and small-group programs.</p>
                @endif
            </div>
            <p class="text-xs uppercase tracking-[0.25em] text-white/45">© {{ date('Y') }} ICAN Eduspace</p>
        </aside>

        {{-- Form panel (white) --}}
        <main class="flex flex-col items-center justify-center px-6 py-12 {{ $isStaffPortal ? 'lg:order-1' : '' }}">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
            <div class="mt-10 flex items-center gap-4 text-xs uppercase tracking-[0.25em]">
                <a href="{{ route('home') }}" class="text-brand-navy/55 hover:text-brand-navy">← {{ __('Back to home') }}</a>
                <span class="text-brand-navy/20">·</span>
                <a href="{{ route('locale.switch', 'en') }}" class="@if(app()->getLocale()==='en') text-brand-gold @else text-brand-navy/55 hover:text-brand-navy @endif">EN</a>
                <a href="{{ route('locale.switch', 'ko') }}" class="@if(app()->getLocale()==='ko') text-brand-gold @else text-brand-navy/55 hover:text-brand-navy @endif">KO</a>
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
