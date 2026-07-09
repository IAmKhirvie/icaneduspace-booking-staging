<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
:root {
    --brand-bg:   #F5F7FB;
    --brand-card: #ffffff;
    --brand-navy: #0d1c4c;
    --brand-dark: #07112f;
    --brand-gold: #d9a72f;
    --brand-text: #0d1c4c;
    --brand-muted:#5b6477;
    --brand-border: rgba(13,28,76,0.08);
}

/* ============ App shell ============ */
html.fi, html.fi body, html.fi .fi-body, html.fi .fi-layout, html.fi .fi-main {
    background-color: var(--brand-bg) !important;
    color: var(--brand-text);
    font-family: 'Montserrat', sans-serif;
}

html.fi .fi-page {
    background: transparent !important;
}

/* ============ Sidebar (navy) ============ */
html.fi .fi-sidebar,
html.fi .fi-sidebar-nav {
    background: linear-gradient(180deg, var(--brand-navy), var(--brand-dark)) !important;
    color: #fff !important;
    border-right: 1px solid rgba(255,255,255,0.05) !important;
}

html.fi .fi-sidebar-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(217,167,47,0.18) !important;
}

html.fi .fi-sidebar .fi-logo,
html.fi .fi-sidebar .fi-logo * {
    color: #fff !important;
}

/* default state — gold text, transparent bg */
html.fi .fi-sidebar-item-btn,
html.fi .fi-sidebar-group-btn {
    color: var(--brand-gold) !important;
    background-color: transparent !important;
    font-weight: 500 !important;
    letter-spacing: 0.03em;
}
html.fi .fi-sidebar-item-btn *,
html.fi .fi-sidebar-group-btn * {
    color: var(--brand-gold) !important;
}

/* hover — gold text on dark navy */
html.fi .fi-sidebar-item-btn:hover,
html.fi .fi-sidebar-group-btn:hover {
    color: var(--brand-gold) !important;
    background-color: var(--brand-dark) !important;
}
html.fi .fi-sidebar-item-btn:hover *,
html.fi .fi-sidebar-group-btn:hover * {
    color: var(--brand-gold) !important;
}

/* active — gold text on solid dark navy, with gold left border */
html.fi .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
html.fi .fi-sidebar-item-btn[aria-current="page"],
html.fi .fi-sidebar-item-btn[aria-current="true"] {
    color: var(--brand-gold) !important;
    background-color: var(--brand-dark) !important;
    background-image: none !important;
    border-left: 3px solid var(--brand-gold) !important;
    font-weight: 600 !important;
}
html.fi .fi-sidebar-item.fi-active > .fi-sidebar-item-btn *,
html.fi .fi-sidebar-item-btn[aria-current="page"] * {
    color: var(--brand-gold) !important;
}
html.fi .fi-sidebar-group-label {
    color: rgba(217,167,47,0.85) !important;
    letter-spacing: 0.24em !important;
    text-transform: uppercase;
    font-size: 0.62rem !important;
    font-weight: 600 !important;
}

/* ============ Topbar (navy, sits next to sidebar) ============ */
html.fi .fi-topbar {
    background: linear-gradient(180deg, var(--brand-navy), var(--brand-dark)) !important;
    border-bottom: 1px solid rgba(217,167,47,0.18) !important;
    box-shadow: 0 2px 12px rgba(7,17,47,0.10);
    color: #fff !important;
}
html.fi .fi-topbar *,
html.fi .fi-topbar .fi-icon-btn,
html.fi .fi-topbar .fi-btn {
    color: rgba(255,255,255,0.88) !important;
}
html.fi .fi-topbar .fi-icon-btn:hover,
html.fi .fi-topbar .fi-btn:hover {
    color: var(--brand-gold) !important;
}
html.fi .fi-topbar input,
html.fi .fi-topbar select {
    background: rgba(255,255,255,0.06) !important;
    border-color: rgba(255,255,255,0.18) !important;
    color: #fff !important;
}
html.fi .fi-topbar input::placeholder {
    color: rgba(255,255,255,0.45) !important;
}

