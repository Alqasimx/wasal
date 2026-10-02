<style>
    :root {
        --wasal-gold: #c79a32;
        --wasal-ink: #172033;
        --wasal-surface: #ffffff;
        --wasal-background: #f5f7fb;
    }

    .fi-body {
        background: var(--wasal-background);
    }

    .fi-main-ctn,
    .fi-page {
        max-width: 100% !important;
    }

    .fi-sidebar {
        background: linear-gradient(180deg, #172033 0%, #202d45 58%, #263b50 100%);
        border-inline-end: 0;
    }

    .fi-sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, .12);
    }

    .fi-sidebar-item-label,
    .fi-sidebar-group-label {
        letter-spacing: .01em;
    }

    .fi-sidebar-item.fi-active {
        background: linear-gradient(90deg, rgba(199, 154, 50, .28), rgba(199, 154, 50, .08));
        box-shadow: inset 3px 0 0 var(--wasal-gold);
    }

    .fi-topbar {
        background: rgba(255, 255, 255, .92);
        border-bottom: 1px solid #e6eaf0;
        box-shadow: 0 4px 20px rgba(23, 32, 51, .04);
        backdrop-filter: blur(12px);
    }

    .dark .fi-topbar {
        background: rgba(23, 32, 51, .92);
        border-bottom-color: rgba(255, 255, 255, .1);
    }

    .fi-page-header-heading {
        color: var(--wasal-ink);
        font-weight: 750;
        letter-spacing: -.02em;
    }

    .dark .fi-page-header-heading {
        color: #f8fafc;
    }

    .fi-wi-stats-overview-stat,
    .fi-wi-table,
    .fi-ta-ctn,
    .fi-fo-component-ctn {
        border: 1px solid #e7ebf1;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(23, 32, 51, .06);
    }

    .fi-wi-stats-overview-stat {
        background: var(--wasal-surface);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(23, 32, 51, .11);
    }

    .fi-ta-ctn {
        overflow: hidden;
        background: var(--wasal-surface);
    }

    .fi-ta-header,
    .fi-ta-footer {
        background: #fbfcfe;
    }

    .fi-ta-table thead th {
        background: #f7f9fc;
        color: #667085;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .fi-ta-table tbody tr {
        min-height: 82px;
        transition: background-color .15s ease;
    }

    .fi-ta-table tbody tr:hover {
        background: #faf8f2;
    }

    .fi-ta-table tbody td {
        padding-top: .8rem;
        padding-bottom: .8rem;
        vertical-align: middle;
    }

    .fi-ta-image {
        width: 148px !important;
        height: 104px !important;
        max-width: 148px !important;
        max-height: 104px !important;
        object-fit: cover;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(23, 32, 51, .12);
    }

    .fi-ta-text {
        line-height: 1.55;
    }

    .fi-badge {
        border-radius: 999px;
        font-weight: 650;
    }

    @media (max-width: 768px) {
        .fi-ta-table {
            min-width: 980px;
        }

        .fi-ta-image {
            width: 116px !important;
            height: 84px !important;
        }
    }
</style>
