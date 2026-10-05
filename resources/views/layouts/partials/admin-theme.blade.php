<style>
    /* Storefront palette shared by the entire administration area. */
    :root {
        --admin-primary: #5a4536;
        --admin-sidebar-bg: #3f2f24;
        --admin-sidebar-hover: rgba(243,233,220,.08);
        --admin-sidebar-active: #c29d62;
        --admin-sidebar-text: #e0d0bd;
        --admin-sidebar-text-hover: #fffaf3;
        --admin-sidebar-width: 270px;
        --admin-topbar-h: 72px;
        --admin-content-bg: #faf6f0;
        --admin-border: #e6d8c8;
        --admin-radius: 14px;
        --admin-shadow: 0 3px 18px rgba(90,69,54,.045);
        --bs-body-font-family: 'Manrope', sans-serif;
        --bs-body-color: #3a2e26;
        --bs-body-color-rgb: 58,46,38;
        --bs-primary: #5a4536;
        --bs-primary-rgb: 90,69,54;
        --bs-link-color: #765338;
        --bs-link-hover-color: #3f2f24;
        --bs-link-color-rgb: 118,83,56;
        --bs-link-hover-color-rgb: 63,47,36;
        --bs-border-color: #e6d8c8;
        --bs-secondary-color: #7e7065;
        --bs-secondary-color-rgb: 126,112,101;
        --bs-tertiary-bg: #faf6f0;
        --bs-light-rgb: 250,246,240;
    }
    body { font-family: var(--bs-body-font-family); }
    .admin-sidebar { height: 100dvh; min-height: 0; border-right: 1px solid #5a4536; }
    .sidebar-brand { height: 92px; padding: 0 1.25rem; gap: .75rem; border-color: rgba(230,216,200,.14); }
    .sidebar-brand-icon { background: #c29d62; color: #3f2f24; border-radius: 10px; width: 38px; height: 38px; }
    .sidebar-brand-text strong { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.38rem; font-weight: 600; color: #fffaf3; }
    .sidebar-brand-text span { color: #d2bda3; font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; margin-top: 6px; }
    .sidebar-section-label { color: #c7ae8f; font-size: .64rem; letter-spacing: .13em; padding-top: 1rem; }
    .sidebar-nav { min-height: 0; }
    .sidebar-nav-link { padding: .7rem .8rem; font-size: .82rem; margin-bottom: 4px; }
    .sidebar-nav-link.active { background: #5a4536; color: #fffaf3; box-shadow: inset 0 0 0 1px rgba(194,157,98,.24); }
    .sidebar-nav-link.active .nav-icon { color: #e3bf86; }
    .sidebar-nav-link.active::before { background: #c29d62; }
    .sidebar-footer { border-color: rgba(230,216,200,.14); }
    .sidebar-user-info strong { color: #fffaf3; }
    .sidebar-user-info span { color: #d2bda3; }
    .sidebar-logout-btn { color: #e0d0bd; border-color: rgba(230,216,200,.2); }
    .sidebar-avatar, .topbar-user-pill .mini-avatar { background: #765338; }
    .admin-topbar { background: #fffdf9; padding-inline: 2rem; }
    .topbar-page-title { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.65rem; font-weight: 600; }
    .admin-role-badge { background: #f0e2ce; color: #5a4536; }
    .admin-content { padding: 1.75rem 2rem 2.5rem; }
    .admin-content .card { border-color: var(--admin-border); border-radius: var(--admin-radius); box-shadow: var(--admin-shadow); }
    .admin-content .card-header { background: #fffdf9; border-color: var(--admin-border); padding-block: 1rem; }
    .admin-content .card-footer { background: #faf6f0; border-color: var(--admin-border); }
    .admin-content .table { --bs-table-color: #3a2e26; --bs-table-border-color: #e6d8c8; --bs-table-hover-bg: #faf6f0; --bs-table-striped-bg: #fffdf9; }
    .admin-content .table-light { --bs-table-bg: #f3e9dc; --bs-table-color: #5a4536; }
    .admin-content .table thead th { color: #6b5848; font-size: .76rem; letter-spacing: .025em; background: #faf6f0; padding-block: 1rem; }
    .admin-content .table td { vertical-align: middle; }
    .btn-primary, .btn-outline-primary:hover, .btn-outline-primary:active {
        --bs-btn-bg: #5a4536; --bs-btn-border-color: #5a4536;
        --bs-btn-hover-bg: #3f2f24; --bs-btn-hover-border-color: #3f2f24;
        --bs-btn-active-bg: #3f2f24; --bs-btn-active-border-color: #3f2f24;
        --bs-btn-disabled-bg: #5a4536; --bs-btn-disabled-border-color: #5a4536;
        --bs-btn-focus-shadow-rgb: 194,157,98;
    }
    .btn-outline-primary { --bs-btn-color: #5a4536; --bs-btn-border-color: #b79a78; --bs-btn-hover-bg: #5a4536; --bs-btn-hover-border-color: #5a4536; --bs-btn-active-bg: #3f2f24; --bs-btn-active-border-color: #3f2f24; --bs-btn-disabled-color: #7e7065; --bs-btn-disabled-border-color: #b79a78; }
    .btn { border-radius: 8px; font-weight: 600; }
    .form-control, .form-select, .input-group-text { border-color: #dfcfbd; border-radius: 8px; }
    .form-control:focus, .form-select:focus { border-color: #a7845b; box-shadow: 0 0 0 .2rem rgba(194,157,98,.18); }
    .form-check-input:checked { background-color: #5a4536; border-color: #5a4536; }
    .form-check-input:focus { border-color: #a7845b; box-shadow: 0 0 0 .2rem rgba(194,157,98,.18); }
    .pagination { --bs-pagination-color: #5a4536; --bs-pagination-border-color: #e6d8c8; --bs-pagination-hover-color: #3f2f24; --bs-pagination-hover-bg: #f3e9dc; --bs-pagination-active-bg: #5a4536; --bs-pagination-active-border-color: #5a4536; }
    .nav-pills { --bs-nav-pills-link-active-bg: #5a4536; }
    .progress { --bs-progress-bar-bg: #8b6544; }
    .admin-content .icon-violet { background: #f0e2ce; color: #765338; }
    :focus-visible { outline: 2px solid #a7845b; outline-offset: 3px; }
    .admin-welcome { display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 1.6rem 1.8rem; margin-bottom: 1.5rem; background: #f3e9dc; border: 1px solid #e6d8c8; border-radius: 16px; }
    .admin-welcome .eyebrow { font-size: .65rem; letter-spacing: .18em; text-transform: uppercase; color: #765338; font-weight: 700; }
    .admin-welcome h2 { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.2rem; margin: .4rem 0; color: #3f2f24; }
    .admin-welcome p { margin: 0; color: #7e7065; font-size: .86rem; }
    .admin-welcome .btn { flex-shrink: 0; }
    @media (max-width: 991.98px) { .admin-content { padding: 1.25rem; } .admin-topbar { padding-inline: 1rem; } }
    @media (max-width: 575.98px) {
        .admin-content { padding: .85rem; }
        .admin-topbar { gap: .5rem; }
        .topbar-page-title { font-size: 1.25rem; white-space: normal; line-height: 1.15; }
        .topbar-user-pill { padding: .25rem; }
        .topbar-user-pill .admin-role-badge { display: none; }
        .admin-welcome { align-items: flex-start; flex-direction: column; padding: 1.25rem; }
        .admin-welcome h2 { font-size: 1.85rem; }
    }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition: none !important; animation: none !important; } }
</style>