/* ============ Surfaces ============ */
html.fi .fi-section,
html.fi .fi-wi,
html.fi .fi-card,
html.fi .fi-ta,
html.fi .fi-fo-component-ctn,
html.fi .fi-modal-window,
html.fi .fi-dropdown-panel {
    background: var(--brand-card) !important;
    border: 1px solid var(--brand-border) !important;
    box-shadow: 0 1px 3px rgba(7,17,47,0.04), 0 4px 14px rgba(7,17,47,0.04) !important;
    border-radius: 2px !important;
    color: var(--brand-text) !important;
}

/* Dropdown panel content (user menu) */
html.fi .fi-dropdown-panel,
html.fi .fi-dropdown-panel *,
html.fi .fi-dropdown-header,
html.fi .fi-dropdown-header span,
html.fi .fi-dropdown-list-item,
html.fi .fi-dropdown-list-item-label,
html.fi .fi-dropdown-list-item .fi-icon {
    color: var(--brand-text) !important;
}
html.fi .fi-dropdown-header {
    border-bottom: 1px solid var(--brand-border);
    padding: 0.6rem 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
}
html.fi .fi-dropdown-list-item {
    padding: 0.55rem 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    background: transparent;
    transition: background 200ms ease;
}
html.fi .fi-dropdown-list-item:hover {
    background: rgba(217,167,47,0.10) !important;
    color: var(--brand-navy) !important;
}

html.fi .fi-section-header,
html.fi .fi-ta-header {
    border-bottom: 1px solid var(--brand-border) !important;
}

/* ============ Typography ============ */
html.fi .fi-header-heading,
html.fi .fi-section-header-heading,
html.fi .fi-wi-stats-overview-stat-value,
html.fi .fi-modal-heading {
    font-family: 'Cormorant Garamond', serif !important;
    color: var(--brand-navy) !important;
    font-weight: 600;
    letter-spacing: 0.005em;
}
html.fi .fi-header-subheading,
html.fi .fi-section-header-description,
html.fi .fi-wi-stats-overview-stat-label {
    color: var(--brand-muted) !important;
}
html.fi .fi-wi-stats-overview-stat-label {
    letter-spacing: 0.22em !important;
    text-transform: uppercase;
    font-size: 0.65rem !important;
    color: var(--brand-gold) !important;
}

/* ============ Tables ============ */
html.fi .fi-ta-table thead {
    background: rgba(13,28,76,0.03) !important;
}
html.fi .fi-ta-header-cell {
    border-bottom: 1px solid var(--brand-border) !important;
}
html.fi .fi-ta-header-cell-label {
    color: var(--brand-navy) !important;
    letter-spacing: 0.08em !important;
    font-size: 0.72rem !important;
    font-weight: 600;
    white-space: nowrap;
}
html.fi .fi-ta-empty-state-heading {
    color: var(--brand-navy) !important;
    font-family: 'Cormorant Garamond', serif !important;
    font-size: 1.25rem !important;
}
html.fi .fi-ta-row {
    border-color: var(--brand-border) !important;
}
html.fi .fi-ta-row:hover {
    background: rgba(217,167,47,0.04) !important;
}
html.fi .fi-ta-cell,
html.fi .fi-ta-text,
html.fi .fi-ta-text-item {
    color: var(--brand-text) !important;
}
/* Restore Filament's normal cell padding */
html.fi .fi-ta-cell,
html.fi .fi-ta-header-cell {
    padding-top: 0.75rem !important;
    padding-bottom: 0.75rem !important;
}
/* Image columns */
html.fi .fi-ta-image-col img {
    border-radius: 2px !important;
    object-fit: cover !important;
}

/* Table toolbar: keep create + filter buttons spaced apart */
html.fi .fi-ta-header,
html.fi .fi-ta-header-toolbar,
html.fi .fi-ta-header-toolbar-end,
html.fi .fi-ac,
html.fi .fi-ac-grp {
    display: flex !important;
    align-items: center;
    gap: 0.75rem !important;
    flex-wrap: wrap;
}
html.fi .fi-ta-header-toolbar > *,
html.fi .fi-ac > *,
html.fi .fi-ac-grp > * {
    flex: 0 0 auto;
}
html.fi .fi-ta-header {
    padding: 1rem 1.25rem !important;
}
html.fi .fi-btn {
    white-space: nowrap;
}
/* Group buttons that visually sit beside each other still need breathing room */
html.fi .fi-ta-header .fi-btn + .fi-btn,
html.fi .fi-ta-header .fi-btn + .fi-icon-btn,
html.fi .fi-ta-header .fi-icon-btn + .fi-btn,
html.fi .fi-ta-header .fi-icon-btn + .fi-icon-btn {
    margin-left: 0.5rem !important;
}

