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

    <style>
        [x-cloak] { display:none !important; }
        body { font-family:'Montserrat',sans-serif; color:#0D1C4C; background:#F5F7FB; -webkit-font-smoothing:antialiased; }

        /* Sidebar */
        .side-nav { background:linear-gradient(180deg,#0D1C4C,#07112F); color:#fff; }
        .side-link { color:rgba(255,255,255,0.72); padding:0.7rem 1.25rem; display:flex; align-items:center; gap:0.75rem; font-size:0.78rem; letter-spacing:0.14em; text-transform:uppercase; border-left:2px solid transparent; transition:all 200ms ease; }
        .side-link:hover { color:#D9A72F; background:rgba(217,167,47,0.06); }
        .side-link.active { color:#D9A72F; border-left-color:#D9A72F; background:rgba(217,167,47,0.10); }
        .side-count { margin-left:auto; min-width:1.4rem; padding:0.05rem 0.35rem; border:1px solid rgba(217,167,47,0.5); background:rgba(217,167,47,0.16); color:#fff; text-align:center; font-size:0.68rem; letter-spacing:0; }
        .side-group-label { color:rgba(217,167,47,0.85); font-size:0.62rem; letter-spacing:0.28em; text-transform:uppercase; padding:1rem 1.25rem 0.5rem; }

        /* Surfaces */
        .card { background:#fff; border:1px solid rgba(13,28,76,0.06); box-shadow:0 4px 18px rgba(7,17,47,0.04); border-radius:2px; }
        .card-soft { background:#fff; border-radius:2px; }
        .eyebrow { color:#D9A72F; letter-spacing:0.3em; text-transform:uppercase; font-size:0.7rem; font-weight:600; }
        .wrap-anywhere { overflow-wrap:anywhere; word-break:break-word; }

        /* Buttons */
        .btn-gold { background:#D9A72F; color:#07112f; padding:0.85rem 2rem; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; font-size:0.72rem; display:inline-flex; align-items:center; justify-content:center; transition:all 250ms ease; }
        .btn-gold:hover { background:#07112f; color:#D9A72F; }
        .btn-ghost { border:1px solid #0D1C4C; color:#0D1C4C; padding:0.7rem 1.5rem; font-size:0.72rem; letter-spacing:0.18em; text-transform:uppercase; transition:all 250ms ease; }
        .btn-ghost:hover { background:#0D1C4C; color:#fff; }
        .btn-navy { background:#0D1C4C; color:#fff; padding:0.7rem 1.5rem; font-size:0.72rem; letter-spacing:0.18em; text-transform:uppercase; }
        .btn-navy:hover { background:#07112f; }
        .link-gold { color:#0D1C4C; }
        .link-gold:hover { color:#D9A72F; }
        .action-menu-button { width:2.25rem; height:2.25rem; border:1px solid rgba(13,28,76,0.22); background:#fff; color:#0D1C4C; display:inline-flex; align-items:center; justify-content:center; transition:background 200ms ease,border-color 200ms ease,color 200ms ease; }
        .action-menu-button:hover, .action-menu-button[aria-expanded='true'] { border-color:#0D1C4C; background:rgba(13,28,76,0.06); color:#0D1C4C; }
        .action-menu-panel { position:fixed; z-index:80; width:12rem; border:1px solid rgba(13,28,76,0.14); background:#fff; box-shadow:0 18px 40px rgba(7,17,47,0.16); text-align:left; overflow:visible; }
        .action-menu-item { position:relative; width:100%; min-height:2.25rem; padding:0.7rem 0.9rem; color:#0D1C4C; display:flex; align-items:center; font-size:0.68rem; letter-spacing:0.12em; line-height:1.1; text-transform:uppercase; transition:background 160ms ease,color 160ms ease; }
        .action-menu-item:hover { background:rgba(13,28,76,0.06); color:#0D1C4C; }
        .action-floating-tooltip { position:fixed; z-index:120; max-width:14rem; background:#07112f; color:#fff; padding:0.55rem 0.7rem; box-shadow:0 14px 30px rgba(7,17,47,0.22); font-size:0.68rem; font-weight:500; letter-spacing:0; line-height:1.35; text-transform:none; pointer-events:none; }
        .action-menu-item-success { color:#047857; }
        .action-menu-item-success:hover { background:rgba(16,185,129,0.08); color:#047857; }
        .action-menu-item-danger { color:#b91c1c; }
        .action-menu-item-danger:hover { background:rgba(185,28,28,0.06); color:#b91c1c; }

        /* Inputs */
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
            padding:0.65rem 0.85rem;
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

        /* Status badges */
        .status-badge { display:inline-block; padding:0.25rem 0.7rem; font-size:0.65rem; letter-spacing:0.2em; text-transform:uppercase; border:1px solid; border-radius:2px; }
        .status-pending   { color:#a37520; border-color:rgba(217,167,47,0.55); background:rgba(217,167,47,0.10); }
        .status-approved  { color:#047857; border-color:rgba(16,185,129,0.45); background:rgba(16,185,129,0.10); }
        .status-rejected  { color:#b91c1c; border-color:rgba(239,68,68,0.45); background:rgba(239,68,68,0.08); }
        .status-cancelled { color:rgba(13,28,76,0.55); border-color:rgba(13,28,76,0.20); background:rgba(13,28,76,0.04); }
        .status-completed { color:#1d4ed8; border-color:rgba(59,130,246,0.40); background:rgba(59,130,246,0.08); }

        /* Room thumbnail card */
        .room-card { position:relative; overflow:hidden; min-height:280px; transition:transform 250ms ease, box-shadow 250ms ease; border-radius:2px; }
        .room-card:hover { transform:translateY(-2px); box-shadow:0 18px 40px rgba(7,17,47,0.18); }
        .room-card .img-bg { position:absolute; inset:0; background-size:cover; background-position:center; transition:transform 600ms ease; }
        .room-card:hover .img-bg { transform:scale(1.05); }
        .room-card .scrim { position:absolute; inset:0; background:linear-gradient(180deg, rgba(7,17,47,0) 0%, rgba(7,17,47,0.55) 60%, rgba(7,17,47,0.92) 100%); }

        /* Topbar (mobile) */
        .topbar { background:#0D1C4C; color:#fff; }
        .app-topbar { position:sticky; top:0; z-index:40; background:#fff; border-bottom:1px solid rgba(13,28,76,0.10); box-shadow:0 8px 24px rgba(7,17,47,0.04); }
        .app-topbar-inner { max-width:80rem; margin:0 auto; padding:0.85rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:0.85rem; }
        .app-search { position:relative; flex:1 1 34rem; max-width:44rem; min-width:14rem; }
        .app-search input { height:2.65rem; padding-left:2.6rem !important; padding-right:3rem !important; background:#FAFBFD !important; border-color:rgba(13,28,76,0.14) !important; }
        .app-search-icon { position:absolute; left:0.9rem; top:50%; width:1rem; height:1rem; transform:translateY(-50%); color:rgba(13,28,76,0.45); pointer-events:none; }
        .app-search-button { position:absolute; right:0.25rem; top:0.25rem; width:2.15rem; height:2.15rem; display:inline-flex; align-items:center; justify-content:center; background:#0D1C4C; color:#fff; transition:background 200ms ease,color 200ms ease; }
        .app-search-button:hover { background:#D9A72F; color:#07112f; }
        .app-search-suggestions { position:absolute; top:calc(100% + 0.55rem); left:0; right:0; max-height:min(31rem, calc(100vh - 8rem)); overflow:auto; border:1px solid rgba(13,28,76,0.12); background:#fff; color:#0D1C4C; box-shadow:0 24px 60px rgba(7,17,47,0.16); z-index:80; }
        .app-search-suggestions-head { padding:0.75rem 0.85rem; border-bottom:1px solid rgba(13,28,76,0.08); background:#FAFBFD; color:rgba(13,28,76,0.58); font-size:0.62rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; }
        .app-search-correction { width:100%; display:flex; align-items:center; justify-content:flex-start; gap:0.35rem; padding:0.75rem 0.85rem; border-bottom:1px solid rgba(13,28,76,0.08); background:rgba(217,167,47,0.09); color:rgba(13,28,76,0.68); font-size:0.72rem; text-align:left; transition:background 160ms ease,color 160ms ease; }
        .app-search-correction:hover { background:rgba(217,167,47,0.16); color:#0D1C4C; }
        .app-search-correction strong { color:#0D1C4C; font-weight:800; }
        .app-search-section { padding:0.7rem; }
        .app-search-section + .app-search-section { border-top:1px solid rgba(13,28,76,0.08); }
        .app-search-section-title { margin:0 0 0.35rem; color:rgba(13,28,76,0.48); font-size:0.58rem; font-weight:800; letter-spacing:0.16em; text-transform:uppercase; }
        .app-search-suggestion { display:grid; grid-template-columns:minmax(0, 1fr) auto; gap:0.2rem 0.65rem; align-items:center; padding:0.62rem 0.65rem; border:1px solid transparent; color:#0D1C4C; text-decoration:none; transition:background 160ms ease,border-color 160ms ease,color 160ms ease; }
        .app-search-suggestion:hover, .app-search-suggestion.active { border-color:rgba(217,167,47,0.42); background:rgba(217,167,47,0.08); color:#07112f; }
        .app-search-suggestion-label { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.78rem; font-weight:700; }
        .app-search-suggestion-meta { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:rgba(13,28,76,0.54); font-size:0.64rem; text-align:right; }
        .app-search-empty { padding:1rem; color:rgba(13,28,76,0.58); font-size:0.72rem; text-align:center; }
        .app-search-footer { display:flex; justify-content:flex-end; padding:0.65rem 0.75rem; border-top:1px solid rgba(13,28,76,0.08); background:#FAFBFD; }
        .app-search-full { color:#0D1C4C; font-size:0.62rem; font-weight:800; letter-spacing:0.14em; text-transform:uppercase; }
        .app-search-full:hover { color:#D9A72F; }
        .app-topbar-tools { display:flex; align-items:center; justify-content:flex-end; gap:0.5rem; flex:0 0 auto; }
        .app-topbar-tool { position:relative; min-width:2.65rem; height:2.65rem; padding:0 0.85rem; display:inline-flex; align-items:center; justify-content:center; gap:0.55rem; border:1px solid rgba(13,28,76,0.16); background:#fff; color:#0D1C4C; transition:background 200ms ease,border-color 200ms ease,color 200ms ease; }
        .app-topbar-tool:hover, .app-topbar-tool.active { border-color:#0D1C4C; background:#0D1C4C; color:#fff; }
        .app-topbar-tool svg { width:1.05rem; height:1.05rem; flex:0 0 1.05rem; }
        .app-topbar-tool-label { font-size:0.66rem; font-weight:600; letter-spacing:0.16em; line-height:1; text-transform:uppercase; white-space:nowrap; }
        .app-topbar-badge { position:absolute; top:-0.4rem; right:-0.35rem; min-width:1.35rem; height:1.35rem; padding:0 0.3rem; border:2px solid #fff; background:#D9A72F; color:#07112f; display:inline-flex; align-items:center; justify-content:center; font-size:0.62rem; font-weight:700; letter-spacing:0; }
        .app-notification-menu { position:relative; }
        .app-notification-popover { position:absolute; top:calc(100% + 0.65rem); right:0; width:min(23rem, calc(100vw - 2rem)); border:1px solid rgba(13,28,76,0.12); background:#fff; color:#0D1C4C; box-shadow:0 24px 60px rgba(7,17,47,0.18); z-index:70; }
        .app-notification-card { padding:1rem; }
        .app-notification-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding-bottom:0.8rem; border-bottom:1px solid rgba(13,28,76,0.08); }
        .app-notification-title { font-family:'Cormorant Garamond',serif; font-size:1.45rem; line-height:1; color:#0D1C4C; }
        .app-notification-subtitle { margin-top:0.25rem; font-size:0.64rem; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; color:rgba(13,28,76,0.54); }
        .app-notification-action { min-height:2rem; border:1px solid rgba(13,28,76,0.16); padding:0 0.65rem; background:#FAFBFD; color:#0D1C4C; font-size:0.62rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; transition:background 160ms ease,border-color 160ms ease,color 160ms ease; }
        .app-notification-action:hover { border-color:#0D1C4C; background:#0D1C4C; color:#fff; }
        .app-notification-stats { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.5rem; margin-top:0.85rem; }
        .app-notification-stat { border:1px solid rgba(13,28,76,0.08); background:#FAFBFD; padding:0.65rem 0.7rem; }
        .app-notification-stat strong { display:block; font-family:'Cormorant Garamond',serif; font-size:1.45rem; line-height:1; color:#0D1C4C; }
        .app-notification-stat span { display:block; margin-top:0.3rem; color:rgba(13,28,76,0.52); font-size:0.58rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; }
        .app-notification-list { display:grid; gap:0.45rem; margin-top:0.85rem; }
        .app-notification-item { display:grid; grid-template-columns:auto minmax(0, 1fr) auto; gap:0.35rem 0.55rem; align-items:start; padding:0.65rem 0.7rem; border:1px solid rgba(13,28,76,0.08); background:#FAFBFD; color:#0D1C4C; }
        .app-notification-item.is-unread { border-color:rgba(217,167,47,0.34); background:rgba(217,167,47,0.08); }
        .app-notification-dot { grid-row:1 / span 2; width:0.55rem; height:0.55rem; margin-top:0.24rem; background:#D9A72F; }
        .app-notification-item.is-read .app-notification-dot { background:rgba(13,28,76,0.22); }
        .app-notification-copy { min-width:0; }
        .app-notification-subject { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.72rem; font-weight:700; color:#0D1C4C; }
        .app-notification-message { margin-top:0.2rem; color:rgba(13,28,76,0.62); font-size:0.66rem; line-height:1.35; }
        .app-notification-meta { margin-top:0.35rem; color:rgba(13,28,76,0.42); font-size:0.58rem; font-weight:700; letter-spacing:0.10em; text-transform:uppercase; }
        .app-notification-read { color:#0D1C4C; font-size:0.58rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; }
        .app-notification-read:hover { color:#D9A72F; }
        .app-notification-empty { margin-top:0.85rem; border:1px dashed rgba(13,28,76,0.18); padding:1rem; color:rgba(13,28,76,0.58); font-size:0.72rem; text-align:center; }
        .app-notification-footer { display:flex; align-items:center; justify-content:space-between; gap:0.85rem; margin-top:0.85rem; padding-top:0.8rem; border-top:1px solid rgba(13,28,76,0.08); }
        .app-notification-link { color:#0D1C4C; font-size:0.64rem; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; }
        .app-notification-link:hover { color:#D9A72F; }
        .app-calendar-menu { position:relative; }
        .app-calendar-popover { position:absolute; top:calc(100% + 0.65rem); right:0; width:min(22.5rem, calc(100vw - 2rem)); border:1px solid rgba(13,28,76,0.12); background:#fff; color:#0D1C4C; box-shadow:0 24px 60px rgba(7,17,47,0.18); z-index:70; }
        .app-calendar-card { padding:1rem; }
        .app-calendar-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding-bottom:0.8rem; border-bottom:1px solid rgba(13,28,76,0.08); }
        .app-calendar-title { font-family:'Cormorant Garamond',serif; font-size:1.45rem; line-height:1; color:#0D1C4C; }
        .app-calendar-subtitle { margin-top:0.25rem; font-size:0.64rem; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; color:rgba(13,28,76,0.54); }
        .app-calendar-controls { display:flex; align-items:center; gap:0.35rem; }
        .app-calendar-nav { width:2rem; height:2rem; border:1px solid rgba(13,28,76,0.16); background:#FAFBFD; color:#0D1C4C; display:inline-flex; align-items:center; justify-content:center; font-size:1.1rem; line-height:1; transition:background 160ms ease,border-color 160ms ease,color 160ms ease; }
        .app-calendar-nav:hover { border-color:#0D1C4C; background:#0D1C4C; color:#fff; }
        .app-calendar-weekdays, .app-calendar-grid { display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); }
        .app-calendar-weekdays { gap:0.15rem; padding:0.9rem 0 0.45rem; color:rgba(13,28,76,0.45); font-size:0.62rem; font-weight:700; letter-spacing:0.08em; text-align:center; text-transform:uppercase; }
        .app-calendar-grid { gap:0.2rem 0; }
        .app-calendar-day-slot { position:relative; min-height:2.35rem; display:flex; align-items:center; justify-content:center; }
        .app-calendar-day-wrap { position:relative; width:100%; display:flex; align-items:center; justify-content:center; }
        .app-calendar-day { position:relative; width:2.05rem; height:2.05rem; border:1px solid transparent; background:transparent; color:#0D1C4C; display:inline-flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:600; line-height:1; transition:transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease; }
        .app-calendar-day:hover, .app-calendar-day:focus-visible { transform:translateY(-1px); outline:none; box-shadow:0 8px 16px rgba(7,17,47,0.10); }
        .app-calendar-day.is-today { border-color:#D9A72F; background:rgba(217,167,47,0.12); color:#07112f; }
        .app-calendar-day.has-events { border-color:#fff; color:#fff; box-shadow:0 6px 14px rgba(7,17,47,0.10); }
        .app-calendar-day.state-pending { background:#D9A72F; color:#07112f; }
        .app-calendar-day.state-approved { background:#10b981; }
        .app-calendar-day.state-neutral { background:#6b7280; }
        .app-calendar-day.is-today.has-events { box-shadow:0 0 0 2px rgba(217,167,47,0.36), 0 8px 16px rgba(7,17,47,0.12); }
        .app-calendar-day-number { position:relative; z-index:2; }
        .app-calendar-day-count { position:absolute; right:-0.22rem; top:-0.22rem; min-width:0.95rem; height:0.95rem; padding:0 0.16rem; border:1px solid #fff; background:#07112f; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:0.52rem; font-weight:700; line-height:1; }
        .app-calendar-tooltip { position:absolute; z-index:90; min-width:12rem; max-width:16rem; border:1px solid rgba(13,28,76,0.12); background:#fff; padding:0.7rem; box-shadow:0 18px 40px rgba(7,17,47,0.20); text-align:left; }
        .app-calendar-tooltip.above { bottom:100%; margin-bottom:0.35rem; }
        .app-calendar-tooltip.below { top:100%; margin-top:0.35rem; }
        .app-calendar-tooltip.left { left:0; }
        .app-calendar-tooltip.center { left:50%; transform:translateX(-50%); }
        .app-calendar-tooltip.right { right:0; }
        .app-calendar-tooltip-row { display:flex; align-items:flex-start; gap:0.45rem; font-size:0.67rem; font-weight:600; line-height:1.35; color:rgba(13,28,76,0.76); }
        .app-calendar-tooltip-row + .app-calendar-tooltip-row { margin-top:0.4rem; }
        .app-calendar-dot { width:0.5rem; height:0.5rem; margin-top:0.22rem; flex:0 0 0.5rem; background:#D9A72F; }
        .app-calendar-status { display:flex; align-items:center; justify-content:space-between; gap:0.8rem; min-height:2.25rem; margin-top:0.85rem; padding-top:0.8rem; border-top:1px solid rgba(13,28,76,0.08); color:rgba(13,28,76,0.64); font-size:0.72rem; line-height:1.35; }
        .app-calendar-bookings { display:grid; gap:0.45rem; margin-top:0.65rem; }
        .app-calendar-booking { display:grid; grid-template-columns:auto minmax(0, 1fr); gap:0.35rem 0.55rem; padding:0.55rem 0.6rem; border:1px solid rgba(13,28,76,0.08); background:#FAFBFD; color:#0D1C4C; text-decoration:none; transition:border-color 160ms ease,background 160ms ease; }
        .app-calendar-booking:hover { border-color:rgba(13,28,76,0.26); background:#fff; }
        .app-calendar-booking-dot { grid-row:1 / span 2; width:0.55rem; height:0.55rem; margin-top:0.22rem; background:#D9A72F; }
        .app-calendar-booking-title { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.72rem; font-weight:700; }
        .app-calendar-booking-time { color:rgba(13,28,76,0.58); font-size:0.64rem; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; }
        .app-calendar-footer { display:flex; align-items:center; justify-content:space-between; gap:0.85rem; margin-top:0.85rem; }
        .app-calendar-legend { display:flex; align-items:center; gap:0.65rem; color:rgba(13,28,76,0.58); font-size:0.62rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; }
        .app-calendar-legend span { display:inline-flex; align-items:center; gap:0.3rem; }
        .app-calendar-legend i { width:0.45rem; height:0.45rem; display:inline-block; }
        .app-calendar-link { color:#0D1C4C; font-size:0.64rem; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; }
        .app-calendar-link:hover { color:#D9A72F; }
        @media (max-width: 767px) {
            .app-topbar-inner { flex-wrap:wrap; padding:0.75rem 1rem; }
            .app-search { order:2; flex-basis:100%; max-width:none; }
            .app-topbar-tools { margin-left:auto; }
            .app-topbar-tool { width:2.65rem; padding:0; }
            .app-topbar-tool-label { display:none; }
            .app-notification-popover { right:-3.35rem; width:calc(100vw - 2rem); }
            .app-calendar-popover { right:-0.2rem; width:calc(100vw - 2rem); }
            .app-search-suggestions { max-height:calc(100vh - 10rem); }
            .app-search-suggestion { grid-template-columns:minmax(0, 1fr); }
            .app-search-suggestion-meta { text-align:left; }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (app(\App\Services\TurnstileVerifier::class)->enabled())
        <x-turnstile.scripts />
    @endif
    @livewireStyles
</head>
<body>
    <x-banner />

    <div class="min-h-screen flex" x-data="{ open: false }">
        {{-- Sidebar --}}
        <aside class="side-nav w-64 shrink-0 hidden lg:flex flex-col sticky top-0 h-screen overflow-y-auto z-30" :class="open ? '!flex fixed inset-y-0 left-0 z-50 w-64' : ''">
            <div class="px-6 py-6 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex flex-col leading-tight">
                    <span class="eyebrow text-brand-gold">ICAN</span>
                    <span class="font-serif text-2xl text-white">Eduspace</span>
                </a>
            </div>
            <nav class="flex-1 py-4 overflow-y-auto">
                @php
                    $u = auth()->user();
                    $dashUrl = $u && $u->hasAnyRole(['admin','super_admin']) ? url('/admin/dashboard')
                              : ($u && $u->hasRole('staff') ? url('/staff/dashboard') : route('dashboard'));
                @endphp
                <div class="side-group-label">{{ __('Account') }}</div>
                <a href="{{ $dashUrl }}" class="side-link {{ request()->is('dashboard','admin/dashboard','staff/dashboard') ? 'active' : '' }}">{{ __('Dashboard') }}</a>
                <a href="{{ route('bookings.index') }}" class="side-link {{ request()->routeIs('bookings.index') || request()->routeIs('bookings.show') ? 'active' : '' }}">{{ __('My bookings') }}</a>
                <a href="{{ route('bookings.create') }}" class="side-link {{ request()->routeIs('bookings.create') ? 'active' : '' }}">{{ __('New booking') }}</a>

                <div class="side-group-label">{{ __('Explore') }}</div>
                <a href="{{ route('home') }}" class="side-link">{{ __('Home') }}</a>
                <a href="{{ route('rooms.index') }}" class="side-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">{{ __('Rooms') }}</a>
                <a href="/#packages" class="side-link" data-sidebar-link="packages">{{ __('Packages') }}</a>

                @auth
                    @php
                        $u       = auth()->user();
                        $isAdmin = $u->hasAnyRole(['admin','super_admin']);
                        $isStaff = $u->hasRole('staff');
                        $unreadNotifications = ($isAdmin || $isStaff) && \Illuminate\Support\Facades\Schema::hasTable('booking_notifications')
                            ? \App\Models\BookingNotification::whereNull('read_at')->count()
                            : 0;
                    @endphp

                    @if($isStaff && ! $isAdmin)
                        <div class="side-group-label">{{ __('Staff') }}</div>
                        <a href="{{ route('manage.bookings.index') }}" class="side-link {{ request()->routeIs('manage.bookings.*') ? 'active' : '' }}">{{ __('All bookings') }}</a>
                        <a href="{{ route('manage.notifications.index') }}" class="side-link {{ request()->routeIs('manage.notifications.*') ? 'active' : '' }}">
                            <span>{{ __('Notifications') }}</span>
                            @if($unreadNotifications > 0)
                                <span class="side-count">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                            @endif
                        </a>
                        <a href="{{ route('manage.calendar') }}" class="side-link {{ request()->routeIs('manage.calendar') ? 'active' : '' }}">{{ __('Calendar') }}</a>
                    @endif

                    @if($isAdmin)
                        <div class="side-group-label">{{ __('Admin') }}</div>
                        <a href="{{ route('manage.bookings.index') }}" class="side-link {{ request()->routeIs('manage.bookings.*') ? 'active' : '' }}">{{ __('All bookings') }}</a>
                        <a href="{{ route('manage.notifications.index') }}" class="side-link {{ request()->routeIs('manage.notifications.*') ? 'active' : '' }}">
                            <span>{{ __('Notifications') }}</span>
                            @if($unreadNotifications > 0)
                                <span class="side-count">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                            @endif
                        </a>
                        <a href="{{ route('manage.calendar') }}" class="side-link {{ request()->routeIs('manage.calendar') ? 'active' : '' }}">{{ __('Calendar') }}</a>
                        <a href="{{ route('manage.classrooms.index') }}" class="side-link {{ request()->routeIs('manage.classrooms.*') ? 'active' : '' }}">{{ __('Room management') }}</a>
                        <a href="{{ route('manage.packages.index') }}" class="side-link {{ request()->routeIs('manage.packages.*') ? 'active' : '' }}">{{ __('Packages') }}</a>
                        <a href="{{ route('manage.users.index') }}" class="side-link {{ request()->routeIs('manage.users.*') ? 'active' : '' }}">{{ __('User management') }}</a>
                    @endif
                @endauth
            </nav>
            <div class="border-t border-white/10 p-5" x-data="{ accountOpen: false }">
                <div
                    x-show="accountOpen"
                    x-cloak
                    x-transition
                    class="mb-4 border border-white/10 bg-white/[0.04]"
                    role="menu"
                >
                    <a href="{{ route('settings') }}" class="block px-4 py-3 text-xs uppercase tracking-[0.22em] {{ request()->routeIs('settings') || request()->routeIs('profile.show') ? 'text-brand-gold' : 'text-white/70 hover:text-brand-gold' }}" role="menuitem">{{ __('Settings') }}</a>

                    <div class="border-t border-white/10 px-4 py-3">
                        <p class="text-[0.62rem] uppercase tracking-[0.22em] text-white/45 mb-2">{{ __('Language') }}</p>
                        <div class="flex items-center gap-3 text-xs">
                            <a href="{{ route('locale.switch', 'en') }}" class="@if(app()->getLocale()==='en') text-brand-gold @else text-white/55 hover:text-white @endif" role="menuitem">EN</a>
                            <span class="text-white/30">·</span>
                            <a href="{{ route('locale.switch', 'ko') }}" class="@if(app()->getLocale()==='ko') text-brand-gold @else text-white/55 hover:text-white @endif" role="menuitem">KO</a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 text-left text-xs uppercase tracking-[0.22em] text-brand-gold hover:text-white" role="menuitem">{{ __('Log out') }}</button>
                    </form>
                </div>

                <button
                    type="button"
                    class="w-full flex items-center justify-between gap-3 text-left"
                    aria-haspopup="menu"
                    :aria-expanded="accountOpen.toString()"
                    @click="accountOpen = ! accountOpen"
                >
                    <span class="min-w-0">
                        <span class="block text-xs uppercase tracking-[0.2em] text-white/55 mb-2">{{ __('Signed in as') }}</span>
                        <span class="block font-serif text-lg leading-tight truncate">{{ auth()->user()->name ?? '—' }}</span>
                    </span>
                    <svg class="size-4 shrink-0 text-brand-gold transition-transform" :class="{ 'rotate-180': accountOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                    </svg>
                </button>
            </div>
        </aside>

        {{-- Backdrop for mobile --}}
        <div x-show="open" x-cloak @click="open=false" class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>

        {{-- Main --}}
        <div class="flex-1 min-w-0 flex flex-col">
            <header class="topbar lg:hidden flex items-center justify-between px-5 py-3">
                <button @click="open = !open" class="text-white p-2">☰</button>
                <span class="font-serif text-lg">ICAN Eduspace</span>
                <span class="w-6"></span>
            </header>

            @auth
                @php
                    $topbarUser = auth()->user();
                    $topbarIsAdmin = $topbarUser->hasAnyRole(['admin', 'super_admin']);
                    $topbarIsStaff = $topbarUser->hasRole('staff');
                    $topbarCanManage = $topbarIsAdmin || $topbarIsStaff;
                    $topbarNotificationsReady = $topbarCanManage && \Illuminate\Support\Facades\Schema::hasTable('booking_notifications');
                    $topbarUnreadNotifications = $topbarNotificationsReady
                        ? \App\Models\BookingNotification::whereNull('read_at')->count()
                        : 0;
                    $topbarIssueNotifications = $topbarNotificationsReady
                        ? \App\Models\BookingNotification::whereIn('status', [
                            \App\Models\BookingNotification::STATUS_FAILED,
                            \App\Models\BookingNotification::STATUS_SKIPPED,
                        ])->count()
                        : 0;
                    $topbarRecentNotifications = $topbarNotificationsReady
                        ? \App\Models\BookingNotification::with('booking')
                            ->orderByRaw('case when read_at is null then 0 else 1 end')
                            ->orderByDesc('id')
                            ->limit(5)
                            ->get()
                        : collect();
                    $topbarSearchValue = request()->routeIs('search.index') ? request('q', '') : '';
                    $topbarQuickRoute = $topbarCanManage ? route('manage.calendar') : route('bookings.create');
                    $topbarQuickActive = $topbarCanManage ? request()->routeIs('manage.calendar') : request()->routeIs('bookings.create');
                    $topbarQuickLabel = $topbarCanManage ? __('Calendar') : __('New booking');
                    $topbarCalendarConfig = $topbarCanManage ? [
                        'eventsUrl' => route('manage.calendar.events'),
                        'calendarUrl' => route('manage.calendar'),
                        'bookingsUrl' => route('manage.bookings.index'),
                    ] : [];
                @endphp

                <header class="app-topbar" data-app-topbar>
                    <div class="app-topbar-inner">
                        <form method="GET" action="{{ route('search.index') }}" class="app-search" data-topbar-tool="search" x-data='topbarSearchSuggest(@json(['url' => route('search.suggestions'), 'query' => $topbarSearchValue]))' @submit="open = false" @keydown.escape.window="open = false" @click.outside="open = false">
                            <label for="app-global-search" class="sr-only">{{ __('Search') }}</label>
                            <svg class="app-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                            </svg>
                            <input id="app-global-search" type="text" name="q" value="{{ $topbarSearchValue }}" placeholder="{{ __('Search bookings, rooms, packages') }}" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="app-search-suggestions" :aria-expanded="open ? 'true' : 'false'" x-model="query" @focus="load()" @input.debounce.220ms="load()" @keydown.arrow-down.prevent="focusNext()" @keydown.arrow-up.prevent="focusPrevious()" @keydown.enter="chooseFocused($event)">
                            <button type="submit" class="app-search-button" aria-label="{{ __('Search') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                </svg>
                            </button>
                            <div id="app-search-suggestions" class="app-search-suggestions" data-search-suggestions x-cloak x-show="open" x-transition.origin.top>
                                <div class="app-search-suggestions-head" x-show="loading">{{ __('Searching') }}</div>
                                <template x-if="! loading && correction">
                                    <button type="button" class="app-search-correction" data-search-correction @click="applyCorrection()">
                                        <span>{{ __('Did you mean') }}</span>
                                        <strong x-text="correction.query"></strong>
                                        <span>?</span>
                                    </button>
                                </template>
                                <template x-if="! loading && sections.length === 0 && ! correction">
                                    <div class="app-search-empty">{{ __('No quick matches.') }}</div>
                                </template>
                                <template x-for="section in sections" :key="section.key">
                                    <div class="app-search-section" :data-search-suggestion-section="section.key">
                                        <p class="app-search-section-title" x-text="section.title"></p>
                                        <template x-for="item in section.items" :key="item.id">
                                            <a class="app-search-suggestion" :class="{ 'active': focusedId === item.id }" :href="item.url" :data-search-suggestion-item="item.id" @mouseenter="focusedId = item.id" @focus="focusedId = item.id" @click="open = false">
                                                <span class="app-search-suggestion-label" x-text="item.label"></span>
                                                <span class="app-search-suggestion-meta" x-text="item.meta"></span>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                                <div class="app-search-footer">
                                    <button type="submit" class="app-search-full">{{ __('Full search') }}</button>
                                </div>
                            </div>
                        </form>

                        <nav class="app-topbar-tools" aria-label="{{ __('Top tools') }}">
                            @if($topbarCanManage)
                                <div class="app-notification-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false" data-notification-popover-root>
                                    <button type="button" class="app-topbar-tool {{ request()->routeIs('manage.notifications.*') ? 'active' : '' }}" data-topbar-tool="notifications" data-notification-toggle aria-label="{{ __('Notifications') }}" aria-controls="app-topbar-notifications-panel" :aria-expanded="open ? 'true' : 'false'" @click="open = ! open">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.85 18.25a2.85 2.85 0 0 1-5.7 0m9.1-5.2V10a6.25 6.25 0 1 0-12.5 0v3.05L4.5 16.5h15l-1.25-3.45Z" />
                                        </svg>
                                        <span class="app-topbar-tool-label">{{ __('Notifications') }}</span>
                                        @if($topbarUnreadNotifications > 0)
                                            <span class="app-topbar-badge">{{ $topbarUnreadNotifications > 99 ? '99+' : $topbarUnreadNotifications }}</span>
                                        @endif
                                    </button>

                                    <div id="app-topbar-notifications-panel" class="app-notification-popover" data-notification-popover x-cloak x-show="open" x-transition.origin.top.right>
                                        <div class="app-notification-card">
                                            <div class="app-notification-head">
                                                <div>
                                                    <p class="app-notification-title">{{ __('Notifications') }}</p>
                                                    <p class="app-notification-subtitle">{{ __('Booking alerts') }}</p>
                                                </div>
                                                @if($topbarUnreadNotifications > 0)
                                                    <form method="POST" action="{{ route('manage.notifications.read-all') }}">
                                                        @csrf
                                                        <button type="submit" class="app-notification-action" data-notification-read-all>{{ __('Read all') }}</button>
                                                    </form>
                                                @endif
                                            </div>

                                            <div class="app-notification-stats">
                                                <a href="{{ route('manage.notifications.index', ['status' => 'unread']) }}" class="app-notification-stat" data-notification-stat="unread">
                                                    <strong>{{ $topbarUnreadNotifications }}</strong>
                                                    <span>{{ __('Unread') }}</span>
                                                </a>
                                                <a href="{{ route('manage.notifications.index', ['status' => 'issues']) }}" class="app-notification-stat" data-notification-stat="issues">
                                                    <strong>{{ $topbarIssueNotifications }}</strong>
                                                    <span>{{ __('Issues') }}</span>
                                                </a>
                                            </div>

                                            @if($topbarRecentNotifications->isNotEmpty())
                                                <div class="app-notification-list" data-notification-list>
                                                    @foreach($topbarRecentNotifications as $notification)
                                                        <div class="app-notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}" data-notification-item>
                                                            <span class="app-notification-dot"></span>
                                                            <div class="app-notification-copy">
                                                                <p class="app-notification-subject">{{ $notification->subject ?: __('Booking notification') }}</p>
                                                                <p class="app-notification-message">{{ \Illuminate\Support\Str::limit($notification->message ?: $notification->notification_type, 86) }}</p>
                                                                <p class="app-notification-meta">
                                                                    @if($notification->booking_id)
                                                                        #{{ $notification->booking_id }} ·
                                                                    @endif
                                                                    {{ optional($notification->created_at)->diffForHumans() }}
                                                                </p>
                                                            </div>
                                                            @unless($notification->read_at)
                                                                <form method="POST" action="{{ route('manage.notifications.read', $notification) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="return" value="{{ url()->current() }}">
                                                                    <button type="submit" class="app-notification-read">{{ __('Read') }}</button>
                                                                </form>
                                                            @endunless
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="app-notification-empty" data-notification-empty>{{ __('No notifications yet.') }}</div>
                                            @endif

                                            <div class="app-notification-footer">
                                                <a href="{{ route('manage.notifications.index', ['status' => 'unread']) }}" class="app-notification-link" data-notification-unread-link>{{ __('Unread') }}</a>
                                                <a href="{{ route('manage.notifications.index') }}" class="app-notification-link" data-notification-full-link>{{ __('All notifications') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('bookings.index') }}" class="app-topbar-tool {{ request()->routeIs('bookings.index') ? 'active' : '' }}" data-topbar-tool="bookings" aria-label="{{ __('My bookings') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h8M8 10h8M8 14h5m-7.5 6h13A1.5 1.5 0 0 0 20 18.5v-13A1.5 1.5 0 0 0 18.5 4h-13A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20Z" />
                                    </svg>
                                    <span class="app-topbar-tool-label">{{ __('My bookings') }}</span>
                                </a>
                            @endif

                            @if($topbarCanManage)
                                <div class="app-calendar-menu" x-data='topbarBookingCalendar(@json($topbarCalendarConfig))' @keydown.escape.window="open = false" @click.outside="open = false" data-calendar-popover-root>
                                    <button type="button" class="app-topbar-tool {{ $topbarQuickActive ? 'active' : '' }}" data-topbar-tool="quick-action" data-calendar-toggle aria-label="{{ $topbarQuickLabel }}" aria-controls="app-topbar-calendar-panel" :aria-expanded="open ? 'true' : 'false'" @click="toggle()">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4.5 9.5h15m-13 11h11a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2h-11a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
                                        </svg>
                                        <span class="app-topbar-tool-label">{{ $topbarQuickLabel }}</span>
                                    </button>

                                    <div id="app-topbar-calendar-panel" class="app-calendar-popover" data-calendar-popover x-cloak x-show="open" x-transition.origin.top.right>
                                        <div class="app-calendar-card">
                                            <div class="app-calendar-head">
                                                <div>
                                                    <p class="app-calendar-title" x-text="monthLabel"></p>
                                                    <p class="app-calendar-subtitle">{{ __('Booking calendar') }}</p>
                                                </div>
                                                <div class="app-calendar-controls">
                                                    <button type="button" class="app-calendar-nav" data-calendar-prev aria-label="{{ __('Previous month') }}" @click="moveMonth(-1)">‹</button>
                                                    <button type="button" class="app-calendar-nav" data-calendar-next aria-label="{{ __('Next month') }}" @click="moveMonth(1)">›</button>
                                                </div>
                                            </div>

                                            <div class="app-calendar-weekdays" aria-hidden="true">
                                                <span>{{ __('S') }}</span>
                                                <span>{{ __('M') }}</span>
                                                <span>{{ __('T') }}</span>
                                                <span>{{ __('W') }}</span>
                                                <span>{{ __('T') }}</span>
                                                <span>{{ __('F') }}</span>
                                                <span>{{ __('S') }}</span>
                                            </div>

                                            <div class="app-calendar-grid" role="grid" data-calendar-month-grid>
                                                <template x-for="(day, index) in days" :key="day ? day.key : 'blank-' + index">
                                                    <div class="app-calendar-day-slot" role="gridcell">
                                                        <template x-if="day">
                                                            <div class="app-calendar-day-wrap">
                                                                <button type="button" class="app-calendar-day" :class="day.classes" :style="day.markerStyle" :title="day.title" @click="openDate(day)" @mouseenter="hoveredDate = day.key" @mouseleave="hoveredDate = null" @focus="hoveredDate = day.key" @blur="hoveredDate = null">
                                                                    <span class="app-calendar-day-number" x-text="day.day"></span>
                                                                    <span class="app-calendar-day-count" x-show="day.count > 1" x-text="day.count"></span>
                                                                </button>
                                                                <div class="app-calendar-tooltip" :class="day.tooltipClass" x-show="hoveredDate === day.key && day.markers.length" x-transition>
                                                                    <template x-for="marker in day.markers.slice(0, 4)" :key="marker.id">
                                                                        <div class="app-calendar-tooltip-row">
                                                                            <span class="app-calendar-dot" :style="'background:' + marker.color"></span>
                                                                            <span x-text="marker.label"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>

                                            <div class="app-calendar-status" data-calendar-status>
                                                <span x-show="loading">{{ __('Loading booking dates...') }}</span>
                                                <span x-show="! loading && error">{{ __('Calendar could not load.') }}</span>
                                                <span x-show="! loading && ! error && events.length === 0">{{ __('No active bookings this month.') }}</span>
                                                <span x-show="! loading && ! error && events.length > 0" x-text="events.length + ' {{ __('active booking date(s)') }}'"></span>
                                            </div>

                                            <div class="app-calendar-bookings" x-show="upcoming.length > 0" data-calendar-upcoming>
                                                <template x-for="booking in upcoming.slice(0, 3)" :key="booking.id">
                                                    <a class="app-calendar-booking" :href="booking.url || calendarUrl">
                                                        <span class="app-calendar-booking-dot" :style="'background:' + booking.color"></span>
                                                        <span class="app-calendar-booking-title" x-text="booking.title"></span>
                                                        <span class="app-calendar-booking-time" x-text="booking.when"></span>
                                                    </a>
                                                </template>
                                            </div>

                                            <div class="app-calendar-footer">
                                                <div class="app-calendar-legend">
                                                    <span><i style="background:#D9A72F"></i>{{ __('Pending') }}</span>
                                                    <span><i style="background:#10b981"></i>{{ __('Booked') }}</span>
                                                </div>
                                                <a href="{{ route('manage.calendar') }}" class="app-calendar-link" data-calendar-full-link>{{ __('Full calendar') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <a href="{{ $topbarQuickRoute }}" class="app-topbar-tool {{ $topbarQuickActive ? 'active' : '' }}" data-topbar-tool="quick-action" aria-label="{{ $topbarQuickLabel }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                                    </svg>
                                    <span class="app-topbar-tool-label">{{ $topbarQuickLabel }}</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                </header>
            @endauth

            @if (isset($header))
                <div class="bg-white border-b border-brand-navy/10">
                    <div class="max-w-6xl mx-auto px-8 py-8">
                        {{ $header }}
                    </div>
                </div>
            @endif

            <main class="flex-1 bg-brand-soft">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')
    <script>
        window.topbarSearchSuggest = function (config) {
            return {
                query: config.query || '',
                sections: [],
                correction: null,
                open: false,
                loading: false,
                focusedId: null,
                requestId: 0,

                get items() {
                    return this.sections.flatMap((section) => section.items || []);
                },

                load() {
                    const term = String(this.query || '').trim();

                    this.focusedId = null;

                    if (term.length < 2) {
                        this.sections = [];
                        this.correction = null;
                        this.open = false;
                        this.loading = false;
                        return;
                    }

                    const currentRequest = ++this.requestId;
                    this.open = true;
                    this.loading = true;

                    fetch(config.url + '?q=' + encodeURIComponent(term), {
                        headers: {
                            Accept: 'application/json',
                        },
                    })
                        .then((response) => {
                            if (! response.ok) {
                                throw new Error('search-suggestions-failed');
                            }

                            return response.json();
                        })
                        .then((payload) => {
                            if (currentRequest !== this.requestId) {
                                return;
                            }

                            this.sections = Array.isArray(payload.sections) ? payload.sections : [];
                            this.correction = payload.correction || null;
                            this.open = true;
                        })
                        .catch(() => {
                            if (currentRequest === this.requestId) {
                                this.sections = [];
                                this.correction = null;
                                this.open = false;
                            }
                        })
                        .finally(() => {
                            if (currentRequest === this.requestId) {
                                this.loading = false;
                            }
                        });
                },

                focusNext() {
                    this.moveFocus(1);
                },

                focusPrevious() {
                    this.moveFocus(-1);
                },

                moveFocus(direction) {
                    const items = this.items;

                    if (! items.length) {
                        this.load();
                        return;
                    }

                    const currentIndex = items.findIndex((item) => item.id === this.focusedId);
                    const nextIndex = currentIndex === -1
                        ? (direction > 0 ? 0 : items.length - 1)
                        : (currentIndex + direction + items.length) % items.length;

                    this.focusedId = items[nextIndex].id;
                    this.open = true;

                    this.$nextTick(() => this.scrollFocusedIntoView());
                },

                chooseFocused(event) {
                    if (! this.focusedId) {
                        return;
                    }

                    const item = this.items.find((candidate) => candidate.id === this.focusedId);

                    if (! item) {
                        return;
                    }

                    event.preventDefault();
                    this.open = false;
                    window.location.href = item.url;
                },

                applyCorrection() {
                    if (! this.correction || ! this.correction.query) {
                        return;
                    }

                    this.query = this.correction.query;
                    this.focusedId = null;
                    this.load();

                    this.$nextTick(() => {
                        const input = this.$el.querySelector('input[name="q"]');

                        if (input) {
                            input.focus();
                        }
                    });
                },

                scrollFocusedIntoView() {
                    if (! this.focusedId) {
                        return;
                    }

                    const selector = '[data-search-suggestion-item="' + this.focusedId + '"]';
                    const item = this.$el.querySelector(selector);

                    if (item) {
                        item.scrollIntoView({ block: 'nearest' });
                    }
                },
            };
        };

        window.topbarBookingCalendar = function (config) {
            return {
                open: false,
                loading: false,
                error: false,
                events: [],
                cache: {},
                hoveredDate: null,
                visibleDate: null,
                eventsUrl: config.eventsUrl,
                calendarUrl: config.calendarUrl,
                bookingsUrl: config.bookingsUrl,

                init() {
                    const today = new Date();
                    this.visibleDate = new Date(today.getFullYear(), today.getMonth(), 1);
                },

                get locale() {
                    return document.documentElement.lang || 'en';
                },

                get monthLabel() {
                    return new Intl.DateTimeFormat(this.locale, {
                        month: 'long',
                        year: 'numeric',
                    }).format(this.visibleDate || new Date());
                },

                get days() {
                    if (! this.visibleDate) {
                        return [];
                    }

                    const year = this.visibleDate.getFullYear();
                    const month = this.visibleDate.getMonth();
                    const firstDay = new Date(year, month, 1);
                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const cells = [];

                    for (let index = 0; index < firstDay.getDay(); index += 1) {
                        cells.push(null);
                    }

                    for (let day = 1; day <= daysInMonth; day += 1) {
                        const date = new Date(year, month, day);
                        const key = this.dateKey(date);
                        const markers = this.eventsForDate(key).map((event) => ({
                            id: event.id + '-' + key,
                            label: event.label,
                            color: event.color,
                            url: event.url,
                            status: event.status,
                        }));
                        const statuses = [...new Set(markers.map((marker) => marker.status))];
                        const colors = [...new Set(markers.map((marker) => marker.color))];
                        const isToday = key === this.dateKey(new Date());
                        const indexInGrid = cells.length;
                        const horizontal = indexInGrid % 7 >= 5 ? 'right' : (indexInGrid % 7 <= 1 ? 'left' : 'center');
                        const vertical = indexInGrid < 14 ? 'below' : 'above';
                        const state = markers.length
                            ? (statuses.length > 1 ? 'mixed' : statuses[0])
                            : '';

                        cells.push({
                            day,
                            key,
                            count: markers.length,
                            markers,
                            classes: [
                                isToday ? 'is-today' : '',
                                markers.length ? 'has-events' : '',
                                state ? 'state-' + state : '',
                            ].filter(Boolean).join(' '),
                            markerStyle: state === 'mixed' ? 'background:' + this.markerBackground(colors) : '',
                            tooltipClass: vertical + ' ' + horizontal,
                            title: markers.map((marker) => marker.label).join(' / '),
                        });
                    }

                    return cells;
                },

                get upcoming() {
                    const todayKey = this.dateKey(new Date());

                    return [...this.events]
                        .filter((event) => event.endKey >= todayKey || this.monthKey(new Date(event.start)) !== this.monthKey(new Date()))
                        .sort((a, b) => a.start - b.start);
                },

                toggle() {
                    this.open = ! this.open;

                    if (this.open) {
                        this.loadMonth();
                    }
                },

                moveMonth(offset) {
                    this.visibleDate = new Date(this.visibleDate.getFullYear(), this.visibleDate.getMonth() + offset, 1);
                    this.hoveredDate = null;
                    this.loadMonth();
                },

                loadMonth() {
                    const key = this.monthKey(this.visibleDate);

                    if (this.cache[key]) {
                        this.events = this.cache[key];
                        return;
                    }

                    this.loading = true;
                    this.error = false;

                    const start = this.dateKey(new Date(this.visibleDate.getFullYear(), this.visibleDate.getMonth(), 1));
                    const end = this.dateKey(new Date(this.visibleDate.getFullYear(), this.visibleDate.getMonth() + 1, 1));
                    const url = this.eventsUrl + '?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end);

                    fetch(url, {
                        headers: {
                            Accept: 'application/json',
                        },
                    })
                        .then((response) => {
                            if (! response.ok) {
                                throw new Error('calendar-load-failed');
                            }

                            return response.json();
                        })
                        .then((payload) => {
                            const events = Array.isArray(payload)
                                ? payload.map((event) => this.normalizeEvent(event)).filter(Boolean)
                                : [];

                            this.cache[key] = events;
                            this.events = events;
                        })
                        .catch(() => {
                            this.error = true;
                            this.events = [];
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                normalizeEvent(event) {
                    if (! event || ! event.start) {
                        return null;
                    }

                    const start = new Date(event.start);
                    const end = event.end ? new Date(event.end) : start;

                    if (Number.isNaN(start.getTime())) {
                        return null;
                    }

                    const safeEnd = Number.isNaN(end.getTime()) || end < start ? start : end;
                    const color = event.backgroundColor || '#6b7280';
                    const status = this.statusFromColor(color);
                    const normalized = {
                        id: event.id || event.title || this.dateKey(start),
                        title: event.title || '{{ __('Booking') }}',
                        start,
                        end: safeEnd,
                        startKey: this.dateKey(start),
                        endKey: this.dateKey(start),
                        color,
                        status,
                        url: event.url || this.calendarUrl,
                    };

                    normalized.when = this.formatEventTime(normalized);
                    normalized.label = normalized.title + ' · ' + normalized.when;

                    return normalized;
                },

                eventsForDate(key) {
                    return this.events.filter((event) => key === event.startKey);
                },

                openDate(day) {
                    if (! day || ! day.markers.length) {
                        return;
                    }

                    if (day.markers.length === 1 && day.markers[0].url) {
                        window.location.href = day.markers[0].url;
                        return;
                    }

                    window.location.href = this.calendarUrl;
                },

                statusFromColor(color) {
                    const value = String(color || '').toLowerCase();

                    if (value.includes('d9a72f')) {
                        return 'pending';
                    }

                    if (value.includes('10b981')) {
                        return 'approved';
                    }

                    return 'neutral';
                },

                markerBackground(colors) {
                    if (! colors.length) {
                        return 'transparent';
                    }

                    if (colors.length === 1) {
                        return colors[0];
                    }

                    const stops = colors.map((color, index) => {
                        const position = Math.round((index / Math.max(colors.length - 1, 1)) * 100);

                        return color + ' ' + position + '%';
                    });

                    return 'linear-gradient(45deg, ' + stops.join(', ') + ')';
                },

                formatEventTime(event) {
                    const date = new Intl.DateTimeFormat(this.locale, {
                        month: 'short',
                        day: 'numeric',
                    }).format(event.start);
                    const start = new Intl.DateTimeFormat(this.locale, {
                        hour: 'numeric',
                        minute: '2-digit',
                    }).format(event.start);
                    const end = new Intl.DateTimeFormat(this.locale, {
                        hour: 'numeric',
                        minute: '2-digit',
                    }).format(event.end);

                    return date + ' · ' + start + '-' + end;
                },

                monthKey(date) {
                    return date.getFullYear() + '-' + this.pad(date.getMonth() + 1);
                },

                dateKey(date) {
                    return date.getFullYear() + '-' + this.pad(date.getMonth() + 1) + '-' + this.pad(date.getDate());
                },

                pad(value) {
                    return String(value).padStart(2, '0');
                },
            };
        };
    </script>
    {{-- Livewire bundles Alpine.js — do NOT load it separately or you get "multiple instances" warnings. --}}
    @livewireScripts
</body>
</html>
