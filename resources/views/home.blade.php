<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICAN Eduspace Booking</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=ican-mark-20260701">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Montserrat:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (app(\App\Services\TurnstileVerifier::class)->enabled())
      <x-turnstile.scripts />
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" defer></script>
    <style>
      :root {
        --bg: #07112f;
        --fg: #ffffff;
        --gold: #d9a72f;
        --navy: #0d1c4c;
        --blue: #17245d;
      }

      *, *::before, *::after { box-sizing: border-box; }
      html {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        scroll-behavior: smooth;
      }
      body {
        width: 100%;
        max-width: 100%;
        background-color: var(--bg);
        color: var(--fg);
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
        text-rendering: geometricPrecision;
      }
      img, video, canvas, svg { max-width: 100%; }
      ::-webkit-scrollbar { width: 8px; }
      ::-webkit-scrollbar-track { background: var(--bg); }
      ::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 4px; }
      .text-stroke {
        -webkit-text-stroke: 1px rgba(217, 167, 47, 0.58);
        color: rgba(255, 255, 255, 0.05);
        text-shadow: 0 0 18px rgba(217, 167, 47, 0.12);
        opacity: 0.8;
      }
      .img-container { overflow: hidden; }
      .img-container img { transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
      .img-container:hover img { transform: scale(1.1); }
      .loader {
        position: fixed;
        inset: 0;
        background: var(--bg);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .loader-text {
        font-family: "Cormorant Garamond", serif;
        font-size: clamp(2rem, 5vw, 3rem);
        color: var(--gold);
        opacity: 0;
        transform: translateY(10px);
      }
      .reveal-on-load {
        opacity: 0;
        transform: translateY(14px);
      }
      .text-brand-gold { color: var(--gold) !important; }
      .bg-brand-gold { background-color: var(--gold) !important; }
      .border-brand-gold { border-color: var(--gold) !important; }
      .bg-brand-dark { background-color: var(--bg) !important; }
      .bg-brand-blue { background-color: var(--blue) !important; }
      .bg-brand-gray { background-color: #f5f7fb !important; }
	      .cta-request:hover {
	        color: var(--bg) !important;
	      }
	      .nav-register:hover {
	        color: var(--bg) !important;
	      }
	      .hero-secondary:hover {
	        color: #fff !important;
	      }
      .nav-shell {
        width: 100%;
        max-width: 1500px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
      }
      .desktop-nav,
      .desktop-actions {
        display: none;
      }
      .menu-button {
        display: inline-flex;
      }
      .package-card-blue {
        position: relative;
        overflow: hidden;
        min-height: 380px;
        padding: 2rem;
        color: #fff;
        display: flex;
        flex-direction: column;
        height: 100%;
        background:
          linear-gradient(145deg, rgba(34, 59, 133, 0.92), rgba(13, 28, 76, 0.98)),
          var(--blue);
        border: 1px solid rgba(217, 167, 47, 0.45);
        box-shadow: 0 24px 70px rgba(7, 17, 47, 0.22);
        transition: border-color 250ms ease, transform 250ms ease, box-shadow 250ms ease;
      }
      .package-card-blue:hover {
        border-color: rgba(217, 167, 47, 0.95);
        transform: translateY(-4px);
        box-shadow: 0 30px 90px rgba(7, 17, 47, 0.32);
      }
      .package-card-blue::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: var(--gold);
      }
      .package-card-blue .package-icon {
        position: absolute;
        top: 1rem;
        right: 1rem;
        color: var(--gold);
        opacity: 0.22;
        transition: opacity 250ms ease;
      }
      .package-card-blue:hover .package-icon { opacity: 0.4; }
      .package-card-blue .package-label {
        color: var(--gold);
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.2em;
        text-transform: uppercase;
      }
      .package-card-blue h3 {
        margin: 0.75rem 0 1rem;
        color: #fff;
        min-height: 3.5rem;
        display: flex;
        align-items: flex-start;
      }
      .package-card-blue p,
      .package-card-blue li {
        color: rgba(255, 255, 255, 0.84);
      }
      .package-card-blue p {
        min-height: 5.5rem;
      }
      .package-card-blue ul {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
      }
      :focus-visible {
        outline: 2px solid var(--gold);
        outline-offset: 3px;
      }
      .glass-panel {
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
      }
      .page-section {
        min-height: 100vh;
        display: flex;
        align-items: center;
        scroll-margin-top: 88px;
      }
      .page-section > .section-container {
        width: 100%;
      }
      .showcase-frame {
        position: relative;
        border: 1px solid rgba(217, 167, 47, 0.28);
        background:
          radial-gradient(700px circle at 20% 20%, rgba(217, 167, 47, 0.18), transparent 60%),
          radial-gradient(600px circle at 80% 30%, rgba(255, 255, 255, 0.08), transparent 55%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.07), rgba(7, 17, 47, 0.2));
        overflow: hidden;
      }
      .showcase-frame::before {
        content: "";
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(135deg, rgba(255,255,255,0.06) 0px, rgba(255,255,255,0.06) 1px, transparent 1px, transparent 14px);
        opacity: 0.35;
        pointer-events: none;
      }
      .showcase-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 22px;
      }
      .form-input {
        width: 100%;
        min-height: 48px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        padding: 0 14px;
        border-radius: 2px;
        outline: none;
      }
      .form-input option { color: #07112f; }
      .form-input::placeholder { color: rgba(255, 255, 255, 0.46); }
      textarea.form-input { min-height: 112px; padding: 13px 14px; }
      @media (min-width: 768px) {
        .book-section .form-input { min-height: 42px; }
        .book-section textarea.form-input { min-height: 84px; }
      }
      @media (min-width: 1345px) {
        .nav-shell {
          display: grid;
          grid-template-columns: minmax(150px, 1fr) auto minmax(260px, 1fr);
          gap: 1.5rem;
        }
        .desktop-nav,
        .desktop-actions {
          display: flex;
        }
        .menu-button {
          display: none;
        }
      }
      @media (max-width: 1344px) {
        #navbar {
          padding-left: 1rem;
          padding-right: 1rem;
        }
        .nav-shell,
        #mobileMenu [data-menu-panel] {
          max-width: 100vw;
        }
        .hero-title {
          font-size: clamp(3rem, 13vw, 4.35rem);
        }
        .hero-text {
          max-width: min(22rem, calc(100vw - 2rem));
        }
        .page-section {
          min-height: auto;
        }
        .showcase-frame {
          min-height: auto;
        }
        .text-stroke {
          overflow-wrap: anywhere;
        }
      }
      @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        *, *::before, *::after {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
          scroll-behavior: auto !important;
        }
      }
    </style>
  </head>
  <body class="font-sans">
    <div class="loader" aria-hidden="true">
      <div class="loader-text" data-brand-text>ICAN Eduspace</div>
    </div>

    <nav class="fixed w-full z-50 transition-all duration-300 px-6 py-4" id="navbar" aria-label="Primary">
      <div class="nav-shell">
        <a href="#home" class="text-2xl font-serif font-bold tracking-wider text-white justify-self-start">
          <span data-brand-short>ICAN</span><span class="text-brand-gold">.</span>
        </a>
        <div class="desktop-nav space-x-7 text-sm font-sans tracking-widest uppercase justify-self-center">
          <a href="#home" class="hover:text-brand-gold transition-colors">Home</a>
          <a href="#about" class="hover:text-brand-gold transition-colors">Estimate</a>
          <a href="#packages" class="hover:text-brand-gold transition-colors">Packages</a>
          <a href="#gallery" class="hover:text-brand-gold transition-colors">Spaces</a>
          <a href="#programs" class="hover:text-brand-gold transition-colors">Programs</a>
          <a href="#flow" class="hover:text-brand-gold transition-colors">Flow</a>
          @auth
            <a href="{{ route('dashboard') }}" class="hover:text-brand-gold transition-colors">Dashboard</a>
          @endauth
        </div>
        <div class="desktop-actions items-center gap-3 justify-self-end">
          @guest
	            <a href="{{ route('register') }}" class="nav-register px-6 py-2 border border-brand-gold/60 text-brand-gold hover:bg-brand-gold hover:text-brand-dark transition-all duration-300 font-sans text-xs tracking-widest uppercase">
	              Register
	            </a>
            <a href="{{ route('login') }}" class="px-6 py-2 border border-white text-white hover:bg-white hover:text-brand-dark transition-all duration-300 font-sans text-xs tracking-widest uppercase">
              Login
            </a>
          @endguest
          <button id="langToggle" class="px-3 py-2 border border-white/30 text-white/80 hover:border-brand-gold hover:text-brand-gold transition-all duration-300 font-sans text-xs tracking-widest uppercase" type="button" aria-pressed="false">KO</button>
        </div>
        <button id="menuBtn" class="menu-button ml-auto h-11 w-11 items-center justify-center text-white text-xl justify-self-end" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu">
          <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
      </div>
    </nav>

    <div id="mobileMenu" class="fixed inset-0 z-[60] pointer-events-none overflow-x-hidden">
      <div class="absolute inset-0 bg-brand-dark/80 opacity-0" data-menu-backdrop></div>
      <div class="absolute top-0 left-0 right-0 bg-brand-dark border-b border-white/10 opacity-0 -translate-y-6" data-menu-panel>
        <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
          <div class="text-lg font-serif tracking-wider text-white"><span data-brand-short>ICAN</span><span class="text-brand-gold">.</span></div>
          <button id="menuCloseBtn" class="text-white text-xl" aria-label="Close menu">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          </button>
        </div>
        <div class="max-w-7xl mx-auto px-6 pb-6">
          <div class="grid gap-4 font-sans tracking-widest uppercase text-sm" role="menu">
            <a class="hover:text-brand-gold transition-colors" href="#home" data-menu-link>Home</a>
            <a class="hover:text-brand-gold transition-colors" href="#about" data-menu-link>Estimate</a>
            <a class="hover:text-brand-gold transition-colors" href="#packages" data-menu-link>Packages</a>
            <a class="hover:text-brand-gold transition-colors" href="#gallery" data-menu-link>Spaces</a>
            <a class="hover:text-brand-gold transition-colors" href="#programs" data-menu-link>Programs</a>
            <a class="hover:text-brand-gold transition-colors" href="#flow" data-menu-link>Flow</a>
            @auth
              <a class="hover:text-brand-gold transition-colors" href="{{ route('dashboard') }}" data-menu-link>Dashboard</a>
            @endauth
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
              <a class="cta-request inline-flex justify-center px-6 py-3 border border-brand-gold text-brand-gold hover:bg-brand-gold transition-all duration-300 text-xs tracking-widest uppercase" href="{{ route('bookings.request') }}" data-menu-link data-cta="primary">Request Room</a>
              @guest
	                <a class="nav-register inline-flex justify-center px-6 py-3 border border-brand-gold/60 text-brand-gold hover:bg-brand-gold hover:text-brand-dark transition-all duration-300 text-xs tracking-widest uppercase" href="{{ route('register') }}" data-menu-link>Register</a>
                <a class="inline-flex justify-center px-6 py-3 border border-white text-white hover:bg-white hover:text-brand-dark transition-all duration-300 text-xs tracking-widest uppercase" href="{{ route('login') }}" data-menu-link>Login</a>
              @endguest
            </div>
            <button id="mobileLangToggle" class="mt-1 inline-flex justify-center border border-white/30 px-4 py-3 text-white/80 hover:border-brand-gold hover:text-brand-gold transition-colors text-xs tracking-widest uppercase" type="button" aria-pressed="false">KO</button>
          </div>
        </div>
      </div>
    </div>

    <header id="home" class="relative w-full h-screen flex items-center justify-center overflow-hidden">
      <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-brand-dark/55 z-10"></div>
        <img src="/media/Default1.jpeg" alt="ICAN Eduspace classroom" class="w-full h-full object-cover hero-img" fetchpriority="high">
      </div>
      <div class="relative z-20 text-center px-4 max-w-5xl mx-auto">
        <p class="hero-subtitle reveal-on-load text-brand-gold font-sans text-sm md:text-base tracking-[0.3em] uppercase mb-4">
          <span data-brand-tagline>CLASSROOM BOOKING SYSTEM</span>
        </p>
        <h1 class="hero-title reveal-on-load text-5xl md:text-7xl lg:text-9xl font-serif font-light text-white mb-6 leading-tight">
          Book <span class="italic text-brand-gold">focused</span><br>learning spaces
        </h1>
        <p class="hero-text reveal-on-load text-white/75 font-sans font-light max-w-lg mx-auto mb-10">
          Request ICAN Eduspace for classes, tutoring, workshops, and hybrid sessions. Staff confirms the room, schedule, and setup before the booking is final.
        </p>
        <div class="hero-btn reveal-on-load flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('bookings.request') }}" class="group relative inline-flex items-center justify-start overflow-hidden rounded bg-white px-8 py-4 transition-all hover:bg-white" data-cta="primary">
            <span class="absolute bottom-0 left-0 mb-9 ml-9 h-48 w-48 -translate-x-full translate-y-full rotate-45 rounded bg-brand-gold transition-all duration-500 ease-out group-hover:ml-0 group-hover:mb-32 group-hover:translate-x-0"></span>
            <span class="relative w-full text-left text-brand-dark transition-colors duration-300 ease-in-out group-hover:text-white font-sans uppercase tracking-wider text-sm">Start Booking</span>
          </a>
          <a href="#about" class="hero-secondary inline-flex items-center justify-center px-8 py-4 border border-brand-gold text-brand-gold hover:bg-brand-gold transition-all duration-300 font-sans text-sm tracking-widest uppercase rounded" data-cta="secondary">
            View Rooms
          </a>
        </div>
      </div>
      <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 flex flex-col items-center opacity-70 animate-bounce">
        <span class="text-[10px] uppercase tracking-widest mb-2 text-white/55">Scroll</span>
        <div class="w-[1px] h-12 bg-gradient-to-b from-brand-gold to-transparent"></div>
      </div>
    </header>

    <main>
      <section id="about" class="page-section py-16 md:py-20 bg-brand-dark relative">
        <div class="section-container max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
          <div data-reveal="left">
            <p class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans mb-5">Smart Planner</p>
            <h2 class="text-4xl md:text-6xl font-serif mb-6 leading-none" id="plannerHeadline">
              Start with a <br>
              <span class="text-stroke text-6xl md:text-8xl">space plan</span>
            </h2>
            <div class="w-20 h-1 bg-brand-gold mb-8"></div>
            <p class="text-white/65 font-sans font-light leading-relaxed mb-6" id="plannerLead">
              Pick your purpose, room, schedule, class option, and refreshments. The estimate updates automatically before you send the request.
            </p>
            <ul class="space-y-4 font-sans text-sm tracking-wide text-white/75" id="plannerHighlights">
              <li class="flex items-center"><i class="fa-solid fa-check text-brand-gold mr-4" aria-hidden="true"></i> Live package recommendation</li>
              <li class="flex items-center"><i class="fa-solid fa-check text-brand-gold mr-4" aria-hidden="true"></i> Auto-computed consultation estimate</li>
              <li class="flex items-center"><i class="fa-solid fa-check text-brand-gold mr-4" aria-hidden="true"></i> Copy or email the setup summary</li>
            </ul>
          </div>
          <div class="relative" data-reveal="right">
            <div class="absolute -top-4 -left-4 w-full h-full border border-brand-gold/30 z-0"></div>
            <div class="showcase-frame relative z-10 min-h-[560px] p-6 md:p-8">
              <div class="relative z-10 grid gap-4">
                <div class="flex items-center justify-between">
                  <span class="font-sans text-xs tracking-widest uppercase text-white/80">Booking Conditions</span>
                  <span class="text-brand-gold text-xs font-sans tracking-widest uppercase" id="plannerBadge">Space Rental</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div><label class="block mb-2 text-xs uppercase tracking-widest text-white/55" for="planPurpose">Purpose</label><select class="form-input" id="planPurpose"><option value="seminar">AI lecture / seminar</option><option value="mentoring">1:1 mentoring / small group</option><option value="tesda">Korean TESDA vocational training</option><option value="office">Office / education workshop</option></select></div>
                  <div><label class="block mb-2 text-xs uppercase tracking-widest text-white/55" for="planSpace">Space</label><select class="form-input" id="planSpace"><option value="dayuse">Place-use customer</option><option value="hub">11F AI Hub</option><option value="mentor">16F Mentoring Zone</option><option value="dual">Dual campus access</option></select></div>
                  <div><label class="block mb-2 text-xs uppercase tracking-widest text-white/55" for="planPeople">Participants</label><select class="form-input" id="planPeople"><option value="4">1-4 people</option><option value="12">5-12 people</option><option value="24">13-24 people</option><option value="32">25+ people</option></select></div>
                  <div><label class="block mb-2 text-xs uppercase tracking-widest text-white/55" for="planHours">Usage time</label><select class="form-input" id="planHours"><option value="1">1 hour</option><option value="2">2 hours</option><option value="morning">Morning block</option><option value="afternoon">Afternoon block</option><option value="evening">Evening block</option><option value="4">Half-day 4 hours</option><option value="8">Full-day 8 hours</option></select></div>
                  <div><label class="block mb-2 text-xs uppercase tracking-widest text-white/55" for="planClass">Class option</label><select class="form-input" id="planClass"><option value="0">Space only</option><option value="500">Add hands-on AI class</option><option value="500">Add Space and AI project</option><option value="500">Add TESDA Korean vocational training</option><option value="500">Add personal business consulting</option></select></div>
                  <div><label class="block mb-2 text-xs uppercase tracking-widest text-white/55" for="planRefresh">Coffee and snacks</label><select class="form-input" id="planRefresh"><option value="basic">Basic included</option><option value="plus">Premium refreshments</option></select></div>
                </div>
                <div class="glass-panel p-5 mt-2">
                  <p class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans mb-3">Estimated Package</p>
                  <h3 id="estimateName" class="text-3xl md:text-4xl font-serif text-white mb-2">AI Class Ready</h3>
                  <p id="estimatePrice" class="text-white/70 font-sans text-sm mb-4">Estimated consultation price: ₱1,800 and up</p>
                  <ul id="estimateItems" class="space-y-2 text-white/60 font-sans text-sm"></ul>
                  <div class="flex flex-col sm:flex-row gap-3 mt-5">
                    <button id="estimateMail" class="bg-brand-gold text-brand-dark px-4 py-3 uppercase tracking-widest text-xs font-semibold hover:bg-white transition-colors" type="button">Request with this setup</button>
                    <button id="copyEstimate" class="border border-white/20 text-white px-4 py-3 uppercase tracking-widest text-xs hover:border-brand-gold hover:text-brand-gold transition-colors" type="button">Copy estimate</button>
                  </div>
                  <p id="estimateStatus" class="mt-3 text-sm text-brand-gold" aria-live="polite"></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="packages" class="page-section py-16 md:py-20 bg-brand-gray text-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6">
          <div class="text-center mb-12">
            <span class="text-brand-gold font-sans text-xs tracking-[0.2em] uppercase">Packages</span>
            <h2 class="text-4xl md:text-5xl font-serif mt-4">Simple packages for common booking needs.</h2>
            <p class="mt-5 text-brand-navy/70 font-sans font-light max-w-2xl mx-auto">
              Start with space only, add class support when needed, or request a regular institution setup. Final pricing is confirmed after staff checks the schedule and requirements.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch" id="cards">
            <div class="service-card package-card-blue group">
              <div class="package-icon">
                <i class="fa-solid fa-cube text-6xl" aria-hidden="true"></i>
              </div>
              <span class="package-label font-sans">Space Only</span>
              <h3 class="text-2xl font-serif text-white mt-3 mb-4 group-hover:text-brand-gold transition-colors">Space Rental</h3>
              <p class="font-sans font-light text-sm leading-relaxed mb-6">Place-use customers can use the space at ₱200/hr or ₱500 per morning, afternoon, or evening block.</p>
              <ul class="space-y-3 font-sans font-light text-sm leading-relaxed">
                <li>Hourly use at ₱200/hr</li>
                <li>Morning, afternoon, and evening blocks at ₱500 each</li>
                <li>Display and presentation setup</li>
              </ul>
            </div>

            <div class="service-card package-card-blue group">
              <div class="package-icon">
                <i class="fa-solid fa-wand-magic-sparkles text-6xl" aria-hidden="true"></i>
              </div>
              <span class="package-label font-sans">Recommended</span>
              <h3 class="text-2xl font-serif text-white mt-3 mb-4 group-hover:text-brand-gold transition-colors">AI Class Ready</h3>
              <p class="font-sans font-light text-sm leading-relaxed mb-6">The recommended package adds AI classes or personal business consulting at ₱500/hr to the space rate.</p>
              <ul class="space-y-3 font-sans font-light text-sm leading-relaxed">
                <li>AI practice topics and facilitation plan</li>
                <li>Prompt and presentation templates</li>
                <li>Personal business consulting available</li>
              </ul>
            </div>

            <div class="service-card package-card-blue group">
              <div class="package-icon">
                <i class="fa-solid fa-building-columns text-6xl" aria-hidden="true"></i>
              </div>
              <span class="package-label font-sans">Institution</span>
              <h3 class="text-2xl font-serif text-white mt-3 mb-4 group-hover:text-brand-gold transition-colors">Institution Partner</h3>
              <p class="font-sans font-light text-sm leading-relaxed mb-6">Classes for 13+ participants use ₱1,200/hr as a guide, and dual campus operations use ₱1,500/hr.</p>
              <ul class="space-y-3 font-sans font-light text-sm leading-relaxed">
                <li>Monthly slot reservation</li>
                <li>Institution-specific operation plan</li>
                <li>Outputs, attendance, and feedback operations</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <section class="relative py-32 bg-fixed bg-center bg-cover" style="background-image:url('/media/AICognitionRoom.jpeg');" aria-label="Quote">
        <div class="absolute inset-0 bg-brand-dark/70"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
          <i class="fa-solid fa-quote-left text-brand-gold text-4xl mb-8" aria-hidden="true"></i>
          <blockquote class="text-3xl md:text-5xl font-serif italic text-white leading-tight">
            Every focused space can turn study time into real progress.
          </blockquote>
        </div>
      </section>

      <section id="gallery" class="page-section py-16 md:py-20 bg-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6">
          <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 gap-6">
            <div class="text-center md:text-left">
              <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">Spaces</span>
              <h2 class="text-4xl md:text-6xl font-serif mt-3">Available Spaces</h2>
            </div>
            <a href="{{ route('bookings.request') }}" class="text-sm uppercase tracking-widest border-b border-brand-gold text-brand-gold pb-1">Request a room</a>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 auto-rows-[280px]">
            @foreach ($classrooms as $classroom)
              <article class="{{ $loop->first ? 'md:col-span-2 md:row-span-2' : '' }} relative group overflow-hidden cursor-pointer">
                <img src="{{ $classroom->hero_image }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $classroom->name }}" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/85 via-brand-dark/20 to-transparent"></div>
                <div class="absolute inset-0 bg-brand-dark/35 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-6">
                  <div class="text-center">
                    <h4 class="font-serif text-2xl italic">{{ $classroom->name }}</h4>
                    <p class="mt-3 text-sm font-sans text-white/70">PHP {{ number_format($classroom->hourly_rate) }}/hr · {{ $classroom->capacity }} <span>pax</span></p>
                  </div>
                </div>
              </article>
            @endforeach
            <article class="md:col-span-2 relative group overflow-hidden cursor-pointer">
              <img src="/media/Default1.jpeg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Hybrid classroom setup" loading="lazy">
              <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/85 via-brand-dark/20 to-transparent"></div>
              <div class="absolute inset-0 bg-brand-dark/35 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                <div class="text-center"><h4 class="font-serif text-xl italic">Hybrid Setup</h4></div>
              </div>
            </article>
          </div>

        </div>
      </section>

      <section id="programs" class="page-section py-16 md:py-20 bg-brand-gray text-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-[.9fr_1.1fr] gap-12 items-center">
          <div class="grid gap-4">
            <article class="bg-brand-dark text-white p-6 border border-white/10"><span class="text-brand-gold text-xs uppercase tracking-[0.2em]">Class Flow</span><strong class="block font-serif text-2xl mt-3">Intro -> Practice -> Presentation</strong><p class="text-white/60 mt-2 text-sm">Instead of only renting a room, the class flow and presentation output are planned together.</p></article>
            <article class="bg-brand-dark text-white p-6 border border-white/10"><span class="text-brand-gold text-xs uppercase tracking-[0.2em]">Materials</span><strong class="block font-serif text-2xl mt-3">Prompts, Templates, and Worksheets</strong><p class="text-white/60 mt-2 text-sm">Class materials and an operations checklist are added so instructors can run the session immediately.</p></article>
            <article class="bg-brand-dark text-white p-6 border border-white/10"><span class="text-brand-gold text-xs uppercase tracking-[0.2em]">Hybrid Ready</span><strong class="block font-serif text-2xl mt-3">Onsite + Online Participation</strong><p class="text-white/60 mt-2 text-sm">Seating and class flow are prepared for both onsite students and online participants.</p></article>
          </div>
          <div>
            <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">Education Included</span>
            <h2 class="text-4xl md:text-6xl font-serif mt-3">Add class support when the room needs more than seats.</h2>
            <p class="mt-5 text-brand-navy/70 font-sans font-light leading-relaxed">External instructors and institutions may use only the space, or combine it with ICAN's AI-native class content and onsite operations.</p>
            <div class="mt-8 grid gap-4">
              <article class="flex gap-4"><div class="shrink-0 w-12 h-12 bg-brand-gold text-brand-dark grid place-items-center font-bold">AI</div><div><h3 class="font-serif text-2xl">Hands-on AI Class Package</h3><p class="text-brand-navy/65 text-sm">Runs classes on generative AI use, prompt design, presentation building, and problem-solving projects.</p></div></article>
              <article class="flex gap-4"><div class="shrink-0 w-12 h-12 bg-brand-gold text-brand-dark grid place-items-center font-bold">SP</div><div><h3 class="font-serif text-2xl">Space and AI Project</h3><p class="text-brand-navy/65 text-sm">An experiential class connecting space technology, future careers, and data exploration into student presentations and outputs.</p></div></article>
              <article class="flex gap-4"><div class="shrink-0 w-12 h-12 bg-brand-gold text-brand-dark grid place-items-center font-bold">KR</div><div><h3 class="font-serif text-2xl">Korean TESDA Vocational Training</h3><p class="text-brand-navy/65 text-sm">Korean-based vocational training, interview preparation, workplace expressions, and field-adaptive language training can be operated.</p></div></article>
            </div>
          </div>
        </div>
      </section>

      <section class="page-section py-16 md:py-20 bg-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6">
          <div class="max-w-3xl mb-10">
            <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">Included</span>
            <h2 class="text-4xl md:text-6xl font-serif mt-3">Space, equipment, operations, and refreshments together.</h2>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <article class="glass-panel p-6"><h3 class="font-serif text-xl mb-3">Classroom Setup</h3><p class="text-white/65 text-sm">Seats, displays, presentation flow, and small-group tables are prepared for the class purpose.</p></article>
            <article class="glass-panel p-6"><h3 class="font-serif text-xl mb-3">AI Class Options</h3><p class="text-white/65 text-sm">AI-native lectures, project topics, presentation templates, and practice flows are available as options.</p></article>
            <article class="glass-panel p-6"><h3 class="font-serif text-xl mb-3">Coffee and Snacks</h3><p class="text-white/65 text-sm">Light drinks and snacks create an education lounge experience where participants can stay longer.</p></article>
            <article class="glass-panel p-6"><h3 class="font-serif text-xl mb-3">Operations Support</h3><p class="text-white/65 text-sm">Onsite support covers entry guidance, basic equipment checks, and hybrid class preparation.</p></article>
          </div>
        </div>
      </section>

      <section id="lead" class="page-section py-16 md:py-20 bg-brand-gray text-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">Custom Request</span>
            <h2 class="text-4xl md:text-6xl font-serif mt-3">Need a custom setup? Send a note.</h2>
            <p class="mt-5 text-brand-navy/70 font-sans font-light leading-relaxed">For repeated use, institution programs, or special schedules, share the details and staff will prepare the next step.</p>
          </div>
          <div class="bg-white p-6 md:p-8 border border-brand-dark/10 shadow-xl">
            <h3 class="font-serif text-3xl mb-2">Space Consultation Request</h3>
            <p class="text-sm text-brand-navy/60 mb-6">Your input is summarized only in the browser.</p>
            <form id="leadForm" class="grid grid-cols-1 md:grid-cols-2 gap-4" novalidate>
              <input id="leadName" class="min-h-12 border border-brand-dark/15 px-3" type="text" placeholder="Your name">
              <input id="leadOrg" class="min-h-12 border border-brand-dark/15 px-3" type="text" placeholder="Academy, company, community">
              <input id="leadContact" class="md:col-span-2 min-h-12 border border-brand-dark/15 px-3" type="text" placeholder="Email or KakaoTalk ID">
              <textarea id="leadMemo" class="md:col-span-2 min-h-28 border border-brand-dark/15 px-3 py-3" placeholder="Example: 12-person AI workshop, Saturday afternoon, coffee and snacks included"></textarea>
              <button id="leadMail" class="md:col-span-2 bg-brand-gold text-brand-dark px-6 py-4 uppercase tracking-widest text-xs font-semibold hover:bg-brand-dark hover:text-white transition-colors" type="submit">Create consultation email</button>
            </form>
            <div id="leadSummary" class="mt-5 p-4 bg-brand-gray text-sm text-brand-navy/70">The consultation summary will appear here as you type.</div>
            <p id="leadStatus" class="mt-3 text-sm text-brand-blue" aria-live="polite"></p>
          </div>
        </div>
      </section>

      <section id="flow" class="page-section py-16 md:py-20 bg-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6">
          <div class="max-w-3xl mb-10">
            <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">How It Works</span>
            <h2 class="text-4xl md:text-6xl font-serif mt-3">Choose the space, add the class purpose, and operate immediately.</h2>
            <p class="mt-5 text-white/65 font-sans font-light leading-relaxed">Space and content are packaged together so academies, companies, churches, education startups, and freelance instructors can start AI education.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <article class="glass-panel p-6"><span class="text-brand-gold font-serif text-4xl">01</span><strong class="block mt-6 mb-2">Choose Purpose</strong><p class="text-white/65 text-sm">Select the purpose: AI class, vocational training, workshop, or office-style study.</p></article>
            <article class="glass-panel p-6"><span class="text-brand-gold font-serif text-4xl">02</span><strong class="block mt-6 mb-2">Match the Space</strong><p class="text-white/65 text-sm">Assign the 11F AI Hub or 16F Mentoring Zone based on participants and flow.</p></article>
            <article class="glass-panel p-6"><span class="text-brand-gold font-serif text-4xl">03</span><strong class="block mt-6 mb-2">Design Class Options</strong><p class="text-white/65 text-sm">Add AI-native class, space project, or Korean TESDA module when needed.</p></article>
            <article class="glass-panel p-6"><span class="text-brand-gold font-serif text-4xl">04</span><strong class="block mt-6 mb-2">Onsite Operations</strong><p class="text-white/65 text-sm">Check equipment, entry, coffee and snacks, and online participation setup before starting.</p></article>
          </div>
        </div>
      </section>

      <section class="page-section py-16 md:py-20 bg-brand-gray text-brand-dark">
        <div class="section-container max-w-7xl mx-auto px-6">
          <div class="max-w-3xl mb-10">
            <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">Contact Flow</span>
            <h2 class="text-4xl md:text-6xl font-serif mt-3">After consultation, you receive an operation plan.</h2>
            <p class="mt-5 text-brand-navy/70 font-sans font-light leading-relaxed">After sending an email, you receive more than a simple reply: the team organizes the recommended space, estimated cost, preparation items, and next confirmations.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <article class="bg-white p-8 border border-brand-dark/10"><span class="text-brand-gold uppercase tracking-[0.2em] text-xs">Email Request</span><h3 class="font-serif text-3xl mt-3 mb-4">Send the estimate and request by email.</h3><p class="text-brand-navy/65 mb-6">Use the estimate calculator or request form to send an automatically prepared Korean-English email.</p><a class="bg-brand-gold text-brand-dark px-5 py-3 uppercase tracking-widest text-xs" href="#about">Create Estimate</a></article>
            <article class="bg-white p-8 border border-brand-dark/10"><span class="text-brand-gold uppercase tracking-[0.2em] text-xs">Existing App</span><h3 class="font-serif text-3xl mt-3 mb-4">Move to the separate consultation app.</h3><p class="text-brand-navy/65 mb-6">Open the external consultation app when detailed consultation or the existing operations app is needed.</p><a class="border border-brand-dark px-5 py-3 uppercase tracking-widest text-xs" href="https://consultant.icanacademy.work/">Open Consultation App</a></article>
          </div>
        </div>
      </section>

      <section class="page-section py-16 md:py-20 bg-brand-dark">
        <div class="section-container max-w-5xl mx-auto px-6">
          <span class="text-brand-gold uppercase tracking-[0.2em] text-xs font-sans">FAQ</span>
          <h2 class="text-4xl md:text-6xl font-serif mt-3 mb-10">Questions before booking.</h2>
          <div class="grid gap-4">
            <details class="glass-panel p-5"><summary class="cursor-pointer font-serif text-2xl">Can I use only the space?</summary><p class="mt-3 text-white/65">Yes. Open study or personal work starts at ₱200/hr, and morning, afternoon, or evening block use is also available.</p></details>
            <details class="glass-panel p-5"><summary class="cursor-pointer font-serif text-2xl">Can I add AI classes or consulting?</summary><p class="mt-3 text-white/65">Yes. Hands-on AI class, Space and AI project, TESDA Korean vocational training, and personal business consulting can be added as hourly options.</p></details>
            <details class="glass-panel p-5"><summary class="cursor-pointer font-serif text-2xl">How is the booking confirmed?</summary><p class="mt-3 text-white/65">Send the estimate and request from the site, then the operations team confirms the date, participants, space setup, equipment, and refreshments.</p></details>
            <details class="glass-panel p-5"><summary class="cursor-pointer font-serif text-2xl">Can online participants join too?</summary><p class="mt-3 text-white/65">Yes. For hybrid classes, the team checks the broadcast setup and onsite flow together.</p></details>
          </div>
        </div>
      </section>

      <section id="contact" class="page-section book-section py-12 md:py-16 bg-gradient-to-br from-brand-blue to-brand-dark relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-brand-gold/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
        <div class="section-container max-w-5xl mx-auto px-6 relative z-10 border border-white/10 p-6 md:p-10 backdrop-blur-sm bg-white/5 rounded-sm">
          <div class="text-center max-w-3xl mx-auto">
            <h4 class="text-brand-gold uppercase tracking-[0.3em] text-sm mb-4">Booking Request</h4>
            <h2 class="text-5xl md:text-6xl font-serif text-white mb-5">Tell us when you need the room.</h2>
            <p class="text-white/70 font-sans font-light mb-7 max-w-lg mx-auto">
              Submit the first request. Staff reviews availability, room setup, and class format before final confirmation.
            </p>
          </div>

          @if (session('booking_saved'))
            <p class="mb-6 border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('booking_saved') }}</p>
          @endif
          @if ($errors->any())
            <p class="mb-6 border border-red-300/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">Please check the booking details and try again.</p>
          @endif

          @auth
          <form class="grid grid-cols-1 md:grid-cols-2 gap-4" method="POST" action="{{ route('bookings.store') }}" data-turnstile-booking-form>
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;opacity:0;">

            <div class="md:col-span-2">
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="purpose">Purpose</label>
              <select class="form-input" id="purpose" name="purpose" required>
                <option>AI lecture / seminar</option>
                <option>1:1 mentoring / small group</option>
                <option>Korean TESDA vocational training</option>
                <option>Office / education workshop</option>
              </select>
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="classroom_id">Room</label>
              <select class="form-input" id="classroom_id" name="classroom_id">
                @foreach ($classrooms as $classroom)
                  <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="booking_date">Date</label>
              <input class="form-input" id="booking_date" name="booking_date" type="date" required min="{{ now()->toDateString() }}">
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="booking_end_date_home">End date <span class="text-white/35">(optional)</span></label>
              <input class="form-input" id="booking_end_date_home" name="booking_end_date" type="date" min="{{ now()->toDateString() }}">
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="time_block">Time block</label>
              <select class="form-input" id="time_block" name="time_block" required>
                <option value="morning">Morning block</option>
                <option value="afternoon">Afternoon block</option>
                <option value="evening">Evening block</option>
              </select>
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="participant_count">Participants</label>
              <input class="form-input" id="participant_count" name="participant_count" type="number" min="1" max="200" placeholder="12">
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="format">Format</label>
              <select class="form-input" id="format" name="format" required>
                <option>Offline</option>
                <option>Online broadcast</option>
                <option>Hybrid</option>
              </select>
            </div>
            <div class="md:col-span-2 border border-white/10 bg-white/5 p-4 text-left">
              <p class="block mb-3 text-xs uppercase tracking-widest text-white/60">Equipment</p>
              <div class="grid sm:grid-cols-2 gap-3">
                @foreach(\App\Models\Booking::EQUIPMENT_OPTIONS as $value => $label)
                  <label class="flex items-center gap-3 text-sm text-white/75">
                    <input type="checkbox" name="equipment_requests[]" value="{{ $value }}">
                    <span>{{ $label }}</span>
                  </label>
                @endforeach
              </div>
              <label class="block mt-4 mb-2 text-xs uppercase tracking-widest text-white/45" for="equipment_notes_home">Equipment notes</label>
              <input class="form-input" id="equipment_notes_home" name="equipment_notes" type="text" placeholder="Example: Need school AI tools prepared for class demo">
            </div>
            <div class="md:col-span-2 border border-white/10 bg-white/5 p-4 text-left">
              <p class="block mb-3 text-xs uppercase tracking-widest text-white/60">Coffee and snacks</p>
              <div class="grid sm:grid-cols-2 gap-3">
                @foreach(\App\Models\Booking::SNACK_BEVERAGE_OPTIONS as $value => $label)
                  <label class="flex items-center gap-3 text-sm text-white/75">
                    <input type="checkbox" name="snack_beverage_requests[]" value="{{ $value }}">
                    <span>{{ $label }}</span>
                  </label>
                @endforeach
              </div>
              <label class="block mt-4 mb-2 text-xs uppercase tracking-widest text-white/45" for="snack_beverage_notes_home">Coffee/snack notes</label>
              <input class="form-input" id="snack_beverage_notes_home" name="snack_beverage_notes" type="text" placeholder="Example: Coffee for 8 guests, no sugar">
            </div>
            <div>
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="customer_name">Name</label>
              <input class="form-input" id="customer_name" name="customer_name" type="text" required placeholder="Your name" value="{{ auth()->user()->name ?? '' }}">
            </div>
            <div class="md:col-span-2">
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="contact">Contact</label>
              <input class="form-input" id="contact" name="contact" type="text" required placeholder="Email, phone, or KakaoTalk" value="{{ auth()->user()->email ?? '' }}">
            </div>
            <div class="md:col-span-2">
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="payment_method_home">Payment method</label>
              <select class="form-input" id="payment_method_home" name="payment_method" required>
                <option value="">Choose payment method</option>
                @foreach(\App\Models\Booking::PAYMENT_METHOD_OPTIONS as $value => $label)
                  <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block mb-2 text-xs uppercase tracking-widest text-white/60" for="customer_notes">Notes</label>
              <textarea class="form-input" id="customer_notes" name="customer_notes" placeholder="Example: 12-person AI workshop, Saturday afternoon, coffee included"></textarea>
            </div>
            @if (app(\App\Services\TurnstileVerifier::class)->enabled())
              <div class="md:col-span-2 flex flex-col items-center">
                <x-turnstile
                  data-turnstile-widget="home-booking"
                  data-theme="dark"
                  data-appearance="always"
                  data-execution="execute"
                  data-callback="bookingTurnstileCallback"
                  data-expired-callback="bookingTurnstileExpiredCallback"
                  data-error-callback="bookingTurnstileErrorCallback"
                />
                <p class="mt-2 text-sm text-white/55 hidden" data-turnstile-status>Verification is required before sending.</p>
                @error('cf-turnstile-response')
                  <p class="mt-2 text-sm text-red-100">{{ $message }}</p>
                @enderror
              </div>
            @endif
            <div class="md:col-span-2 flex flex-col md:flex-row justify-between items-center gap-6 pt-4">
              <p class="text-xs uppercase tracking-widest text-white/45">Dates are confirmed after staff review.</p>
              <button type="submit" class="bg-brand-gold text-brand-dark px-10 py-4 font-sans font-semibold uppercase tracking-widest hover:bg-white transition-colors duration-300 w-full md:w-auto" data-turnstile-submit>
                Send request
              </button>
            </div>
          </form>
          @else
            <div class="grid gap-4 md:grid-cols-2">
              <a href="{{ route('bookings.request') }}" class="bg-brand-gold text-brand-dark px-6 py-4 text-center font-sans font-semibold uppercase tracking-widest hover:bg-white transition-colors duration-300">
                Register to request room
              </a>
              <a href="{{ route('login') }}" class="border border-white/50 px-6 py-4 text-center font-sans font-semibold uppercase tracking-widest text-white hover:bg-white hover:text-brand-dark transition-colors duration-300">
                Login
              </a>
            </div>
          @endauth

          @auth
            <script>
              (() => {
                const form = document.querySelector('[data-turnstile-booking-form]');

                if (!form) {
                  return;
                }

                const startInput = form.querySelector('[name="booking_date"]');
                const endInput = form.querySelector('[name="booking_end_date"]');
                const timeInput = form.querySelector('[name="time_block"]');
                const parseDate = (value) => {
                  const parts = String(value || '').split('-').map(Number);

                  if (parts.length !== 3 || parts.some((part) => Number.isNaN(part))) {
                    return null;
                  }

                  return new Date(parts[0], parts[1] - 1, parts[2]);
                };
                const dateKey = (date) => [
                  date.getFullYear(),
                  String(date.getMonth() + 1).padStart(2, '0'),
                  String(date.getDate()).padStart(2, '0'),
                ].join('-');
                const rangeDates = () => {
                  const start = parseDate(startInput?.value);
                  const end = parseDate(endInput?.value) || start;

                  if (!start) {
                    return [];
                  }

                  if (end < start) {
                    return [dateKey(start)];
                  }

                  const dates = [];

                  for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                    dates.push(dateKey(date));
                  }

                  return dates;
                };
                const scheduleLabel = () => {
                  const dates = rangeDates();

                  if (dates.length > 1) {
                    return `${dates[0]} to ${dates[dates.length - 1]} (${dates.length} days)`;
                  }

                  return dates[0] || 'the selected date';
                };
                const resetScheduleConfirmation = () => {
                  form.dataset.scheduleConfirmed = '0';

                  if (endInput && startInput?.value) {
                    endInput.min = startInput.value;
                  }
                };

                form.addEventListener('change', (event) => {
                  if (event.target.matches('[name="classroom_id"], [name="booking_date"], [name="booking_end_date"], [name="time_block"]')) {
                    resetScheduleConfirmation();
                  }
                });
                form.addEventListener('submit', (event) => {
                  if (form.dataset.scheduleConfirmed === '1') {
                    return;
                  }

                  event.preventDefault();

                  const dates = rangeDates();
                  const time = timeInput?.selectedOptions?.[0]?.textContent?.trim() || 'selected time';
                  const message = dates.length > 1
                    ? `You are requesting ${dates.length} daily bookings from ${scheduleLabel()} at ${time}. Staff will review each date before final confirmation. Continue?`
                    : `You are requesting ${scheduleLabel()} at ${time}. Staff will review before final confirmation. Continue?`;

                  if (confirm(message)) {
                    form.dataset.scheduleConfirmed = '1';
                    form.requestSubmit();
                  }
                });

                resetScheduleConfirmation();
              })();
            </script>
            @if (app(\App\Services\TurnstileVerifier::class)->enabled())
              <script>
                (() => {
                  const form = document.querySelector('[data-turnstile-booking-form]');

                  if (!form) {
                    return;
                  }

                  const turnstileSelector = '[data-turnstile-widget="home-booking"]';
                  const widget = form.querySelector(turnstileSelector);
                  const status = form.querySelector('[data-turnstile-status]');
                  const submitButton = form.querySelector('[data-turnstile-submit]');
                  const response = () => form.querySelector('input[name="cf-turnstile-response"]')?.value || '';

                  const setStatus = (message, isError = false) => {
                    if (!status) {
                      return;
                    }

                    status.textContent = message;
                    status.classList.remove('hidden', 'text-white/55', 'text-red-100');
                    status.classList.add(isError ? 'text-red-100' : 'text-white/55');
                  };

                  const setWaiting = (waiting) => {
                    if (!submitButton) {
                      return;
                    }

                    submitButton.disabled = waiting;
                    submitButton.style.opacity = waiting ? '0.72' : '';
                    submitButton.style.cursor = waiting ? 'wait' : '';
                  };

                  window.bookingTurnstileCallback = () => {
                    const pending = form.dataset.turnstilePending === '1';
                    form.dataset.turnstileVerified = '1';
                    setWaiting(false);

                    if (pending) {
                      setStatus('Verification complete. Sending request...');
                      form.dataset.turnstilePending = '0';
                      form.requestSubmit(submitButton);
                    } else {
                      status?.classList.add('hidden');
                    }
                  };

                  window.bookingTurnstileExpiredCallback = () => {
                    form.dataset.turnstileVerified = '0';
                    form.dataset.turnstilePending = '0';
                    setWaiting(false);
                    setStatus('Verification expired. Please send the request again.', true);
                  };

                  window.bookingTurnstileErrorCallback = () => {
                    form.dataset.turnstileVerified = '0';
                    form.dataset.turnstilePending = '0';
                    setWaiting(false);
                    setStatus('Verification could not start. Please reload and try again.', true);
                  };

                  form.addEventListener('submit', (event) => {
                    if (event.defaultPrevented) {
                      return;
                    }

                    if (form.dataset.turnstileVerified === '1' || response()) {
                      return;
                    }

                    event.preventDefault();

                    if (!widget || !window.turnstile || typeof window.turnstile.execute !== 'function') {
                      setStatus('Verification is still loading. Please try again in a moment.', true);
                      return;
                    }

                    form.dataset.turnstilePending = '1';
                    setWaiting(true);
                    setStatus('Starting verification...');
                    window.turnstile.execute(turnstileSelector);
                  });
                })();
              </script>
            @endif
          @endauth

          <div class="mt-12 pt-8 border-t border-white/10 flex flex-col md:flex-row justify-center items-center space-y-4 md:space-y-0 md:space-x-12">
            <div class="flex items-center text-white/55">
              <i class="fa-solid fa-location-dot mr-3 text-brand-gold" aria-hidden="true"></i>
              <span class="font-sans text-sm" data-brand-location>11F + 16F Eduspace</span>
            </div>
            <div class="flex space-x-6">
              <a href="mailto:icanacademy@naver.com" class="text-white/55 hover:text-brand-gold" aria-label="Email"><i class="fa-solid fa-envelope text-xl" aria-hidden="true"></i></a>
              <a href="/admin" class="text-white/55 hover:text-brand-gold" aria-label="Admin panel"><i class="fa-solid fa-user-shield text-xl" aria-hidden="true"></i></a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="bg-brand-dark py-8 border-t border-white/10">
      <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-xs text-white/45 font-sans tracking-wider">
        <p>&copy; <span data-year></span> <span data-brand-text>ICAN Eduspace</span>. All Rights Reserved.</p>
        <div class="mt-4 md:mt-0 space-x-6">
          @guest
            <a href="{{ route('register') }}" class="hover:text-white">Register</a>
          @endguest
          <a href="{{ route('login') }}" class="hover:text-white">Login</a>
          <a href="/admin" class="hover:text-white">Admin Panel</a>
        </div>
      </div>
    </footer>

    <script>
      (() => {
        "use strict";

        const CTA = { primary: "{{ route('bookings.request') }}", secondary: "#about" };
        const BRAND = {
          name: "ICAN Eduspace",
          short: "ICAN",
          tagline: "CLASSROOM BOOKING SYSTEM",
          location: "11F + 16F Eduspace",
        };
        let activeLang = localStorage.getItem("icaneduspace-lang") || "en";

        const staticTranslations = {
          Home: "홈",
          Estimate: "견적",
          Packages: "패키지",
          Spaces: "공간",
          Programs: "프로그램",
          Flow: "진행",
          Planner: "계산기",
          Book: "예약",
          Dashboard: "대시보드",
          Login: "로그인",
          Register: "회원가입",
          "Request Room": "공간 요청",
          "Register to request room": "회원가입 후 공간 요청",
          "CLASSROOM BOOKING SYSTEM": "강의실 예약 시스템",
          "focused": "집중 학습",
          "Book focused": "집중 학습",
          "learning spaces": "공간 예약",
          "Request ICAN Eduspace for classes, tutoring, workshops, and hybrid sessions. Staff confirms the room, schedule, and setup before the booking is final.": "강의, 튜터링, 워크숍, 하이브리드 수업을 위한 아이캔 에듀스페이스 공간을 요청하세요. 최종 확정 전 담당자가 공간, 일정, 세팅을 확인합니다.",
          "Start Booking": "예약 시작",
          "View Rooms": "공간 보기",
          Scroll: "스크롤",
          "Smart Planner": "스마트 견적",
          "Start with a": "먼저",
          "space plan": "공간 계획",
          "Pick your purpose, room, schedule, class option, and refreshments. The estimate updates automatically before you send the request.": "목적, 공간, 일정, 수업 옵션, 다과를 선택하면 요청 전 예상 견적이 자동으로 바뀝니다.",
          "Live package recommendation": "추천 패키지 자동 표시",
          "Auto-computed consultation estimate": "상담 견적 자동 계산",
          "Copy or email the setup summary": "요약 복사 또는 이메일 작성",
          "Booking Conditions": "예약 조건",
          Purpose: "목적",
          Space: "공간",
          "Place-use customer": "장소 이용 고객",
          "11F AI Hub": "11F AI 허브",
          "16F Mentoring Zone": "16F 맨투맨 Zone",
          "Dual campus access": "듀얼 캠퍼스 전체",
          Participants: "인원",
          "1-4 people": "1-4명",
          "5-12 people": "5-12명",
          "13-24 people": "13-24명",
          "25+ people": "25명 이상",
          "Usage time": "사용 시간",
          "1 hour": "1시간",
          "2 hours": "2시간",
          "Half-day 4 hours": "반일 4시간",
          "Full-day 8 hours": "종일 8시간",
          "Class option": "수업 옵션",
          "Space only": "공간만 사용",
          "Add hands-on AI class": "AI 실전수업 추가",
          "Add Space and AI project": "우주·AI 프로젝트 추가",
          "Add TESDA Korean vocational training": "TESDA 한국어 직업교육 추가",
          "Add personal business consulting": "개인별 사업 컨설팅 추가",
          "Coffee and snacks": "커피·다과",
          "Basic included": "기본 포함",
          "Premium refreshments": "프리미엄 다과",
          "Estimated Package": "예상 패키지",
          "Request with this setup": "이 조건으로 요청",
          "Copy estimate": "견적 복사",
          "Simple packages for common booking needs.": "자주 쓰는 예약 목적에 맞춘 간단한 패키지",
          "Start with space only, add class support when needed, or request a regular institution setup. Final pricing is confirmed after staff checks the schedule and requirements.": "공간만 이용하거나, 필요할 때 수업 지원을 추가하거나, 기관 정기 운영을 요청할 수 있습니다. 최종 요금은 담당자가 일정과 조건을 확인한 뒤 확정됩니다.",
          "Space Only": "공간만",
          "Space Rental": "공간 기본 임대",
          "Place-use customers can use the space at ₱200/hr or ₱500 per morning, afternoon, or evening block.": "장소 이용 고객은 ₱200/hr 또는 오전·오후·저녁 각 ₱500 블록으로 이용할 수 있습니다.",
          "Hourly use at ₱200/hr": "시간제 ₱200/hr",
          "Morning, afternoon, and evening blocks at ₱500 each": "오전·오후·저녁 블록 각 ₱500",
          "Display and presentation setup": "모니터와 발표 환경 제공",
          Recommended: "추천",
          "AI Class Ready": "AI 클래스 준비",
          "The recommended package adds AI classes or personal business consulting at ₱500/hr to the space rate.": "추천 패키지는 공간요금에 AI 강의 또는 개인별 사업 컨설팅 ₱500/hr를 추가합니다.",
          "AI practice topics and facilitation plan": "AI 실습 주제와 진행안 제공",
          "Prompt and presentation templates": "프롬프트와 발표 템플릿",
          "Personal business consulting available": "개인별 사업 컨설팅 선택 가능",
          Institution: "기관",
          "Institution Partner": "교육기관 파트너",
          "Classes for 13+ participants use ₱1,200/hr as a guide, and dual campus operations use ₱1,500/hr.": "13명 이상 클래스는 ₱1,200/hr, 듀얼 캠퍼스 운영은 ₱1,500/hr 기준으로 운영합니다.",
          "Monthly slot reservation": "월 단위 슬롯 확보",
          "Institution-specific operation plan": "기관 맞춤 운영안 설계",
          "Outputs, attendance, and feedback operations": "결과물·출석·피드백 운영",
          "A classroom booking should feel clear before it feels confirmed.": "강의실 예약은 확정되기 전에도 명확해야 합니다.",
          "Request a room": "공간 요청",
          "Hybrid Setup": "하이브리드 세팅",
          pax: "명",
          "Book Today": "오늘 상담",
          "Three easy starting points for consultation today.": "오늘 바로 상담하기 쉬운 세 가지 시작점",
          "Open Study and Personal Work": "오픈 스터디·개인 업무",
          "AI Class and Seminar": "AI 클래스·세미나",
          "Institution and Team Operations": "기관·팀 정기 운영",
          "Calculate Estimate": "견적 계산",
          "Request Consultation": "상담 요청",
          "Rate Guide": "요금 안내",
          "ICAN proposed rates based on Manila market references.": "마닐라 시장가를 참고한 아이캔 제안 요금",
          "Optional Add-on": "선택 추가 옵션",
          "App build options only for customers who need them.": "필요한 고객에게만 제공하는 앱 제작 옵션",
          "Available Spaces": "사용 가능한 공간",
          "Education Included": "교육 운영 포함",
          "Add class support when the room needs more than seats.": "공간 이상의 지원이 필요할 때 수업 운영을 추가하세요.",
          "Class Flow": "수업 흐름",
          "Intro -> Practice -> Presentation": "도입 -> 실습 -> 발표",
          "Instead of only renting a room, the class flow and presentation output are planned together.": "공간만 빌리는 대신 수업 흐름과 발표 산출물까지 함께 설계합니다.",
          "Materials": "자료",
          "Prompts, Templates, and Worksheets": "프롬프트, 템플릿, 활동지",
          "Class materials and an operations checklist are added so instructors can run the session immediately.": "강사가 바로 운영할 수 있는 수업 자료와 운영 체크리스트를 제공합니다.",
          "Hybrid Ready": "하이브리드 준비",
          "Onsite + Online Participation": "현장 + 온라인 참여",
          "Seating and class flow are prepared for both onsite students and online participants.": "현장 학생과 온라인 참여자를 함께 고려해 좌석과 수업 흐름을 준비합니다.",
          "External instructors and institutions may use only the space, or combine it with ICAN's AI-native class content and onsite operations.": "외부 강사와 기관은 공간만 사용할 수도 있고, 아이캔의 AI Native 수업 콘텐츠와 현장 운영을 결합할 수도 있습니다.",
          "Hands-on AI Class Package": "AI 실전수업 패키지",
          "Runs classes on generative AI use, prompt design, presentation building, and problem-solving projects.": "생성형 AI 활용, 프롬프트 설계, 발표자료 제작, 문제해결 프로젝트를 수업으로 운영합니다.",
          "Space and AI Project": "우주와 AI 프로젝트",
          "An experiential class connecting space technology, future careers, and data exploration into student presentations and outputs.": "우주 기술, 미래 직업, 데이터 탐구를 학생 발표와 결과물로 연결하는 체험형 수업입니다.",
          "Korean TESDA Vocational Training": "한국어 TESDA 직업교육",
          "Korean-based vocational training, interview preparation, workplace expressions, and field-adaptive language training can be operated.": "한국어 기반 직업교육, 인터뷰 준비, 실무 표현, 현장 적응형 언어 훈련을 운영할 수 있습니다.",
          Included: "포함 사항",
          "Space, equipment, operations, and refreshments together.": "공간, 장비, 운영, 리프레시먼트를 한 번에",
          "Classroom Setup": "교육장 세팅",
          "Seats, displays, presentation flow, and small-group tables are prepared for the class purpose.": "수업 목적에 맞춰 좌석, 모니터, 발표 동선, 소그룹 테이블을 준비합니다.",
          "AI Class Options": "AI 수업 옵션",
          "AI-native lectures, project topics, presentation templates, and practice flows are available as options.": "AI Native 강의, 프로젝트 주제, 발표 템플릿, 실습 흐름을 선택형으로 제공합니다.",
          "Coffee and Snacks": "커피와 다과",
          "Light drinks and snacks create an education lounge experience where participants can stay longer.": "간단한 음료와 다과로 오래 머무를 수 있는 교육 라운지 경험을 제공합니다.",
          "Operations Support": "운영 지원",
          "Onsite support covers entry guidance, basic equipment checks, and hybrid class preparation.": "입실 안내, 기본 장비 체크, 온오프 통합 수업 준비를 현장에서 지원합니다.",
          "Custom Request": "맞춤 요청",
          "Need a custom setup? Send a note.": "맞춤 세팅이 필요하면 요청 내용을 보내세요.",
          "Space Consultation Request": "공간 상담 요청",
          "For repeated use, institution programs, or special schedules, share the details and staff will prepare the next step.": "반복 이용, 기관 프로그램, 특별 일정이 필요하면 세부 내용을 보내 주세요. 담당자가 다음 단계를 준비합니다.",
          "Create consultation email": "상담 메일 작성",
          "The consultation summary will appear here as you type.": "입력하면 상담 요약이 여기에 표시됩니다.",
          "Your input is summarized only in the browser.": "입력 내용은 브라우저 안에서만 요약됩니다.",
          "How It Works": "운영 방식",
          "Choose the space, add the class purpose, and operate immediately.": "공간을 고르고, 수업 목적을 붙이고, 바로 운영합니다.",
          "Space and content are packaged together so academies, companies, churches, education startups, and freelance instructors can start AI education.": "학원, 기업, 교회, 교육 스타트업, 프리랜서 강사가 바로 AI 교육을 시작할 수 있도록 공간과 콘텐츠를 함께 구성합니다.",
          "Choose Purpose": "목적 선택",
          "Select the purpose: AI class, vocational training, workshop, or office-style study.": "AI 강의, 직업교육, 워크숍, 오피스형 스터디 중 목적을 선택합니다.",
          "Match the Space": "공간 매칭",
          "Assign the 11F AI Hub or 16F Mentoring Zone based on participants and flow.": "인원과 동선에 맞춰 11F AI 허브 또는 16F 맨투맨 Zone을 배정합니다.",
          "Design Class Options": "수업 옵션 설계",
          "Add AI-native class, space project, or Korean TESDA module when needed.": "필요하면 AI Native 강의, 우주 프로젝트, 한국어 TESDA 모듈을 붙입니다.",
          "Onsite Operations": "현장 운영",
          "Check equipment, entry, coffee and snacks, and online participation setup before starting.": "시작 전 장비, 입실, 커피와 다과, 온라인 참여 환경을 확인합니다.",
          "Contact Flow": "상담 흐름",
          "After consultation, you receive an operation plan.": "상담 후에는 운영안까지 정리해서 드립니다.",
          "After sending an email, you receive more than a simple reply: the team organizes the recommended space, estimated cost, preparation items, and next confirmations.": "이메일을 보내면 단순 답변을 넘어 추천 공간, 예상 비용, 준비 항목, 다음 확인 사항까지 정리해서 안내합니다.",
          "Email Request": "이메일 요청",
          "Send the estimate and request by email.": "견적과 요청 내용을 이메일로 보내기",
          "Use the estimate calculator or request form to send an automatically prepared Korean-English email.": "견적 계산기 또는 요청 양식에서 자동 작성된 한영 이메일을 보낼 수 있습니다.",
          "Create Estimate": "견적 작성",
          "Existing App": "기존 상담 앱",
          "Move to the separate consultation app.": "별도 상담 앱으로 이동하기",
          "Open the external consultation app when detailed consultation or the existing operations app is needed.": "상세 상담이나 기존 운영팀 앱이 필요할 때 외부 상담 앱으로 이동합니다.",
          "Open Consultation App": "상담 앱 열기",
          FAQ: "FAQ",
          "Questions before booking.": "예약 전 자주 묻는 질문",
          "Can I use only the space?": "공간만 사용할 수 있나요?",
          "Can I add AI classes or consulting?": "AI 강의나 컨설팅을 같이 붙일 수 있나요?",
          "How is the booking confirmed?": "예약 확정은 어떻게 하나요?",
          "Can online participants join too?": "온라인 참여자도 함께 운영할 수 있나요?",
          "Yes. Open study or personal work starts at ₱200/hr, and morning, afternoon, or evening block use is also available.": "네. 오픈 스터디나 개인 업무는 ₱200/hr 기준으로 시작하고 오전·오후·저녁 블록 이용도 가능합니다.",
          "Yes. Hands-on AI class, Space and AI project, TESDA Korean vocational training, and personal business consulting can be added as hourly options.": "네. AI 실전수업, 우주·AI 프로젝트, TESDA 한국어 직업교육, 개인별 사업 컨설팅을 시간당 옵션으로 추가할 수 있습니다.",
          "Send the estimate and request from the site, then the operations team confirms the date, participants, space setup, equipment, and refreshments.": "사이트에서 견적과 요청을 보내면 운영팀이 날짜, 인원, 공간 구성, 장비, 다과 옵션을 확인한 뒤 확정합니다.",
          "Yes. For hybrid classes, the team checks the broadcast setup and onsite flow together.": "네. 하이브리드 수업은 송출 환경과 현장 동선을 함께 확인합니다.",
          "Booking Request": "예약 요청",
          "Tell us when you need the room.": "언제 공간이 필요한지 알려주세요.",
          "Submit the first request. Staff reviews availability, room setup, and class format before final confirmation.": "먼저 요청을 보내면 담당자가 가능 시간, 공간 세팅, 운영 형태를 확인한 뒤 최종 확정합니다.",
          Room: "공간",
          Date: "날짜",
          Time: "시간",
          Format: "운영 형태",
          Name: "이름",
          Contact: "연락처",
          Notes: "메모",
          "Dates are confirmed after staff review.": "날짜는 담당자 확인 후 확정됩니다.",
          "Send request": "요청 보내기",
          "All Rights Reserved.": "All Rights Reserved.",
          "Admin Panel": "관리자 패널",
          "Please check the booking details and try again.": "예약 정보를 확인한 뒤 다시 시도해 주세요.",
          "Offline": "오프라인",
          "Online broadcast": "온라인 송출",
          "Hybrid": "온오프 통합",
          "AI lecture / seminar": "AI 강의 / 세미나",
          "1:1 mentoring / small group": "1:1 멘토링 / 소그룹",
          "Korean TESDA vocational training": "한국어 TESDA 직업교육",
          "Office / education workshop": "오피스 / 교육 워크숍",
          "Morning block": "오전 블록",
          "Afternoon block": "오후 블록",
          "Evening block": "저녁 블록",
        };
        const placeholderTranslations = {
          "Your name": "이름",
          "Email, phone, or KakaoTalk": "이메일, 전화번호 또는 카카오톡",
          "Example: 12-person AI workshop, Saturday afternoon, coffee included": "예: 12명 AI 워크숍, 토요일 오후, 커피 포함",
          "Academy, company, community": "학원, 회사, 커뮤니티",
          "Email or KakaoTalk ID": "이메일 또는 카카오톡 ID",
          "Example: 12-person AI workshop, Saturday afternoon, coffee and snacks included": "예: 12명 AI 워크숍, 토요일 오후, 커피와 다과 포함",
        };
        const originalTextNodes = new WeakMap();
        const originalPlaceholders = new WeakMap();

        const prefersReducedMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches ?? false;
        const hasGSAP = typeof window.gsap !== "undefined";
        const hasScrollTrigger = typeof window.ScrollTrigger !== "undefined";

        function applyBrand() {
          document.querySelectorAll("[data-brand-text]").forEach((el) => (el.textContent = BRAND.name));
          document.querySelectorAll("[data-brand-short]").forEach((el) => (el.textContent = BRAND.short));
          const taglineEl = document.querySelector("[data-brand-tagline]");
          if (taglineEl) taglineEl.textContent = activeLang === "ko" ? "강의실 예약 시스템" : BRAND.tagline;
          const locEl = document.querySelector("[data-brand-location]");
          if (locEl) locEl.textContent = BRAND.location;
          const yearEl = document.querySelector("[data-year]");
          if (yearEl) yearEl.textContent = String(new Date().getFullYear());
        }

        function normalizeText(text) {
          return text.replace(/\s+/g, " ").trim();
        }

        function translateStaticText() {
          document.documentElement.lang = activeLang;
          document.querySelectorAll("#langToggle, #mobileLangToggle").forEach((button) => {
            button.textContent = activeLang === "ko" ? "EN" : "KO";
            button.setAttribute("aria-pressed", activeLang === "ko" ? "true" : "false");
          });

          const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
              const parent = node.parentElement;
              if (!parent || ["SCRIPT", "STYLE"].includes(parent.tagName)) return NodeFilter.FILTER_REJECT;
              return node.nodeValue.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            },
          });
          const nodes = [];
          while (walker.nextNode()) nodes.push(walker.currentNode);

          nodes.forEach((node) => {
            if (!originalTextNodes.has(node)) originalTextNodes.set(node, node.nodeValue);
            const original = originalTextNodes.get(node);
            const leading = original.match(/^\s*/)?.[0] || "";
            const trailing = original.match(/\s*$/)?.[0] || "";
            const core = normalizeText(original);
            const translated = staticTranslations[core];
            node.nodeValue = activeLang === "ko" && translated ? `${leading}${translated}${trailing}` : original;
          });

          document.querySelectorAll("[placeholder]").forEach((element) => {
            if (!originalPlaceholders.has(element)) originalPlaceholders.set(element, element.getAttribute("placeholder"));
            const original = originalPlaceholders.get(element);
            element.setAttribute("placeholder", activeLang === "ko" && placeholderTranslations[original] ? placeholderTranslations[original] : original);
          });
        }

        function setLanguage(lang) {
          activeLang = lang;
          localStorage.setItem("icaneduspace-lang", activeLang);
          document.title = activeLang === "ko" ? "아이캔 에듀스페이스 예약" : "ICAN Eduspace Booking";
          applyBrand();
          translateStaticText();
          window.updatePlannerEstimate?.();
          window.updateLeadSummary?.();
        }

        function initLanguageToggle() {
          document.querySelectorAll("#langToggle, #mobileLangToggle").forEach((button) => {
            button.addEventListener("click", () => setLanguage(activeLang === "ko" ? "en" : "ko"));
          });
          setLanguage(activeLang);
        }

        function applyCTALinks() {
          document.querySelectorAll('[data-cta="primary"]').forEach((a) => a.setAttribute("href", CTA.primary));
          document.querySelectorAll('[data-cta="secondary"]').forEach((a) => a.setAttribute("href", CTA.secondary));
        }

        function initMobileMenu() {
          const menuRoot = document.getElementById("mobileMenu");
          const menuBtn = document.getElementById("menuBtn");
          const menuCloseBtn = document.getElementById("menuCloseBtn");
          const panel = menuRoot?.querySelector("[data-menu-panel]");
          const backdrop = menuRoot?.querySelector("[data-menu-backdrop]");
          const menuLinks = menuRoot?.querySelectorAll("[data-menu-link]") || [];
          let menuOpen = false;
          const lockScroll = (lock) => { document.documentElement.style.overflow = lock ? "hidden" : ""; };
          const setMenuA11y = (open) => { menuBtn?.setAttribute("aria-expanded", String(open)); };
          const openMenu = () => {
            if (!menuRoot || !panel || !backdrop || menuOpen) return;
            menuOpen = true;
            setMenuA11y(true);
            lockScroll(true);
            menuRoot.style.pointerEvents = "auto";
            if (!prefersReducedMotion && hasGSAP) {
              window.gsap.to(backdrop, { opacity: 1, duration: 0.25, ease: "power2.out" });
              window.gsap.to(panel, { opacity: 1, y: 0, duration: 0.35, ease: "power3.out", overwrite: true });
            } else {
              backdrop.style.opacity = "1";
              panel.style.opacity = "1";
              panel.style.transform = "translateY(0)";
            }
          };
          const closeMenu = () => {
            if (!menuRoot || !panel || !backdrop || !menuOpen) return;
            menuOpen = false;
            setMenuA11y(false);
            lockScroll(false);
            if (!prefersReducedMotion && hasGSAP) {
              window.gsap.to(backdrop, { opacity: 0, duration: 0.2, ease: "power2.out" });
              window.gsap.to(panel, { opacity: 0, y: -24, duration: 0.25, ease: "power2.in", onComplete: () => { menuRoot.style.pointerEvents = "none"; } });
            } else {
              backdrop.style.opacity = "0";
              panel.style.opacity = "0";
              panel.style.transform = "translateY(-24px)";
              menuRoot.style.pointerEvents = "none";
            }
          };
          menuBtn?.addEventListener("click", openMenu);
          menuCloseBtn?.addEventListener("click", closeMenu);
          backdrop?.addEventListener("click", closeMenu);
          menuLinks.forEach((a) => a.addEventListener("click", closeMenu));
          window.addEventListener("keydown", (e) => { if (e.key === "Escape") closeMenu(); });
        }

        function initNavbarScroll() {
          const nav = document.getElementById("navbar");
          if (!nav) return;
          const onScroll = () => {
            if (window.scrollY > 50) nav.classList.add("bg-brand-dark/90", "backdrop-blur-md", "shadow-lg");
            else nav.classList.remove("bg-brand-dark/90", "backdrop-blur-md", "shadow-lg");
          };
          window.addEventListener("scroll", onScroll, { passive: true });
          onScroll();
        }

        function showFallback() {
          document.querySelector(".loader")?.remove();
          document.querySelectorAll(".reveal-on-load").forEach((el) => {
            el.style.opacity = "1";
            el.style.transform = "none";
          });
        }

        function initAnimations() {
          if (prefersReducedMotion || !hasGSAP) {
            showFallback();
            return;
          }
          if (hasScrollTrigger) window.gsap.registerPlugin(window.ScrollTrigger);
          const tl = window.gsap.timeline();
          tl.to(".loader-text", { opacity: 1, y: 0, duration: 0.9, ease: "power2.out" })
            .to(".loader", { y: "-100%", duration: 0.9, delay: 0.35, ease: "power4.inOut" })
            .to(".reveal-on-load", { opacity: 1, y: 0, duration: 0.7, stagger: 0.12, ease: "power2.out" }, "-=0.15");

          if (!hasScrollTrigger) return;
          document.querySelectorAll('[data-reveal="left"]').forEach((el) => {
            window.gsap.from(el, { scrollTrigger: { trigger: el, start: "top 80%", toggleActions: "play none none reverse" }, x: -50, opacity: 0, duration: 0.9, ease: "power3.out" });
          });
          document.querySelectorAll('[data-reveal="right"]').forEach((el) => {
            window.gsap.from(el, { scrollTrigger: { trigger: el, start: "top 80%", toggleActions: "play none none reverse" }, x: 50, opacity: 0, duration: 0.9, delay: 0.1, ease: "power3.out" });
          });
          window.gsap.to(".hero-img", { scrollTrigger: { trigger: "#home", start: "top top", end: "bottom top", scrub: true }, y: 90, scale: 1.08 });
        }

        function initPlanner() {
          const plannerIds = ["planPurpose", "planSpace", "planPeople", "planHours", "planClass", "planRefresh"];
          if (!plannerIds.every((id) => document.getElementById(id))) return;

          const money = new Intl.NumberFormat("en-PH", {
            style: "currency",
            currency: "PHP",
            maximumFractionDigits: 0,
          });
          const labelSets = {
            en: {
              seminar: "AI lecture / seminar",
              mentoring: "1:1 mentoring / small group",
              tesda: "Korean TESDA vocational training",
              office: "Office / education workshop",
              dayuse: "Place-use customer",
              hub: "11F AI Hub",
              mentor: "16F Mentoring Zone",
              dual: "Dual campus access",
              basic: "Basic coffee and snacks",
              plus: "Premium refreshments",
              spaceRental: "Space Rental",
              aiReady: "AI Class Ready",
              institution: "Institution Partner",
              estimatePrefix: "Estimated consultation price",
              andUp: "and up",
              participantsFor: "participants for",
              included: "included",
              serviceRate: "Service rate",
              copied: "Copied. You can paste it into email or messenger.",
              copyBlocked: "Copy was blocked. Please use the email button.",
            },
            ko: {
              seminar: "AI 강의 / 세미나",
              mentoring: "1:1 멘토링 / 소그룹",
              tesda: "한국어 TESDA 직업교육",
              office: "오피스 / 교육 워크숍",
              dayuse: "장소 이용 고객",
              hub: "11F AI 허브",
              mentor: "16F 맨투맨 Zone",
              dual: "듀얼 캠퍼스 전체",
              basic: "기본 커피와 다과",
              plus: "프리미엄 다과",
              spaceRental: "공간 기본 임대",
              aiReady: "AI 클래스 준비",
              institution: "교육기관 파트너",
              estimatePrefix: "예상 상담가",
              andUp: "부터",
              participantsFor: "명 규모",
              included: "포함",
              serviceRate: "서비스 요금",
              copied: "복사되었습니다. 이메일이나 메신저에 붙여넣을 수 있습니다.",
              copyBlocked: "복사가 차단되었습니다. 이메일 버튼을 사용해 주세요.",
            },
          };
          const durationLabels = {
            en: {
              "1": "1 hour",
              "2": "2 hours",
              "4": "half-day 4 hours",
              "8": "full-day 8 hours",
              morning: "morning block",
              afternoon: "afternoon block",
              evening: "evening block",
            },
            ko: {
              "1": "1시간",
              "2": "2시간",
              "4": "반일 4시간",
              "8": "종일 8시간",
              morning: "오전 블록",
              afternoon: "오후 블록",
              evening: "저녁 블록",
            },
          };
          const estimateName = document.getElementById("estimateName");
          const estimatePrice = document.getElementById("estimatePrice");
          const estimateItems = document.getElementById("estimateItems");
          const estimateMail = document.getElementById("estimateMail");
          const copyEstimate = document.getElementById("copyEstimate");
          const estimateStatus = document.getElementById("estimateStatus");
          const plannerHeadline = document.getElementById("plannerHeadline");
          const plannerLead = document.getElementById("plannerLead");
          const plannerBadge = document.getElementById("plannerBadge");
          let currentEstimateBody = "";

          function optionText(id) {
            const select = document.getElementById(id);
            return select.options[select.selectedIndex].text;
          }

          function updateEstimate() {
            const labels = labelSets[activeLang];
            const purpose = document.getElementById("planPurpose").value;
            const space = document.getElementById("planSpace").value;
            const people = Number(document.getElementById("planPeople").value);
            const duration = document.getElementById("planHours").value;
            const isBlock = ["morning", "afternoon", "evening"].includes(duration);
            const hours = isBlock ? 1 : Number(duration);
            const classFee = Number(document.getElementById("planClass").value);
            const refresh = document.getElementById("planRefresh").value;
            const baseSpaceRate = { dayuse: 200, mentor: 500, hub: people > 12 ? 1200 : 800, dual: 1500 }[space];
            const spaceRate = isBlock && space === "dayuse" ? 500 : people > 24 ? Math.max(baseSpaceRate, 1500) : baseSpaceRate;
            const refreshFee = refresh === "plus" ? people * 100 : 0;
            const serviceFee = classFee * hours;
            const total = (isBlock && space === "dayuse" ? spaceRate : spaceRate * hours) + refreshFee + serviceFee;
            const packageName = space === "dual" || hours >= 8 || people > 24 ? labels.institution : classFee > 0 ? labels.aiReady : labels.spaceRental;
            const durationText = durationLabels[activeLang][duration];
            const summary = [
              activeLang === "ko"
                ? (isBlock ? `${labels[space]} ${durationText}` : `${labels[space]} ${durationText}`)
                : (isBlock ? `${labels[space]} ${durationText}` : `${labels[space]} for ${hours} ${hours === 1 ? "hour" : "hours"}`),
              activeLang === "ko" ? `${people}${labels.participantsFor} ${labels[purpose]}` : `${people} ${labels.participantsFor} ${labels[purpose]}`,
              optionText("planClass"),
              `${labels[refresh]} ${labels.included}`,
              `${labels.serviceRate}: ${isBlock && space === "dayuse" ? `${money.format(spaceRate)}/block` : `${money.format(spaceRate)}/hr`}${classFee ? ` + ${money.format(classFee)}/hr` : ""}`,
            ];

            estimateName.textContent = packageName;
            estimatePrice.textContent = activeLang === "ko" ? `${labels.estimatePrefix} ${money.format(total)}${labels.andUp}` : `${labels.estimatePrefix}: ${money.format(total)} ${labels.andUp}`;
            estimateItems.innerHTML = summary.map((item) => `<li>${item}</li>`).join("");
            if (plannerBadge) plannerBadge.textContent = packageName;
            if (plannerHeadline) {
              const headline = {
                [labels.spaceRental]: activeLang === "ko" ? ["먼저", "공간 계획"] : ["Start with a", "space plan"],
                [labels.aiReady]: activeLang === "ko" ? ["AI 클래스", "계획 만들기"] : ["Build an", "AI class plan"],
                [labels.institution]: activeLang === "ko" ? ["기관 운영", "전체 계획"] : ["Plan a full", "institution setup"],
              }[packageName] || (activeLang === "ko" ? ["먼저", "공간 계획"] : ["Start with a", "space plan"]);
              plannerHeadline.innerHTML = `${headline[0]} <br><span class="text-stroke text-6xl md:text-8xl">${headline[1]}</span>`;
              originalTextNodes.delete(plannerHeadline.firstChild);
            }
            if (plannerLead) {
              plannerLead.textContent = activeLang === "ko"
                ? `${summary[0]}. ${summary[1]}. 담당자 확정 전 예상 금액은 ${money.format(total)}부터입니다.`
                : `${summary[0]}. ${summary[1]}. The planner estimates ${money.format(total)} and up before staff confirmation.`;
            }
            currentEstimateBody = [
              activeLang === "ko" ? "교육공유오피스 견적 상담 요청" : "Education Shared Office estimate request",
              "",
              `${activeLang === "ko" ? "추천 패키지" : "Recommended package"}: ${packageName}`,
              `${activeLang === "ko" ? "예상 견적" : "Estimated price"}: ${money.format(total)} ${labels.andUp}`,
              ...summary.map((item) => `- ${item}`),
            ].join("\n");
          }

          plannerIds.forEach((id) => document.getElementById(id).addEventListener("change", updateEstimate));
          window.updatePlannerEstimate = updateEstimate;
          estimateMail?.addEventListener("click", () => {
            window.location.href = `mailto:icanacademy@naver.com?subject=${encodeURIComponent(activeLang === "ko" ? "교육공유오피스 견적 상담" : "Education Shared Office estimate request")}&body=${encodeURIComponent(currentEstimateBody)}`;
          });
          copyEstimate?.addEventListener("click", async () => {
            try {
              await navigator.clipboard.writeText(currentEstimateBody);
              estimateStatus.textContent = labels.copied;
            } catch {
              estimateStatus.textContent = labels.copyBlocked;
            }
          });
          updateEstimate();
        }

        function initLeadForm() {
          const leadForm = document.getElementById("leadForm");
          if (!leadForm) return;
          const leadName = document.getElementById("leadName");
          const leadOrg = document.getElementById("leadOrg");
          const leadContact = document.getElementById("leadContact");
          const leadMemo = document.getElementById("leadMemo");
          const leadSummary = document.getElementById("leadSummary");
          const leadStatus = document.getElementById("leadStatus");
          let leadBody = "";

          function updateLead() {
            const empty = activeLang === "ko" ? "미입력" : "Not entered";
            const memoEmpty = activeLang === "ko" ? "요청 내용을 입력해 주세요." : "Please enter your request details.";
            const name = leadName.value.trim() || empty;
            const org = leadOrg.value.trim() || empty;
            const contact = leadContact.value.trim() || empty;
            const memo = leadMemo.value.trim() || memoEmpty;
            leadSummary.textContent = `${activeLang === "ko" ? "상담 요약" : "Consultation summary"}: ${name} / ${org} / ${contact} / ${memo}`;
            leadBody = [
              activeLang === "ko" ? "교육공유오피스 장소 이용 상담 요청" : "Education Shared Office place-use consultation request",
              "",
              `${activeLang === "ko" ? "이름" : "Name"}: ${name}`,
              `${activeLang === "ko" ? "기관 / 팀" : "Organization / team"}: ${org}`,
              `${activeLang === "ko" ? "연락처" : "Contact"}: ${contact}`,
              "",
              activeLang === "ko" ? "요청 내용:" : "Request details:",
              memo,
            ].join("\n");
          }

          [leadName, leadOrg, leadContact, leadMemo].forEach((el) => el.addEventListener("input", updateLead));
          window.updateLeadSummary = updateLead;
          leadForm.addEventListener("submit", (event) => {
            event.preventDefault();
            updateLead();
            if (!leadName.value.trim() || !leadContact.value.trim()) {
              leadStatus.textContent = activeLang === "ko" ? "이름과 연락처를 입력하면 상담 메일을 만들 수 있습니다." : "Enter your name and contact to create the consultation email.";
              (!leadName.value.trim() ? leadName : leadContact).focus();
              return;
            }
            window.location.href = `mailto:icanacademy@naver.com?subject=${encodeURIComponent(activeLang === "ko" ? "교육공유오피스 상담" : "Education Shared Office consultation")}&body=${encodeURIComponent(leadBody)}`;
          });
          updateLead();
        }

        document.addEventListener("DOMContentLoaded", () => {
          applyBrand();
          applyCTALinks();
          initMobileMenu();
          initNavbarScroll();
          initPlanner();
          initLeadForm();
          initLanguageToggle();
          try { initAnimations(); } catch { showFallback(); }
        });
      })();
    </script>
  </body>
</html>