/* ============ Inputs ============ */
html.fi .fi-input,
html.fi .fi-select-input,
html.fi .fi-textarea-input,
html.fi .fi-fo-text-input input,
html.fi .fi-fo-select select,
html.fi .fi-fo-textarea textarea {
    background: #fff !important;
    border: 1px solid rgba(13,28,76,0.18) !important;
    color: var(--brand-text) !important;
    border-radius: 2px !important;
}
html.fi .fi-input:focus,
html.fi .fi-input-wrp:focus-within {
    border-color: var(--brand-gold) !important;
    box-shadow: 0 0 0 2px rgba(217,167,47,0.18) !important;
}
html.fi .fi-fo-field-label,
html.fi label {
    color: var(--brand-navy) !important;
    font-weight: 500 !important;
}

/* ============ Buttons ============ */
html.fi .fi-btn-color-primary {
    background: var(--brand-gold) !important;
    color: var(--brand-dark) !important;
    border: none !important;
    letter-spacing: 0.12em;
    font-weight: 600;
}
html.fi .fi-btn-color-primary:hover {
    background: var(--brand-dark) !important;
    color: var(--brand-gold) !important;
}
html.fi .fi-btn-outlined {
    border-color: var(--brand-navy) !important;
    color: var(--brand-navy) !important;
}
html.fi .fi-btn-outlined:hover {
    background: var(--brand-navy) !important;
    color: #fff !important;
}

