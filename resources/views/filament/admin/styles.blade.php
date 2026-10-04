<style>
    :root {
        --wasal-gold: #d4af37;
        --wasal-gold-strong: #b58b16;
        --wasal-gold-soft: #f3e7bd;
        --wasal-gold-faint: #fbf6e8;
        --wasal-ink: #17150f;
        --wasal-ink-soft: #2a271f;
        --wasal-warm-bg: #f7f4ec;
        --wasal-card: #fffdf8;
        --wasal-border: #e8e0cf;
        --wasal-muted: #756f63;
        --wasal-shadow: 0 10px 28px rgba(57, 45, 14, .06);
        --wasal-shadow-soft: 0 4px 14px rgba(57, 45, 14, .045);
    }

    .dark {
        --wasal-warm-bg: #0f0f0d;
        --wasal-card: #171713;
        --wasal-border: #302b20;
        --wasal-ink: #f7f2e7;
        --wasal-ink-soft: #e6dec9;
        --wasal-muted: #aaa294;
        --wasal-gold-faint: #211d13;
        --wasal-shadow: 0 10px 28px rgba(0, 0, 0, .24);
        --wasal-shadow-soft: 0 4px 14px rgba(0, 0, 0, .2);
    }

    html {
        scroll-behavior: smooth;
    }

    body,
    .fi-body {
        background: var(--wasal-warm-bg);
        color: var(--wasal-ink);
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
    }

    .fi-main-ctn,
    .fi-page {
        max-width: 100% !important;
    }

    .fi-main {
        background:
            radial-gradient(circle at 18% 0%, rgba(212, 175, 55, .045), transparent 28rem),
            var(--wasal-warm-bg);
    }

    .fi-page {
        padding-bottom: 2rem;
    }

    .fi-header-heading,
    .fi-page-header-heading {
        color: var(--wasal-ink);
        font-weight: 850 !important;
        letter-spacing: -.02em;
    }

    .fi-header-subheading,
    .fi-page-header-subheading {
        color: var(--wasal-muted);
    }

    /* ---------- Top bar ---------- */

    .fi-topbar {
        border-bottom: 1px solid var(--wasal-border);
        background: color-mix(in srgb, var(--wasal-card) 96%, transparent);
        backdrop-filter: blur(14px);
        box-shadow: 0 1px 0 rgba(212, 175, 55, .06);
    }

    .fi-topbar nav {
        min-height: 4.1rem;
    }

    /* ---------- Sidebar ---------- */

    .fi-sidebar {
        background: linear-gradient(180deg, #fffdf8 0%, #fbf6e8 55%, #f6eed8 100%) !important;
        border-inline-start: 1px solid #e7dcc1;
        box-shadow: -8px 0 28px rgba(75, 58, 18, .05);
    }

    .dark .fi-sidebar {
        background: linear-gradient(180deg, #1b1913 0%, #16140f 55%, #12110d 100%) !important;
        border-inline-start-color: rgba(212, 175, 55, .18);
    }

    .fi-sidebar-header {
        background: rgba(255, 255, 255, .55) !important;
        border-bottom: 1px solid #eadfc6;
    }

    .dark .fi-sidebar-header {
        background: rgba(255, 255, 255, .015) !important;
        border-bottom-color: rgba(212, 175, 55, .16);
    }

    .fi-sidebar-nav {
        scrollbar-color: rgba(181, 139, 22, .38) transparent;
    }

    .fi-sidebar-group-label,
    .fi-sidebar-group-button {
        color: #756a52 !important;
        font-weight: 800;
    }

    .dark .fi-sidebar-group-label,
    .dark .fi-sidebar-group-button {
        color: #b9b09b !important;
    }

    .fi-sidebar-item-button {
        border: 1px solid transparent;
        border-radius: 12px !important;
        color: #3f392d !important;
        transition:
            background-color .18s ease,
            border-color .18s ease,
            color .18s ease,
            transform .18s ease;
    }

    .dark .fi-sidebar-item-button {
        color: #ddd6c8 !important;
    }

    .fi-sidebar-item-button:hover {
        background: #f3e8c8 !important;
        border-color: #e3cc86;
        color: #201c12 !important;
        transform: translateX(-2px);
    }

    .dark .fi-sidebar-item-button:hover {
        background: rgba(212, 175, 55, .09) !important;
        border-color: rgba(212, 175, 55, .14);
        color: #fff8e7 !important;
    }

    .fi-sidebar a[aria-current="page"],
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
        background: linear-gradient(135deg, #e4c75d, #d4af37) !important;
        border-color: #cda92f;
        box-shadow: 0 7px 18px rgba(181, 139, 22, .14);
        color: #17130a !important;
    }

    .fi-sidebar a[aria-current="page"] *,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button * {
        color: #17130a !important;
    }

    .fi-sidebar-item-icon {
        color: #8c8065;
    }

    .dark .fi-sidebar-item-icon {
        color: #a99f8d;
    }

    .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #9a7411;
    }

    /* ---------- Dashboard / cards ---------- */

    .fi-wi-stats-overview-stat,
    .fi-section,
    .fi-fo-section {
        position: relative;
        border: 1px solid var(--wasal-border) !important;
        border-radius: 18px !important;
        background: linear-gradient(145deg, var(--wasal-card), var(--wasal-gold-faint)) !important;
        box-shadow: var(--wasal-shadow-soft) !important;
        overflow: hidden;
    }

    .fi-wi-stats-overview-stat::before {
        content: "";
        position: absolute;
        inset-inline: 0;
        top: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--wasal-gold), transparent);
        opacity: .8;
    }

    .fi-wi-stats-overview-stat-value {
        color: var(--wasal-ink);
        font-weight: 850 !important;
    }

    .fi-wi-stats-overview-stat-description {
        color: var(--wasal-muted);
    }

    /* ---------- Forms ---------- */

    .fi-input-wrp,
    .fi-select-input {
        border-radius: 11px !important;
        border-color: var(--wasal-border) !important;
        background: var(--wasal-card);
        box-shadow: none !important;
    }

    .fi-input-wrp:focus-within {
        border-color: rgba(212, 175, 55, .78) !important;
        box-shadow: 0 0 0 3px rgba(212, 175, 55, .1) !important;
    }

    .fi-btn {
        border-radius: 10px !important;
        font-weight: 750;
    }

    /* ---------- Tables: compact premium layout ---------- */

    .fi-ta-ctn {
        border: 1px solid var(--wasal-border) !important;
        border-radius: 16px !important;
        background: var(--wasal-card) !important;
        box-shadow: var(--wasal-shadow) !important;
        overflow: hidden;
    }

    .fi-ta-header-ctn,
    .fi-ta-header-toolbar {
        background: linear-gradient(180deg, var(--wasal-card), #fdf9ef);
        border-bottom: 1px solid var(--wasal-border) !important;
        padding-block: .7rem !important;
    }

    .dark .fi-ta-header-ctn,
    .dark .fi-ta-header-toolbar {
        background: var(--wasal-card);
    }

    .fi-ta-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        background: var(--wasal-card);
    }

    .fi-ta-table thead {
        background: #f7f0dd;
    }

    .dark .fi-ta-table thead {
        background: #211d13;
    }

    .fi-ta-table thead th {
        padding-top: .78rem !important;
        padding-bottom: .78rem !important;
        padding-inline: .8rem !important;
        border-bottom: 1px solid #e7dcc2 !important;
        color: #5f5127 !important;
        font-size: .79rem;
        font-weight: 850 !important;
        white-space: nowrap;
        vertical-align: middle;
    }

    .dark .fi-ta-table thead th {
        color: #e6cf7d !important;
        border-bottom-color: #352e1f !important;
    }

    .fi-ta-table tbody tr {
        background: var(--wasal-card);
        transition: background-color .15s ease;
    }

    .fi-ta-table tbody tr:nth-child(even) {
        background: #fdfaf2;
    }

    .dark .fi-ta-table tbody tr:nth-child(even) {
        background: #191712;
    }

    .fi-ta-table tbody tr:hover {
        background: #faf2db !important;
    }

    .dark .fi-ta-table tbody tr:hover {
        background: #211d13 !important;
    }

    .fi-ta-table tbody td {
        padding-top: .82rem !important;
        padding-bottom: .82rem !important;
        padding-inline: .8rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #eee6d4 !important;
        color: var(--wasal-ink-soft);
        font-size: .86rem;
    }

    .dark .fi-ta-table tbody td {
        border-bottom-color: #2b271f !important;
    }

    .fi-ta-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .fi-ta-text-item-label,
    .fi-ta-text {
        line-height: 1.45;
    }

    .fi-ta-actions {
        gap: .35rem;
        white-space: nowrap;
    }

    .fi-ta-actions .fi-link,
    .fi-ta-actions a {
        font-weight: 750;
    }

    .fi-badge {
        border-radius: 999px !important;
        font-weight: 750;
        border: 1px solid color-mix(in srgb, currentColor 16%, transparent);
        box-shadow: none;
        white-space: nowrap;
    }

    .fi-ta-empty-state {
        padding-block: 3rem !important;
        color: var(--wasal-muted);
    }

    /* Smaller, consistent thumbnails across all tables */
    .fi-ta-image {
        width: 136px !important;
        height: 96px !important;
        max-width: 136px !important;
        max-height: 96px !important;
        object-fit: cover;
        border: 1px solid #eadfc8;
        border-radius: 11px !important;
        box-shadow: 0 3px 10px rgba(31, 24, 8, .07);
    }

    .fi-pagination {
        background: var(--wasal-card);
        border-top: 1px solid var(--wasal-border) !important;
        padding-block: .7rem !important;
    }

    .fi-pagination-item {
        border-radius: 8px !important;
    }

    .fi-dropdown-panel,
    .fi-modal-window {
        border: 1px solid var(--wasal-border) !important;
        border-radius: 16px !important;
        background: var(--wasal-card) !important;
        box-shadow: 0 18px 48px rgba(20, 17, 9, .14) !important;
    }

    * {
        scrollbar-width: thin;
        scrollbar-color: rgba(181, 139, 22, .42) transparent;
    }

    *::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    *::-webkit-scrollbar-thumb {
        border: 2px solid transparent;
        border-radius: 999px;
        background: rgba(181, 139, 22, .38);
        background-clip: padding-box;
    }

    *::-webkit-scrollbar-thumb:hover {
        background: rgba(181, 139, 22, .56);
        background-clip: padding-box;
    }

    @media (max-width: 1024px) {
        .fi-ta-content {
            overflow-x: auto;
        }

        .fi-ta-table {
            min-width: 760px;
        }
    }

    @media (max-width: 768px) {
        .fi-page {
            padding-inline: .6rem;
        }

        .fi-ta-ctn,
        .fi-section,
        .fi-fo-section,
        .fi-wi-stats-overview-stat {
            border-radius: 14px !important;
        }

        .fi-ta-table {
            min-width: 820px;
        }

        .fi-ta-table thead th,
        .fi-ta-table tbody td {
            padding-inline: .65rem !important;
        }

        .fi-ta-image {
            width: 116px !important;
            height: 82px !important;
        }

        .fi-sidebar-item-button:hover {
            transform: none;
        }
    }

    .wasal-panel-switcher {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid var(--wasal-border);
        border-radius: 18px;
        background: linear-gradient(145deg, var(--wasal-card), var(--wasal-gold-faint));
        box-shadow: var(--wasal-shadow-soft);
    }

    .wasal-panel-switcher__copy {
        display: grid;
        gap: .2rem;
    }

    .wasal-panel-switcher__copy strong {
        color: var(--wasal-ink);
        font-size: 1rem;
        font-weight: 850;
    }

    .wasal-panel-switcher__copy span {
        color: var(--wasal-muted);
        font-size: .86rem;
    }

    .wasal-panel-switcher__actions {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
    }

    .wasal-panel-switcher__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.55rem;
        padding: .62rem .9rem;
        border: 1px solid var(--wasal-border);
        border-radius: 10px;
        background: var(--wasal-card);
        color: var(--wasal-ink);
        font-size: .86rem;
        font-weight: 800;
        text-decoration: none;
        transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .wasal-panel-switcher__button:hover {
        transform: translateY(-1px);
        border-color: rgba(212, 175, 55, .72);
        box-shadow: 0 6px 16px rgba(181, 139, 22, .10);
    }

    .wasal-panel-switcher__button--primary {
        border-color: #cda92f;
        background: linear-gradient(135deg, #e4c75d, #d4af37);
        color: #17130a;
    }

    @media (max-width: 768px) {
        .wasal-panel-switcher {
            align-items: stretch;
            flex-direction: column;
        }

        .wasal-panel-switcher__actions {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>