/* ============ Badges ============ */
html.fi .fi-badge.fi-color-warning { background: rgba(217,167,47,0.15) !important; color: #a37520 !important; border: 1px solid rgba(217,167,47,0.4) !important; }
html.fi .fi-badge.fi-color-success { background: rgba(16,185,129,0.10) !important; color: #047857 !important; border: 1px solid rgba(16,185,129,0.40) !important; }
html.fi .fi-badge.fi-color-danger  { background: rgba(239,68,68,0.08) !important;  color: #b91c1c !important; border: 1px solid rgba(239,68,68,0.40) !important; }
html.fi .fi-badge.fi-color-info    { background: rgba(59,130,246,0.08) !important; color: #1d4ed8 !important; border: 1px solid rgba(59,130,246,0.40) !important; }
html.fi .fi-badge.fi-color-gray    { background: rgba(13,28,76,0.05) !important;   color: var(--brand-muted) !important; border: 1px solid rgba(13,28,76,0.15) !important; }

/* ============ Auth (admin login) ============ */
html.fi .fi-simple-layout {
    background:
      radial-gradient(circle at 12% 18%, rgba(217,167,47,0.10), transparent 45%),
      radial-gradient(circle at 88% 82%, rgba(23,36,93,0.05), transparent 60%),
      var(--brand-bg) !important;
}
html.fi .fi-simple-main-ctn {
    background: #fff !important;
    border: 1px solid var(--brand-border) !important;
    box-shadow: 0 24px 70px rgba(7,17,47,0.10) !important;
    border-radius: 2px !important;
}

/* ============ FullCalendar toolbar ============ */
html.fi .fc-toolbar { gap: 0.75rem; flex-wrap: wrap; }
html.fi .fc-toolbar-chunk { display: flex; align-items: center; gap: 0.5rem; }

html.fi .fc-button.fc-button-primary {
    background: #fff !important;
    border: 1px solid rgba(13,28,76,0.18) !important;
    color: var(--brand-navy) !important;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.72rem !important;
    font-weight: 600;
    padding: 0.5rem 0.9rem !important;
    box-shadow: none !important;
    border-radius: 2px !important;
}
html.fi .fc-button.fc-button-primary:hover:not(:disabled) {
    background: var(--brand-navy) !important;
    color: #fff !important;
    border-color: var(--brand-navy) !important;
}
html.fi .fc-button.fc-button-primary:disabled {
    opacity: 0.45;
}
html.fi .fc-button.fc-button-primary.fc-button-active {
    background: var(--brand-gold) !important;
    border-color: var(--brand-gold) !important;
    color: var(--brand-dark) !important;
}
html.fi .fc-button-group > .fc-button { border-radius: 0 !important; margin: 0 !important; }
html.fi .fc-button-group > .fc-button:first-child { border-radius: 2px 0 0 2px !important; }
html.fi .fc-button-group > .fc-button:last-child  { border-radius: 0 2px 2px 0 !important; }
html.fi .fc-button-group + .fc-button { margin-left: 0.5rem !important; }
html.fi .fc-toolbar-title { color: var(--brand-navy) !important; font-family: 'Cormorant Garamond', serif; font-size: 1.6rem !important; font-weight: 600; }

/* Calendar fills viewport + separates from action buttons above */
html.fi .filament-fullcalendar { min-height: calc(100vh - 220px); margin-top: 1.25rem !important; }
html.fi .fi-wi-fullcalendar .fi-section-content > .fi-ac,
html.fi .fi-wi-fullcalendar .fi-ac.fi-align-start {
    margin-bottom: 1.25rem !important;
    padding-bottom: 0.25rem;
}
html.fi .fi-section-content { padding: 1.25rem 1.5rem !important; }
html.fi .fc-view-harness { min-height: calc(100vh - 320px) !important; }

/* Calendar header row + slot times */
html.fi .fc-col-header-cell-cushion { color: var(--brand-navy) !important; font-weight: 600; text-decoration: none !important; padding: 0.5rem 0.25rem; }
html.fi .fc-timegrid-slot-label-cushion { color: var(--brand-muted) !important; font-size: 0.7rem; }
html.fi .fc-day-today { background: rgba(217,167,47,0.05) !important; }

/* ============ Staff dashboard ============ */
html.fi .staff-dashboard {
    display: flex;
    flex-direction: column;
    gap: 3rem;
}
html.fi .staff-hero {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 2rem 0 0.25rem;
    flex-wrap: wrap;
}
html.fi .staff-hero h1,
html.fi .staff-section-head h2,
html.fi .staff-booking-row h3,
html.fi .staff-room-card h3 {
    font-family: 'Cormorant Garamond', serif !important;
    color: var(--brand-navy) !important;
    font-weight: 600;
    line-height: 1.05;
}
html.fi .staff-hero h1 {
    font-size: clamp(2.35rem, 4vw, 3.5rem);
    margin: 0.25rem 0 0.35rem;
}
html.fi .staff-hero p:not(.staff-eyebrow),
html.fi .staff-muted,
html.fi .staff-empty p,
html.fi .staff-shortcut small {
    color: rgba(13,28,76,0.62) !important;
}
html.fi .staff-eyebrow {
    color: var(--brand-gold) !important;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    font-size: 0.68rem;
    line-height: 1.4;
    font-weight: 600;
}
html.fi .staff-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}
html.fi .staff-card {
    background: #fff !important;
    border: 1px solid rgba(13,28,76,0.06) !important;
    border-radius: 2px !important;
    box-shadow: 0 4px 18px rgba(7,17,47,0.04) !important;
    color: var(--brand-navy) !important;
    transition: transform 200ms ease, box-shadow 200ms ease;
}
html.fi a.staff-card:hover,
html.fi .staff-room-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(7,17,47,0.10) !important;
}
html.fi .staff-stat {
    padding: 1.5rem;
}
html.fi .staff-stat strong {
    display: block;
    margin-top: 0.45rem;
    font-family: 'Cormorant Garamond', serif;
    color: var(--brand-gold);
    font-size: 2.75rem;
    font-weight: 600;
    line-height: 1;
}
html.fi .staff-stat strong.is-success {
    color: #34d399;
}
html.fi .staff-stat strong.is-muted {
    color: rgba(13,28,76,0.55);
}
html.fi .staff-section-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
html.fi .staff-section-head h2 {
    font-size: 2rem;
    margin-top: 0.2rem;
}
html.fi .staff-link {
    color: var(--brand-navy) !important;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    font-size: 0.72rem;
    font-weight: 600;
}
html.fi .staff-link:hover {
    color: var(--brand-gold) !important;
}
html.fi .staff-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
html.fi .staff-booking-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem;
}
html.fi .staff-booking-main {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    min-width: 0;
}
html.fi .staff-booking-image {
    width: 5rem;
    height: 5rem;
    background-size: cover;
    background-position: center;
    flex: 0 0 auto;
}
html.fi .staff-booking-row h3 {
    font-size: 1.65rem;
    margin: 0.25rem 0;
}
html.fi .staff-status {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.7rem;
    border: 1px solid;
    border-radius: 2px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    font-size: 0.65rem;
    white-space: nowrap;
}
html.fi .staff-status-pending { color:#a37520; border-color:rgba(217,167,47,0.55); background:rgba(217,167,47,0.10); }
html.fi .staff-status-approved { color:#047857; border-color:rgba(16,185,129,0.45); background:rgba(16,185,129,0.10); }
html.fi .staff-status-rejected { color:#b91c1c; border-color:rgba(239,68,68,0.45); background:rgba(239,68,68,0.08); }
html.fi .staff-status-cancelled { color:rgba(13,28,76,0.55); border-color:rgba(13,28,76,0.20); background:rgba(13,28,76,0.04); }
html.fi .staff-status-completed { color:#1d4ed8; border-color:rgba(59,130,246,0.40); background:rgba(59,130,246,0.08); }
html.fi .staff-room-grid,
html.fi .staff-shortcuts {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
}
html.fi .staff-room-card {
    position: relative;
    display: block;
    min-height: 280px;
    overflow: hidden;
    border-radius: 2px;
    box-shadow: 0 4px 18px rgba(7,17,47,0.04);
    transition: transform 250ms ease, box-shadow 250ms ease;
}
html.fi .staff-room-image,
html.fi .staff-room-scrim {
    position: absolute;
    inset: 0;
}
html.fi .staff-room-image {
    background-size: cover;
    background-position: center;
    transition: transform 600ms ease;
}
html.fi .staff-room-card:hover .staff-room-image {
    transform: scale(1.05);
}
html.fi .staff-room-scrim {
    background: linear-gradient(180deg, rgba(7,17,47,0) 0%, rgba(7,17,47,0.55) 60%, rgba(7,17,47,0.92) 100%);
}
html.fi .staff-room-content {
    position: relative;
    z-index: 1;
    min-height: 280px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
html.fi .staff-room-card h3 {
    color: #fff !important;
    font-size: 1.65rem;
    margin: 0.2rem 0;
}
html.fi .staff-room-card p:last-child {
    color: rgba(255,255,255,0.72) !important;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-size: 0.72rem;
}
html.fi .staff-empty {
    text-align: center;
    padding: 2.5rem;
}
html.fi .staff-btn-gold {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--brand-gold) !important;
    color: var(--brand-dark) !important;
    padding: 0.85rem 2rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-size: 0.72rem;
    font-weight: 600;
    transition: all 250ms ease;
}
html.fi .staff-btn-gold:hover {
    background: var(--brand-dark) !important;
    color: var(--brand-gold) !important;
}
html.fi .staff-shortcut {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
html.fi .staff-shortcut span {
    font-family: 'Cormorant Garamond', serif;
    color: var(--brand-navy);
    font-size: 1.6rem;
    font-weight: 600;
}
@media (max-width: 1024px) {
    html.fi .staff-stat-grid,
    html.fi .staff-room-grid,
    html.fi .staff-shortcuts {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 720px) {
    html.fi .staff-stat-grid,
    html.fi .staff-room-grid,
    html.fi .staff-shortcuts {
        grid-template-columns: 1fr;
    }
    html.fi .staff-booking-row,
    html.fi .staff-booking-main {
        align-items: flex-start;
        flex-direction: column;
    }
    html.fi .staff-booking-image {
        width: 100%;
        height: 10rem;
    }
}

/* ============ Scrollbar ============ */
html.fi ::-webkit-scrollbar { width: 8px; }
html.fi ::-webkit-scrollbar-track { background: var(--brand-bg); }
html.fi ::-webkit-scrollbar-thumb { background: rgba(13,28,76,0.30); border-radius: 4px; }
html.fi ::-webkit-scrollbar-thumb:hover { background: var(--brand-gold); }
</style>
